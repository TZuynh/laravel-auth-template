<?php

namespace App\Services\Marketing;

use App\Models\BrainMemory;
use App\Models\ContentAiDraft;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ContentAiService
{
    public function generate(User $user, array $data): ContentAiDraft
    {
        $product = !empty($data['product_id'])
            ? Product::query()->find((int) $data['product_id'])
            : null;

        $platform = (string) ($data['platform'] ?? 'facebook');
        $tone = (string) ($data['tone'] ?? 'expert');
        $prompt = trim((string) ($data['prompt'] ?? ''));
        $idea = trim((string) ($data['idea'] ?? ''));
        if ($prompt === '') {
            $prompt = $idea !== '' ? $idea : $this->randomIdea($platform, $product);
        }

        $includeEmoji = (bool) ($data['include_emoji'] ?? true);
        $includeHashtags = (bool) ($data['include_hashtags'] ?? true);
        $audience = (string) ($data['audience'] ?? 'Mass (All) tại Việt Nam');
        $brain = $this->brainContext($user, $prompt);

        $draft = ContentAiDraft::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'product_id' => $product?->id,
            'platform' => $platform,
            'title' => $this->title($platform, $product, $prompt),
            'status' => 'running',
            'tone' => $tone,
            'audience' => $audience,
            'include_emoji' => $includeEmoji,
            'include_hashtags' => $includeHashtags,
            'prompt' => $prompt,
            'content' => '',
            'metadata' => [
                'brain_memory_count' => count($brain),
                'idea' => $prompt,
                'source' => 'generating',
            ],
        ]);

        try {
            $content = $this->generateWithOpenAi($platform, $tone, $prompt, $product, $audience, $includeEmoji, $includeHashtags, $brain)
                ?: $this->fallbackContent($platform, $tone, $prompt, $product, $audience, $includeEmoji, $includeHashtags, $brain);

            $draft->update([
                'status' => 'completed',
                'content' => $content,
                'metadata' => array_replace($draft->metadata ?? [], [
                    'source' => $this->hasOpenAiKey() ? 'openai' : 'local_fallback',
                    'platform_label' => $this->platformLabel($platform),
                ]),
            ]);
        } catch (Throwable $exception) {
            $draft->update([
                'status' => 'completed',
                'content' => $this->fallbackContent($platform, $tone, $prompt, $product, $audience, $includeEmoji, $includeHashtags, $brain),
                'metadata' => array_replace($draft->metadata ?? [], [
                    'source' => 'local_fallback',
                    'fallback_reason' => Str::limit($exception->getMessage(), 500),
                ]),
            ]);
        }

        return $draft->refresh();
    }

    public function update(ContentAiDraft $draft, array $data): ContentAiDraft
    {
        $draft->update([
            'content' => trim((string) $data['content']),
            'status' => 'saved',
            'metadata' => array_replace($draft->metadata ?? [], [
                'edited_at' => now()->toIso8601String(),
            ]),
        ]);

        return $draft->refresh();
    }

    private function generateWithOpenAi(
        string $platform,
        string $tone,
        string $prompt,
        ?Product $product,
        string $audience,
        bool $includeEmoji,
        bool $includeHashtags,
        array $brain
    ): ?string {
        if (!$this->hasOpenAiKey()) {
            return null;
        }

        $response = Http::baseUrl(rtrim((string) config('ai_providers.providers.openai.base_url', 'https://api.openai.com/v1'), '/'))
            ->withToken((string) config('ai_providers.providers.openai.api_key'))
            ->asJson()
            ->timeout(120)
            ->post('/responses', [
                'model' => (string) config('ai_providers.providers.openai.text_model', 'gpt-5.5'),
                'input' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a senior Vietnamese performance copywriter. Write substantial, natural, conversion-focused Vietnamese content. Use supplied brand memory strictly when relevant. Make every platform output structurally different, with a visible tone difference and enough detail to feel useful.',
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode([
                            'platform' => $this->platformLabel($platform),
                            'platform_strategy' => $this->platformStrategy($platform),
                            'tone' => $tone,
                            'tone_strategy' => $this->toneInstruction($tone),
                            'audience' => $audience,
                            'include_emoji' => $includeEmoji,
                            'include_hashtags' => $includeHashtags,
                            'product' => $product ? [
                                'name' => $product->name,
                                'category' => $product->category,
                                'brand' => $product->brand,
                                'description' => $product->seo_description,
                            ] : null,
                            'request_or_random_angle' => $prompt,
                            'brand_memory' => $brain,
                            'variation_seed' => now()->format('YmdHis') . '-' . random_int(1000, 9999),
                            'output_rules' => [
                                'Vietnamese by default unless user asks another language',
                                'Open with a specific hook, not a generic greeting',
                                'Write more complete content: 180-260 Vietnamese words for social posts, 220-320 words for email, 140-220 words for Zalo',
                                'Use short mobile-first paragraphs but include enough context, benefit, objection handling, proof angle, and CTA',
                                'The selected tone must visibly change word choice and rhythm',
                                'CTA at the end',
                                'Do not repeat the input prompt verbatim',
                                'No markdown table',
                            ],
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ],
                ],
                'max_output_tokens' => 1600,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI content generation failed: ' . $response->body());
        }

        $json = $response->json() ?? [];
        $text = data_get($json, 'output_text');
        if (is_string($text) && trim($text) !== '') {
            return trim($text);
        }

        foreach ((array) data_get($json, 'output', []) as $item) {
            foreach ((array) data_get($item, 'content', []) as $content) {
                $candidate = data_get($content, 'text');
                if (is_string($candidate) && trim($candidate) !== '') {
                    return trim($candidate);
                }
            }
        }

        return null;
    }

    private function fallbackContent(
        string $platform,
        string $tone,
        string $prompt,
        ?Product $product,
        string $audience,
        bool $includeEmoji,
        bool $includeHashtags,
        array $brain
    ): string {
        $subject = $product?->name ?: $prompt;
        $emoji = $includeEmoji ? $this->emojiSet($platform) : ['', '', ''];
        $toneLine = $this->toneInstruction($tone);
        $memoryText = trim(implode(' ', array_column($brain, 'content')));
        $memoryLine = $memoryText !== ''
            ? "\n\nGhi nhớ thương hiệu: " . Str::limit($memoryText, 420)
            : '';
        $cta = match ($platform) {
            'email' => 'Trả lời email này để nhận tư vấn phù hợp với nhu cầu hiện tại của bạn.',
            'zalo' => 'Nhắn Zalo để được hỗ trợ nhanh trong hôm nay và nhận gợi ý phù hợp nhất.',
            'linkedin' => 'Kết nối với đội ngũ của chúng tôi nếu bạn muốn trao đổi sâu hơn về hướng triển khai.',
            default => 'Nhắn tin ngay để nhận tư vấn, bảng giá hoặc ưu đãi phù hợp.',
        };

        $blocks = match ($platform) {
            'instagram' => [
                trim("{$emoji[0]} " . $this->hook($tone, $subject)),
                "Có rất nhiều bài đăng đẹp, nhưng thứ khiến khách dừng lại lâu hơn là một câu chuyện đủ gần với nhu cầu thật. Với {$subject}, góc triển khai nên bắt đầu từ cảm giác mà khách đang tìm kiếm: {$prompt}.",
                "Dành cho {$audience}, nội dung nên cho họ thấy bối cảnh sử dụng, lý do nên quan tâm ngay bây giờ và một lợi ích cụ thể có thể hình dung được. Đừng chỉ nói sản phẩm tốt; hãy mô tả khoảnh khắc sau khi khách đã chọn đúng giải pháp.",
                trim("{$emoji[1]} Gợi ý triển khai: mở bằng một câu hỏi chạm pain point, tiếp theo là 2-3 lợi ích rõ ràng, sau đó thêm một chi tiết tạo niềm tin như chất liệu, quy trình, phản hồi khách hàng hoặc cam kết dịch vụ."),
                'Nếu khách đang phân vân, hãy giúp họ thấy lựa chọn này ít rủi ro hơn, dễ bắt đầu hơn và đáng lưu lại hơn so với việc tiếp tục tìm kiếm.',
                trim("{$emoji[2]} {$cta}"),
            ],
            'tiktok' => [
                trim("{$emoji[0]} Dừng cuộn 3 giây: " . $this->hook($tone, $subject)),
                "Vấn đề là khách nhìn thấy quá nhiều nội dung mỗi ngày, nên họ chỉ ở lại khi câu mở đầu đủ cụ thể. Với chủ đề {$prompt}, hãy nói thẳng vào tình huống mà họ đang gặp, thay vì mở đầu chung chung.",
                "Cách xử lý: 3 giây đầu nêu pain point, 5 giây tiếp theo cho thấy sự khác biệt của {$subject}, phần giữa đưa bằng chứng hoặc ví dụ dễ hiểu, phần cuối chốt bằng một hành động nhỏ nhưng rõ.",
                trim("{$emoji[1]} Kịch bản caption: nếu bạn đang cân nhắc {$subject}, hãy chú ý đến chi tiết mà nhiều người bỏ qua. Chính chi tiết này quyết định trải nghiệm sau khi mua, cảm giác sử dụng và mức độ hài lòng về lâu dài."),
                "Nội dung này phù hợp với {$audience}. {$toneLine}",
                $cta,
            ],
            'linkedin' => [
                $this->hook($tone, $subject),
                "Trong thị trường hiện tại, {$audience} không chỉ cần một thông điệp nghe hay. Họ cần một luận điểm rõ: vì sao vấn đề này đáng quan tâm, vì sao giải pháp này hợp lý và vì sao nên hành động ngay.",
                "Góc nhìn đề xuất: {$prompt}. Từ góc này, bài viết nên đi theo mạch: bối cảnh thị trường, vấn đề khách hàng đang gặp, tác động nếu không xử lý, và cách {$subject} có thể tạo ra thay đổi thực tế.",
                'Điểm nên nhấn mạnh là kết quả cụ thể, bằng chứng đáng tin và logic ra quyết định. Khi nội dung có lập luận tốt, khách không cảm thấy bị bán hàng; họ cảm thấy được giúp hiểu vấn đề rõ hơn.',
                "Với giọng {$tone}, bài viết cần giữ nhịp chuyên nghiệp, tránh phóng đại và ưu tiên những câu có giá trị thực.",
                $cta,
            ],
            'email' => [
                'Subject: ' . $this->hook($tone, $subject),
                'Chào bạn,',
                "Nếu bạn đang cân nhắc {$subject}, có một góc nhìn đáng chú ý: {$prompt}. Đây không chỉ là câu chuyện về một lựa chọn sản phẩm, mà là cách bạn giảm thời gian tìm kiếm, tránh chọn sai và có trải nghiệm chắc chắn hơn.",
                'Điều khách thường cần trước khi ra quyết định là sự rõ ràng. Sản phẩm/dịch vụ này phù hợp với ai, giải quyết điểm đau nào, khác biệt nằm ở đâu và bước tiếp theo có đơn giản không. Vì vậy, nội dung nên đi thẳng vào lợi ích thật, sau đó bổ sung lý do tin cậy.',
                "Với {$audience}, hãy nhấn mạnh kết quả dễ hình dung: tiết kiệm thời gian, cảm giác an tâm, trải nghiệm đẹp hơn hoặc hiệu quả rõ hơn sau khi sử dụng.",
                $cta,
            ],
            'zalo' => [
                trim("{$emoji[0]} " . $this->hook($tone, $subject)),
                "Gợi ý nhanh cho {$audience}: {$prompt}.",
                'Nếu bạn đang phân vân, hãy bắt đầu từ nhu cầu thật của mình: bạn muốn giải quyết vấn đề gì, cần kết quả trong bao lâu và có tiêu chí nào không thể bỏ qua?',
                'Đội ngũ có thể tư vấn theo đúng trường hợp của bạn, đưa gợi ý ngắn gọn, dễ hiểu và không làm mất thời gian.',
                $cta,
            ],
            default => [
                trim("{$emoji[0]} " . $this->hook($tone, $subject)),
                "Bài viết này dành cho {$audience}. {$toneLine}",
                "Góc triển khai: {$prompt}. Thay vì chỉ mô tả sản phẩm, hãy bắt đầu từ bối cảnh mà khách đang gặp: họ cần gì, đang lo điều gì và muốn kết quả ra sao sau khi lựa chọn.",
                "Với {$subject}, nội dung nên làm rõ ba điểm: lợi ích chính, lý do tin cậy và hành động tiếp theo. Khi ba điểm này rõ, khách dễ hiểu hơn và cũng dễ nhắn tin hơn.",
                trim("{$emoji[1]} Gợi ý nội dung: kể một tình huống thật, chỉ ra sai lầm thường gặp, sau đó cho thấy cách sản phẩm/dịch vụ giúp khách chọn đúng hơn."),
                trim("{$emoji[2]} {$cta}"),
            ],
        };

        $hashtags = $includeHashtags ? $this->hashtags($platform, $product) : '';

        return trim(implode("\n\n", array_filter($blocks))) . $memoryLine . $hashtags;
    }

    private function fallbackContentLegacy(
        string $platform,
        string $tone,
        string $prompt,
        ?Product $product,
        string $audience,
        bool $includeEmoji,
        bool $includeHashtags,
        array $brain
    ): string {
        $subject = $product?->name ?: $prompt;
        $emoji = $includeEmoji ? $this->emojiSet($platform) : ['', '', ''];
        $memoryLine = $brain !== []
            ? "\n\nGhi nhớ thương hiệu: " . Str::limit(implode(' ', array_column($brain, 'content')), 220)
            : '';
        $cta = match ($platform) {
            'email' => 'Trả lời email này để nhận tư vấn phù hợp.',
            'zalo' => 'Nhắn Zalo để được hỗ trợ nhanh trong hôm nay.',
            'linkedin' => 'Kết nối với đội ngũ của chúng tôi để trao đổi chiến lược chi tiết.',
            default => 'Nhắn tin ngay để nhận tư vấn và ưu đãi phù hợp.',
        };

        $blocks = match ($platform) {
            'instagram' => [
                trim("{$emoji[0]} " . $this->hook($tone, $subject)),
                "Một visual đẹp chỉ giữ chân người xem vài giây. Điều khiến họ hành động là cảm giác: {$prompt}.",
                trim("{$emoji[1]} Dành cho {$audience}: biến lợi ích thành một câu chuyện ngắn, dễ lưu, dễ chia sẻ."),
                trim("{$emoji[2]} {$cta}"),
            ],
            'tiktok' => [
                trim("{$emoji[0]} Dừng cuộn 3 giây: " . $this->hook($tone, $subject)),
                'Vấn đề: khách thấy rất nhiều nội dung nhưng ít lý do để tin.',
                'Cách xử lý: mở bằng một câu chạm pain point, chứng minh nhanh lợi ích, rồi chốt bằng hành động rõ.',
                trim("{$emoji[1]} Ý tưởng video/caption: {$prompt}"),
                $cta,
            ],
            'linkedin' => [
                $this->hook($tone, $subject),
                "Trong thị trường hiện tại, {$audience} không chỉ cần một thông điệp đẹp. Họ cần một lý do đủ rõ để ra quyết định.",
                "Góc nhìn đề xuất: {$prompt}.",
                'Điểm nên nhấn mạnh: kết quả cụ thể, bằng chứng tin cậy, và tác động kinh doanh.',
                $cta,
            ],
            'email' => [
                'Subject: ' . $this->hook($tone, $subject),
                'Chào bạn,',
                "Nếu bạn đang cân nhắc {$subject}, đây là góc nhìn đáng chú ý: {$prompt}.",
                'Thông điệp nên đi thẳng vào lợi ích, giảm rủi ro và đưa ra bước tiếp theo thật dễ thực hiện.',
                $cta,
            ],
            'zalo' => [
                trim("{$emoji[0]} " . $this->hook($tone, $subject)),
                "Gợi ý nhanh cho {$audience}: {$prompt}.",
                'Nội dung nên ngắn, rõ lợi ích, có lý do phản hồi ngay và không tạo cảm giác bị bán hàng quá mạnh.',
                $cta,
            ],
            default => [
                trim("{$emoji[0]} " . $this->hook($tone, $subject)),
                "Bài viết này dành cho {$audience}. {$this->toneInstruction($tone)}",
                "Góc triển khai: {$prompt}.",
                trim("{$emoji[1]} Tập trung vào lợi ích rõ, cảm xúc thật và một hành động dễ làm ngay."),
                trim("{$emoji[2]} {$cta}"),
            ],
        };

        $hashtags = $includeHashtags ? $this->hashtags($platform, $product) : '';

        return trim(implode("\n\n", array_filter($blocks))) . $memoryLine . $hashtags;
    }

    private function brainContext(User $user, string $prompt): array
    {
        $keywords = Str::of($prompt)
            ->lower()
            ->explode(' ')
            ->filter(fn ($word) => mb_strlen((string) $word) >= 4)
            ->take(8)
            ->values();

        return BrainMemory::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(24)
            ->get(['category', 'topic', 'content'])
            ->sortByDesc(function (BrainMemory $memory) use ($keywords): int {
                $score = match ($memory->category) {
                    'voice_style', 'brand_rule' => 20,
                    'usp', 'offer' => 16,
                    'customer_insight', 'faq' => 12,
                    default => 8,
                };
                $haystack = Str::lower($memory->topic . ' ' . $memory->content);
                foreach ($keywords as $keyword) {
                    if (str_contains($haystack, (string) $keyword)) {
                        $score += 10;
                    }
                }

                return $score;
            })
            ->take(8)
            ->map(fn (BrainMemory $memory): array => [
                'category' => $memory->category,
                'topic' => $memory->topic,
                'content' => Str::limit($memory->content, 600),
            ])
            ->values()
            ->all();
    }

    private function title(string $platform, ?Product $product, string $prompt): string
    {
        return $this->platformLabel($platform) . ' - ' . Str::limit($product?->name ?: Str::headline($prompt ?: 'Bài viết mới'), 72);
    }

    private function platformLabel(string $platform): string
    {
        return match ($platform) {
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
            'email' => 'Email',
            'zalo' => 'Zalo',
            'tiktok' => 'TikTok',
            default => 'Facebook',
        };
    }

    private function randomIdea(string $platform, ?Product $product): string
    {
        $subject = $product?->name ?: 'sản phẩm/dịch vụ';
        $ideas = [
            'facebook' => [
                "Kể câu chuyện khách hàng trước và sau khi dùng {$subject}",
                "Bài post mở thảo luận về sai lầm thường gặp khi chọn {$subject}",
                "Minigame nhẹ nhàng giúp khách nhớ tới {$subject}",
            ],
            'instagram' => [
                "Caption cho carousel 5 ảnh về phong cách sống xoay quanh {$subject}",
                "Bài đăng visual premium với hook cảm xúc cho {$subject}",
                "Story caption ngắn tạo cảm giác muốn lưu lại về {$subject}",
            ],
            'linkedin' => [
                "Góc nhìn chuyên gia về giá trị kinh doanh của {$subject}",
                "Bài phân tích vấn đề thị trường mà {$subject} giải quyết",
                'Bài thought leadership giúp thương hiệu đáng tin hơn',
            ],
            'email' => [
                'Email chăm sóc khách cũ bằng một ưu đãi có lý do rõ ràng',
                "Email giới thiệu lợi ích chính của {$subject} trong 30 giây đọc",
                'Email nhắc khách quay lại với lời mở đầu cá nhân hóa',
            ],
            'zalo' => [
                "Tin nhắn ngắn thông báo ưu đãi hôm nay cho {$subject}",
                'Kịch bản Zalo chăm sóc khách đang phân vân',
                'Tin nhắn gợi lại nhu cầu và mời khách phản hồi nhanh',
            ],
            'tiktok' => [
                'Hook 3 giây cho video TikTok về nỗi đau của khách',
                "Caption TikTok bắt trend nhưng vẫn bán được {$subject}",
                'Kịch bản short caption có problem, solution và CTA nhanh',
            ],
        ];

        $pool = $ideas[$platform] ?? $ideas['facebook'];

        return $pool[array_rand($pool)];
    }

    private function platformStrategy(string $platform): string
    {
        return match ($platform) {
            'instagram' => 'Visual-first caption, aspirational hook, short polished paragraphs, save/share intent.',
            'tiktok' => 'Fast hook, direct pain point, creator-style language, high energy CTA.',
            'linkedin' => 'Professional insight, clear argument, business value, credible tone.',
            'email' => 'Subject line, personal opening, concise value, one clear CTA.',
            'zalo' => 'Short conversational message, quick benefit, immediate reply CTA.',
            default => 'Community-friendly post, emotional hook, benefit bullets, comment/message CTA.',
        };
    }

    private function toneInstruction(string $tone): string
    {
        return match ($tone) {
            'friendly' => 'Giọng văn thân thiện, gần gũi, ít thuật ngữ, tạo cảm giác đang tư vấn thật.',
            'premium' => 'Giọng văn cao cấp, tinh gọn, ít phóng đại, nhấn vào trải nghiệm và sự tin cậy.',
            'viral' => 'Giọng văn nhanh, mạnh, hook lớn, câu ngắn, tạo cảm giác muốn dừng cuộn.',
            'direct' => 'Giọng văn bán hàng trực diện, lợi ích rõ, CTA mạnh, không vòng vo.',
            default => 'Giọng văn chuyên gia, rõ ràng, có lập luận, đáng tin và tập trung chuyển đổi.',
        };
    }

    private function hook(string $tone, string $subject): string
    {
        return match ($tone) {
            'friendly' => "Có thể bạn đang cần {$subject} theo cách đơn giản hơn.",
            'premium' => "{$subject} không cần ồn ào để trở nên đáng chú ý.",
            'viral' => "Đừng mua {$subject} trước khi bạn biết điều này.",
            'direct' => "Nếu bạn cần kết quả rõ hơn, hãy bắt đầu từ {$subject}.",
            default => "Một góc nhìn đáng cân nhắc về {$subject}.",
        };
    }

    private function emojiSet(string $platform): array
    {
        return match ($platform) {
            'linkedin', 'email' => ['', '', ''],
            'tiktok' => ['⚡', '🎬', '👉'],
            'instagram' => ['✨', '📌', '💬'],
            default => ['✨', '✅', '👉'],
        };
    }

    private function hashtags(string $platform, ?Product $product): string
    {
        if (in_array($platform, ['email', 'zalo'], true)) {
            return '';
        }

        $category = Str::slug((string) ($product?->category ?: 'marketing'), '') ?: 'marketing';

        return "\n\n#socialviral #contentai #{$category}";
    }

    private function hasOpenAiKey(): bool
    {
        return trim((string) config('ai_providers.providers.openai.api_key', '')) !== '';
    }
}
