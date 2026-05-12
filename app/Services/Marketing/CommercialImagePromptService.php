<?php

namespace App\Services\Marketing;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class CommercialImagePromptService
{
    private const NEGATIVE_PROMPT = 'blurry, low quality, distorted objects, bad anatomy, duplicate elements, random text, watermark, logo, typography, poster layout, graphic template, flat design, oversaturated, deformed, cropped subject';

    public function build(?Product $product, array $data, array $brain = []): array
    {
        if ($geminiPackage = $this->buildWithGemini($product, $data, $brain)) {
            return $geminiPackage;
        }

        $input = trim((string) ($data['prompt'] ?? ''));
        $styleKey = (string) ($data['style'] ?? 'cinematic');
        $aspectRatio = (string) ($data['aspect_ratio'] ?? '9:16');
        $subject = $this->subject($product, $input);
        $category = $this->category($product, $input);
        $scene = $this->scene($subject, $category, $input, $styleKey);
        $camera = $this->camera($styleKey, $aspectRatio, $category);
        $lighting = $this->lighting($styleKey, $category);
        $environment = $this->environment($category, $styleKey);
        $mood = $this->mood($styleKey, $category);
        $materials = $this->materials($category);
        $colors = $this->colors($styleKey, $category);
        $brandMemory = $this->brandMemory($brain);

        $prompt = trim(implode(",\n", array_filter([
            $scene,
            $environment,
            $lighting,
            $camera,
            $mood,
            $materials,
            'deep spatial depth, realistic scale, natural perspective, believable shadows, clean subject separation',
            'rich textures, premium realism, dramatic contrast, ultra detailed, photoreal',
            'professional interior and product photography, real photographed scene, no graphic design layout',
            'color palette: ' . implode(', ', $colors),
            $input !== '' ? 'user visual direction interpreted as physical scene details: ' . $this->sanitize($input) : null,
            $brandMemory !== '' ? 'subtle brand memory as visual constraints only: ' . $brandMemory : null,
            'strictly no text, no logos, no typography, no poster layout, no marketing flyer, no watermark',
        ])));

        return [
            'title' => $this->title($subject, $category),
            'concept' => $this->concept($subject, $category, $environment),
            'prompt' => $this->sanitize($prompt),
            'negative_prompt' => self::NEGATIVE_PROMPT,
            'style_tags' => $this->styleTags($styleKey, $category),
            'category' => $category,
            'scene' => $scene,
            'environment' => $environment,
            'camera' => $camera,
            'lighting' => $lighting,
            'mood' => $mood,
            'colors' => $colors,
            'engine' => 'local_rules',
        ];
    }

    public function negativePrompt(): string
    {
        return self::NEGATIVE_PROMPT;
    }

    private function subject(?Product $product, string $input): string
    {
        $name = trim((string) ($product?->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        if ($input !== '') {
            return Str::limit($input, 120, '');
        }

        return 'a refined hero object';
    }

    private function category(?Product $product, string $input): string
    {
        $haystack = Str::lower(trim(implode(' ', [
            $product?->name,
            $product?->category,
            $product?->brand,
            $product?->seo_title,
            $product?->seo_description,
            $input,
        ])));

        return match (true) {
            str_contains($haystack, 'wall art') || str_contains($haystack, 'artwork') || str_contains($haystack, 'poster') || str_contains($haystack, 'print') || str_contains($haystack, 'canvas') || str_contains($haystack, 'gallery') || str_contains($haystack, 'tranh') || str_contains($haystack, 'nghe thuat') || str_contains($haystack, 'nghệ thuật') => 'artwork',
            str_contains($haystack, 'skin') || str_contains($haystack, 'serum') || str_contains($haystack, 'beauty') || str_contains($haystack, 'cosmetic') || str_contains($haystack, 'mask') || str_contains($haystack, 'duong da') || str_contains($haystack, 'dưỡng da') => 'beauty',
            str_contains($haystack, 'shoe') || str_contains($haystack, 'sneaker') || str_contains($haystack, 'fashion') || str_contains($haystack, 'watch') || str_contains($haystack, 'bag') || str_contains($haystack, 'tui xach') || str_contains($haystack, 'túi xách') => 'fashion',
            str_contains($haystack, 'coffee') || str_contains($haystack, 'food') || str_contains($haystack, 'drink') || str_contains($haystack, 'tea') || str_contains($haystack, 'do uong') || str_contains($haystack, 'đồ uống') => 'food',
            str_contains($haystack, 'phone') || str_contains($haystack, 'laptop') || str_contains($haystack, 'tech') || str_contains($haystack, 'device') || str_contains($haystack, 'dien thoai') || str_contains($haystack, 'điện thoại') => 'technology',
            str_contains($haystack, 'chair') || str_contains($haystack, 'sofa') || str_contains($haystack, 'furniture') || str_contains($haystack, 'decor') || str_contains($haystack, 'noi that') || str_contains($haystack, 'nội thất') => 'interior',
            default => 'premium_object',
        };
    }

    private function scene(string $subject, string $category, string $input, string $styleKey): string
    {
        return match ($category) {
            'artwork' => "Ultra realistic luxury contemporary art gallery interior, massive {$subject} mounted on a textured charcoal concrete wall, museum-grade presentation, artwork as the central physical object",
            'beauty' => "Ultra realistic skincare studio scene, {$subject} placed beside translucent glass, dewy ceramic surfaces, subtle water droplets, refined spa atmosphere",
            'fashion' => "Ultra realistic luxury dressing room scene, {$subject} positioned on polished stone and brushed metal surfaces, tactile fabric and leather details visible",
            'food' => "Ultra realistic premium culinary scene, {$subject} placed on handcrafted stoneware, fresh ingredients and natural steam creating sensory realism",
            'technology' => "Ultra realistic futuristic studio scene, {$subject} resting on seamless glass and anodized metal, precise reflections and clean industrial detail",
            'interior' => "Ultra realistic architectural interior scene, {$subject} placed in a calm luxury living space with textured walls and refined spatial depth",
            default => $styleKey === 'clean_studio'
                ? "Ultra realistic minimal studio scene, {$subject} placed in a quiet physical space with soft material surfaces and natural depth"
                : "Ultra realistic cinematic environment, {$subject} placed as a real object inside a refined physical scene with atmospheric depth",
        };
    }

    private function environment(string $category, string $styleKey): string
    {
        return match ($category) {
            'artwork' => 'luxury contemporary art gallery interior, textured concrete wall, dark polished reflective floor, minimal architectural space, premium museum atmosphere',
            'beauty' => 'warm stone skincare suite, frosted glass panels, calm water reflections, soft ivory surfaces, natural spa depth',
            'fashion' => 'private luxury atelier, brushed steel rails, travertine floor, soft fabric shadows, elegant dressing environment',
            'food' => 'moody chef table environment, stone counter, warm wood, ceramic props, shallow background atmosphere',
            'technology' => 'minimal futuristic showroom, seamless graphite surfaces, polished glass reflections, quiet architectural geometry',
            'interior' => 'high-end residential interior, textured plaster walls, curated furniture silhouettes, warm window depth',
            default => $styleKey === 'lifestyle_ad'
                ? 'realistic lived-in luxury environment with refined props and natural atmospheric depth'
                : 'minimal cinematic studio with tactile surfaces, spatial depth, and controlled reflections',
        };
    }

    private function camera(string $styleKey, string $aspectRatio, string $category): string
    {
        if ($category === 'artwork') {
            return '35mm lens, slightly low gallery perspective, artwork centered with architectural depth, subtle depth of field';
        }

        if ($category === 'beauty') {
            return '85mm macro close-up, eye-level product perspective, shallow depth of field, crisp surface texture';
        }

        if ($category === 'technology') {
            return '50mm low three-quarter angle, precise perspective compression, clean edge highlights, controlled reflections';
        }

        return match ($styleKey) {
            'premium_packshot' => '70mm three-quarter hero angle, realistic object scale, soft foreground falloff',
            'luxury_editorial' => '50mm editorial perspective, refined asymmetry, natural depth of field',
            'lifestyle_ad' => '35mm eye-level perspective, environmental storytelling, realistic background bokeh',
            'social_viral' => '35mm close physical perspective, dramatic foreground presence, energetic crop without graphic layout',
            default => $aspectRatio === '16:9'
                ? 'wide cinematic 35mm perspective, controlled spatial depth'
                : 'vertical 45mm hero perspective, strong object presence, natural lens depth',
        };
    }

    private function lighting(string $styleKey, string $category): string
    {
        if ($category === 'artwork') {
            return 'warm cinematic spotlight illuminating the artwork, soft volumetric lighting, architectural shadows, gentle falloff across the concrete wall';
        }

        return match ($styleKey) {
            'premium_packshot' => 'large softbox key light, crisp rim light, elegant reflective highlights, controlled studio shadows',
            'luxury_editorial' => 'warm cinematic key light, dramatic falloff, soft fill, deep shadow detail',
            'clean_studio' => 'large diffused soft light, feathered highlights, pristine shadow gradients',
            'lifestyle_ad' => 'natural window light mixed with subtle rim light and realistic ambient bounce',
            'social_viral' => 'high-contrast cinematic light, punchy rim highlight, dramatic but believable shadow shaping',
            default => 'cinematic lighting, dimensional soft key light, elegant reflections, dramatic shadows',
        };
    }

    private function mood(string $styleKey, string $category): string
    {
        if ($category === 'artwork') {
            return 'minimal luxury environment, quiet museum atmosphere, dramatic contrast, contemplative and expensive';
        }

        return match ($category) {
            'beauty' => 'calm, fresh, tactile, clean, sensual, premium',
            'technology' => 'precise, futuristic, quiet, elegant, innovative',
            'food' => 'warm, sensory, intimate, appetizing, handcrafted',
            default => match ($styleKey) {
                'social_viral' => 'bold, magnetic, cinematic, energetic, premium',
                'luxury_editorial' => 'exclusive, elegant, dramatic, refined',
                'clean_studio' => 'quiet, pure, modern, trustworthy',
                'lifestyle_ad' => 'warm, natural, desirable, emotionally realistic',
                default => 'cinematic, refined, atmospheric, modern luxury',
            },
        };
    }

    private function materials(string $category): string
    {
        return match ($category) {
            'artwork' => 'rich material details: framed artwork surface, fine paper or canvas grain, textured charcoal concrete, polished reflective floor, soft dust in light beam',
            'beauty' => 'rich material details: frosted glass, smooth cream texture, wet ceramic, dewy highlights, fine skin-like softness only where relevant',
            'fashion' => 'rich material details: premium leather grain, woven fabric, brushed metal, tactile stitching, soft textile shadows',
            'food' => 'rich material details: condensation, natural steam, crisp ingredient texture, ceramic glaze, warm wood grain',
            'technology' => 'rich material details: anodized metal, polished glass, micro edge highlights, dust-free industrial surfaces',
            'interior' => 'rich material details: plaster texture, wood grain, stone veining, soft upholstery, natural floor reflections',
            default => 'rich material details: tactile surface texture, subtle imperfections, believable reflections, crisp physical edges',
        };
    }

    private function colors(string $styleKey, string $category): array
    {
        return match ($category) {
            'artwork' => ['charcoal concrete', 'warm amber light', 'deep black floor', 'muted gallery white', 'soft bronze'],
            'beauty' => ['soft ivory', 'champagne beige', 'warm rose', 'clean white', 'subtle gold'],
            'technology' => ['graphite black', 'cool silver', 'electric blue', 'soft white', 'deep navy'],
            'food' => ['warm walnut', 'deep ceramic gray', 'cream', 'natural green', 'amber'],
            'interior' => ['warm gallery white', 'charcoal', 'walnut brown', 'muted gold', 'deep slate'],
            default => match ($styleKey) {
                'social_viral' => ['deep black', 'electric blue', 'hot coral', 'clean white', 'polished chrome'],
                'luxury_editorial' => ['deep espresso', 'champagne gold', 'warm ivory', 'charcoal', 'soft bronze'],
                'clean_studio' => ['porcelain white', 'soft gray', 'pale blue', 'silver', 'warm neutral'],
                default => ['deep navy', 'charcoal black', 'cool silver', 'soft white', 'premium blue'],
            },
        };
    }

    private function styleTags(string $styleKey, string $category): array
    {
        $base = ['photoreal', 'cinematic realism', 'environmental storytelling', 'professional photography'];

        $categoryTags = match ($category) {
            'artwork' => ['luxury gallery interior', 'museum atmosphere', 'architectural shadows'],
            'beauty' => ['skincare studio', 'dewy surfaces', 'soft reflections'],
            'technology' => ['futuristic showroom', 'precise reflections', 'industrial materials'],
            default => ['physical scene', 'rich textures', 'spatial depth'],
        };

        return array_values(array_unique([...$base, ...$categoryTags, $styleKey]));
    }

    private function brandMemory(array $brain): string
    {
        return $this->sanitize(Str::limit(implode(' | ', array_filter(array_column($brain, 'content'))), 500, ''));
    }

    private function buildWithGemini(?Product $product, array $data, array $brain): ?array
    {
        $apiKey = trim((string) config('services.gemini.api_key', ''));
        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash'));

        if ($apiKey === '' || $model === '') {
            return null;
        }

        $context = [
            'product' => [
                'name' => $product?->name,
                'category' => $product?->category,
                'brand' => $product?->brand,
                'tags' => $product?->tags,
                'seo_title' => $product?->seo_title,
                'seo_description' => Str::limit((string) ($product?->seo_description ?? ''), 500, ''),
            ],
            'user_brief' => trim((string) ($data['prompt'] ?? '')),
            'style' => (string) ($data['style'] ?? 'cinematic'),
            'aspect_ratio' => (string) ($data['aspect_ratio'] ?? '9:16'),
            'audience' => (string) ($data['audience'] ?? ''),
            'brain_memory' => array_slice($brain, 0, 6),
        ];

        $cacheKey = 'image-prompt-gemini:v2:' . sha1(json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: serialize($context));
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && !empty($cached['prompt'])) {
            return $cached;
        }

        $package = $this->requestGeminiPromptPackage($model, $apiKey, $context);
        if (!$package) {
            return null;
        }

        Cache::put($cacheKey, $package, now()->addHours(12));

        return $package;
    }

    private function requestGeminiPromptPackage(string $model, string $apiKey, array $context): ?array
    {
        $instruction = implode("\n", [
            'You are an elite AI Creative Director and prompt engineer for cinematic AI image generation.',
            'Your job is NOT marketing copy. Your job is to create a physical, photoreal, cinematic scene prompt.',
            'Transform the product or user idea into a premium photographed environment.',
            '',
            'STRICT RULES:',
            '- Never describe social media layouts, typography, posters, flyers, graphic templates, branding decks, or ad canvas.',
            '- Never ask the image model to render text, logos, captions, UI, watermarks, or random letters.',
            '- Always describe subject placement, category, physical environment, camera, lighting, mood, materials, texture, reflections, atmosphere, and spatial depth.',
            '- Make it feel like a real high-end photographed scene with cinematic realism.',
            '- If product/category is unclear, infer the most visually expensive environment.',
            '- Optimize the prompt for FLUX Schnell, Stable Diffusion, and OpenAI image models.',
            '',
            'Return ONLY valid JSON with these exact keys:',
            'title, concept, prompt, negative_prompt, style_tags, category, scene, environment, camera, lighting, mood, colors.',
            '',
            'JSON rules:',
            '- style_tags must be an array of short strings.',
            '- colors must be an array of color/material names.',
            '- prompt must be 120-220 words, comma-separated cinematic visual details.',
            '- negative_prompt must include: blurry, low quality, distorted objects, bad anatomy, duplicate elements, random text, watermark, logo, typography, poster layout, graphic template, deformed, cropped subject.',
        ]);

        try {
            $response = Http::connectTimeout(max(1, (int) config('services.gemini.connect_timeout', 3)))
                ->timeout(max(12, (int) config('services.gemini.timeout', 8)))
                ->acceptJson()
                ->contentType('application/json')
                ->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [
                            ['text' => $instruction],
                            ['text' => 'INPUT_JSON:' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                        ],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.82,
                        'topP' => 0.92,
                        'responseMimeType' => 'application/json',
                    ],
                ]);
        } catch (Throwable) {
            return null;
        }

        if (!$response->ok()) {
            return null;
        }

        $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text', ''));
        $decoded = $this->decodeGeminiJson($text);

        if ($decoded === []) {
            return null;
        }

        return $this->normalizeGeminiPackage($decoded, $model);
    }

    private function normalizeGeminiPackage(array $decoded, string $model): ?array
    {
        $prompt = trim((string) ($decoded['prompt'] ?? ''));
        if ($prompt === '') {
            return null;
        }

        $negative = trim((string) ($decoded['negative_prompt'] ?? ''));
        $negative = $negative !== ''
            ? trim($negative . ', ' . self::NEGATIVE_PROMPT)
            : self::NEGATIVE_PROMPT;

        return [
            'title' => Str::limit(trim((string) ($decoded['title'] ?? 'Cinematic Product Scene')), 90, ''),
            'concept' => Str::limit(trim((string) ($decoded['concept'] ?? 'A cinematic physical product scene generated by Gemini.')), 300, ''),
            'prompt' => $this->sanitize($prompt),
            'negative_prompt' => $this->sanitize($negative),
            'style_tags' => $this->stringList($decoded['style_tags'] ?? []),
            'category' => Str::slug((string) ($decoded['category'] ?? 'premium_object'), '_') ?: 'premium_object',
            'scene' => $this->sanitize(Str::limit((string) ($decoded['scene'] ?? ''), 500, '')),
            'environment' => $this->sanitize(Str::limit((string) ($decoded['environment'] ?? ''), 500, '')),
            'camera' => $this->sanitize(Str::limit((string) ($decoded['camera'] ?? '50mm cinematic product perspective, realistic depth of field'), 240, '')),
            'lighting' => $this->sanitize(Str::limit((string) ($decoded['lighting'] ?? 'cinematic studio lighting, elegant reflections, dramatic shadows'), 260, '')),
            'mood' => $this->sanitize(Str::limit((string) ($decoded['mood'] ?? 'premium, cinematic, refined, realistic'), 220, '')),
            'colors' => $this->stringList($decoded['colors'] ?? []),
            'engine' => 'gemini',
            'engine_model' => $model,
        ];
    }

    private function decodeGeminiJson(string $text): array
    {
        $cleaned = trim($text);
        $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/iu', '', $cleaned) ?? $cleaned;
        $cleaned = trim($cleaned);

        $decoded = json_decode($cleaned, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($cleaned, '{');
        $end = strrpos($cleaned, '}');
        if ($start === false || $end === false || $end <= $start) {
            return [];
        }

        $decoded = json_decode(substr($cleaned, $start, $end - $start + 1), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function stringList(mixed $items): array
    {
        if (is_string($items)) {
            $items = preg_split('/[,|]/', $items) ?: [];
        }

        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $item): string => Str::limit(trim((string) $item), 80, ''),
            $items
        )));
    }

    private function title(string $subject, string $category): string
    {
        $prefix = match ($category) {
            'artwork' => 'Cinematic Gallery Scene',
            'beauty' => 'Cinematic Beauty Scene',
            'technology' => 'Cinematic Technology Scene',
            'food' => 'Cinematic Culinary Scene',
            'fashion' => 'Cinematic Fashion Scene',
            default => 'Cinematic Product Scene',
        };

        return $prefix . ' - ' . Str::limit(Str::headline($subject), 58, '');
    }

    private function concept(string $subject, string $category, string $environment): string
    {
        return "A photographed cinematic {$category} scene where {$subject} exists naturally inside {$environment}.";
    }

    private function sanitize(string $text): string
    {
        return str_ireplace(
            [
                'social media layout',
                'social media canvas',
                'advertising canvas',
                'poster composition',
                'typography composition',
                'graphic template',
                'marketing flyer',
                'behance',
                'branding language',
                'premium branding',
                'brand campaign',
                'ad campaign',
                'campaign',
            ],
            [
                'physical photographed scene',
                'physical photographed scene',
                'physical photographed scene',
                'cinematic physical framing',
                'cinematic physical framing',
                'real photographed environment',
                'real photographed environment',
                'professional photography',
                'visual constraints',
                'refined visual identity',
                'cinematic scene',
                'cinematic scene',
                'scene',
            ],
            $text
        );
    }
}
