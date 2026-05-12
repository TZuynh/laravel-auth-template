<?php

namespace App\Services\Marketing;

use App\Models\AiImageGeneration;
use App\Models\BrainMemory;
use App\Models\Product;
use App\Models\User;
use App\Services\AI\AiProviderManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AiImageGenerationService
{
    public function __construct(
        private readonly AiProviderManager $providers,
        private readonly CommercialImagePromptService $promptEngine,
    ) {
    }

    public function generate(User $user, array $data): AiImageGeneration
    {
        $product = !empty($data['product_id'])
            ? Product::query()->find((int) $data['product_id'])
            : null;

        $creativeBrief = trim((string) ($data['prompt'] ?? ''));
        if ($creativeBrief === '' || (bool) ($data['random'] ?? false)) {
            $creativeBrief = $this->randomCreativeBrief($product, $data);
        }
        $data['prompt'] = $creativeBrief;

        $aspectRatio = (string) ($data['aspect_ratio'] ?? '9:16');
        $format = $this->format($aspectRatio);
        $brain = $this->brainContext($user, $creativeBrief);
        $promptPackage = $this->promptEngine->build($product, $data, $brain);
        $prompt = $promptPackage['prompt'];
        $negativePrompt = $promptPackage['negative_prompt'];
        $generation = AiImageGeneration::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'product_id' => $product?->id,
            'provider' => (string) ($data['provider'] ?? 'pollinations'),
            'model' => $data['model'] ?? null,
            'style' => (string) ($data['style'] ?? 'cinematic'),
            'aspect_ratio' => $aspectRatio,
            'status' => 'running',
            'prompt' => $creativeBrief,
            'optimized_prompt' => $prompt,
            'negative_prompt' => $negativePrompt,
            'metadata' => [
                'product_name' => $product?->name,
                'audience' => $data['audience'] ?? 'visual scene',
                'brain_memory_count' => count($brain),
                'random' => (bool) ($data['random'] ?? false),
                'prompt_package' => $promptPackage,
            ],
        ]);

        if (($data['provider'] ?? null) === 'local-cinematic') {
            $stored = $this->createLocalFallback($generation, $product, $format);
            $generation->update([
                'provider' => 'local-cinematic',
                'model' => 'gd-fallback',
                'status' => 'completed',
                'image_path' => $stored['path'],
                'mime_type' => $stored['mime'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'metadata' => array_replace($generation->metadata ?? [], [
                    'source' => 'local_fallback',
                ]),
            ]);

            return $generation->refresh();
        }

        try {
            $response = $this->providers->generate('image', [
                'prompt' => $prompt,
                'model' => $data['model'] ?: null,
                'size' => $this->providerSize((string) ($data['model'] ?? ''), $aspectRatio),
                'style' => $generation->style,
                'aspect_ratio' => $aspectRatio,
                'negative_prompt' => $negativePrompt,
                'skip_prompt_optimization' => true,
            ], $data['provider'] ?? null);

            $stored = $this->storeProviderImage($generation, $response->data, $format);
            if (!$stored) {
                throw new \RuntimeException('AI provider did not return image binary or URL.');
            }

            $generation->update([
                'provider' => $response->provider,
                'status' => 'completed',
                'image_path' => $stored['path'],
                'mime_type' => $stored['mime'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'metadata' => array_replace($generation->metadata ?? [], [
                    'source' => 'provider',
                    'tokens_used' => $response->tokensUsed,
                    'cost' => $response->cost,
                ]),
            ]);
        } catch (Throwable $exception) {
            $stored = $this->createLocalFallback($generation, $product, $format);
            $generation->update([
                'provider' => 'local-cinematic',
                'model' => 'gd-fallback',
                'status' => 'completed',
                'image_path' => $stored['path'],
                'mime_type' => $stored['mime'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'metadata' => array_replace($generation->metadata ?? [], [
                    'source' => 'local_fallback',
                    'fallback_reason' => Str::limit($exception->getMessage(), 500),
                ]),
            ]);
        }

        return $generation->refresh();
    }

    public function delete(AiImageGeneration $generation): void
    {
        if ($generation->image_path) {
            Storage::disk('public')->delete($generation->image_path);
        }

        if ($generation->thumbnail_path) {
            Storage::disk('public')->delete($generation->thumbnail_path);
        }

        $generation->delete();
    }

    private function randomCreativeBrief(?Product $product, array $data): string
    {
        $subject = $product?->name ?: 'hero product';
        $briefs = [
            "Luxury contemporary art gallery interior with {$subject} mounted on textured concrete wall, warm cinematic spotlight, reflective polished floor",
            "Ultra realistic minimal studio scene with {$subject} on dark glass, cinematic rim light, architectural shadows, rich material texture",
            "High-end interior photography scene with {$subject}, warm spotlight, soft volumetric lighting, dramatic contrast, natural depth of field",
            "Quiet museum-grade environment with {$subject}, charcoal wall texture, polished floor reflections, refined spatial depth",
            "Editorial physical scene with {$subject}, natural perspective, tactile surfaces, controlled shadows, premium realism",
        ];

        return $briefs[array_rand($briefs)];
    }

    private function brainContext(User $user, string $prompt): array
    {
        return BrainMemory::query()
            ->where('user_id', $user->id)
            ->whereIn('category', ['brand_rule', 'usp', 'voice_style', 'customer_insight'])
            ->latest('id')
            ->limit(6)
            ->get(['category', 'topic', 'content'])
            ->map(fn (BrainMemory $memory): array => [
                'category' => $memory->category,
                'topic' => $memory->topic,
                'content' => Str::limit($memory->content, 420),
            ])
            ->all();
    }

    private function styleDirection(string $style): string
    {
        return match ($style) {
            'premium_packshot' => 'glossy physical surface, exact product silhouette, studio reflection, strong hero framing',
            'luxury_editorial' => 'editorial interior photography, magazine-grade lighting, elegant props, refined contrast, expensive mood',
            'clean_studio' => 'minimal clean studio, softbox lighting, crisp shadows, calm background, modern retail framing',
            'lifestyle_ad' => 'realistic lifestyle scene, human context, natural environment, emotional spatial detail',
            'social_viral' => 'bold physical scene, high contrast, dramatic color, dynamic crop, natural lens framing',
            'canvas_campaign', 'award_campaign' => 'contemporary gallery scene, sculptural framing, refined color harmony, premium atmosphere',
            'luxury' => 'luxury editorial interior photography, premium reflections, dark elegant lighting',
            'minimalist' => 'minimal clean studio, restrained colors, precise product placement',
            'cyberpunk' => 'neon social visual, cinematic glow, dramatic contrast',
            default => 'cinematic product photography, premium light, depth of field, polished physical framing',
        };
    }

    private function providerSize(string $model, string $aspectRatio): string
    {
        $isGptImage = str_contains(strtolower($model), 'gpt-image');

        return match ($aspectRatio) {
            '16:9' => $isGptImage ? '1536x1024' : '1792x1024',
            '1:1' => '1024x1024',
            '4:5' => $isGptImage ? '1024x1536' : '1024x1792',
            default => $isGptImage ? '1024x1536' : '1024x1792',
        };
    }

    private function format(string $aspectRatio): array
    {
        return match ($aspectRatio) {
            '16:9' => ['width' => 1600, 'height' => 900],
            '1:1' => ['width' => 1200, 'height' => 1200],
            '4:5' => ['width' => 1200, 'height' => 1500],
            default => ['width' => 1080, 'height' => 1920],
        };
    }

    private function storeProviderImage(AiImageGeneration $generation, array $data, array $format): ?array
    {
        $binary = null;
        $mime = 'image/png';

        if (!empty($data['b64_json'])) {
            $binary = base64_decode((string) $data['b64_json'], true) ?: null;
        }

        if (!$binary && !empty($data['binary'])) {
            $binary = (string) $data['binary'];
            $mime = (string) ($data['mime'] ?? $mime);
        }

        if (!$binary && !empty($data['url'])) {
            $response = Http::timeout(60)->get((string) $data['url']);
            if ($response->ok()) {
                $binary = $response->body();
                $mime = (string) ($response->header('Content-Type') ?: 'image/png');
            }
        }

        if (!$binary) {
            return null;
        }

        $size = @getimagesizefromstring($binary);
        $path = 'ai-images/' . $generation->uuid . '.png';
        Storage::disk('public')->put($path, $binary);

        return [
            'path' => $path,
            'mime' => $mime,
            'width' => (int) ($size[0] ?? $format['width']),
            'height' => (int) ($size[1] ?? $format['height']),
        ];
    }

    private function createLocalFallback(AiImageGeneration $generation, ?Product $product, array $format): array
    {
        $width = $format['width'];
        $height = $format['height'];
        $image = imagecreatetruecolor($width, $height);

        $this->paintBackground($image, $width, $height, (string) $generation->style);
        $this->paintLight($image, $width, $height);
        $this->paintProduct($image, $product, $width, $height);

        $path = 'ai-images/' . $generation->uuid . '.png';
        $absolute = Storage::disk('public')->path($path);
        File::ensureDirectoryExists(dirname($absolute));
        imagepng($image, $absolute, 6);
        imagedestroy($image);

        return [
            'path' => $path,
            'mime' => 'image/png',
            'width' => $width,
            'height' => $height,
        ];
    }

    private function paintBackground(\GdImage $image, int $width, int $height, string $style): void
    {
        $palette = match ($style) {
            'luxury', 'luxury_editorial' => [[8, 10, 18], [56, 42, 38], [232, 199, 126]],
            'cyberpunk', 'social_viral' => [[3, 7, 18], [18, 91, 120], [236, 72, 153]],
            'canvas_campaign', 'award_campaign' => [[16, 24, 39], [52, 64, 84], [125, 211, 252]],
            'minimalist', 'clean_studio' => [[235, 239, 246], [203, 213, 225], [59, 130, 246]],
            'lifestyle_ad' => [[32, 45, 38], [88, 120, 92], [246, 214, 170]],
            'premium_packshot' => [[10, 15, 28], [31, 41, 55], [147, 197, 253]],
            default => [[2, 6, 23], [30, 64, 175], [168, 85, 247]],
        };

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $r = (int) ($palette[0][0] + ($palette[1][0] - $palette[0][0]) * $ratio);
            $g = (int) ($palette[0][1] + ($palette[1][1] - $palette[0][1]) * $ratio);
            $b = (int) ($palette[0][2] + ($palette[1][2] - $palette[0][2]) * $ratio);
            imageline($image, 0, $y, $width, $y, imagecolorallocate($image, $r, $g, $b));
        }

        imagefilledellipse(
            $image,
            (int) ($width * .78),
            (int) ($height * .24),
            (int) ($width * .7),
            (int) ($height * .36),
            imagecolorallocatealpha($image, $palette[2][0], $palette[2][1], $palette[2][2], 95)
        );
    }

    private function paintLight(\GdImage $image, int $width, int $height): void
    {
        $white = imagecolorallocatealpha($image, 255, 255, 255, 95);
        for ($i = -3; $i < 7; $i++) {
            imageline($image, (int) ($width * ($i / 6)), 0, (int) ($width * (($i + 4) / 6)), $height, $white);
        }

        imagefilledrectangle(
            $image,
            0,
            (int) ($height * .72),
            $width,
            $height,
            imagecolorallocatealpha($image, 2, 6, 23, 42)
        );
    }

    private function paintProduct(\GdImage $image, ?Product $product, int $width, int $height): void
    {
        $stageX = (int) ($width * .18);
        $stageY = (int) ($height * .22);
        $stageW = (int) ($width * .64);
        $stageH = (int) ($height * .42);
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, 72);
        $frame = imagecolorallocate($image, 248, 250, 252);
        $panel = imagecolorallocate($image, 18, 24, 38);

        imagefilledellipse($image, (int) ($width * .52), (int) ($height * .64), (int) ($stageW * 1.05), (int) ($stageH * .22), $shadow);
        imagefilledrectangle($image, $stageX, $stageY, $stageX + $stageW, $stageY + $stageH, $frame);
        imagefilledrectangle($image, $stageX + 18, $stageY + 18, $stageX + $stageW - 18, $stageY + $stageH - 18, $panel);

        $source = $this->productImageBinary($product);
        if ($source) {
            $productImage = @imagecreatefromstring($source);
            if ($productImage instanceof \GdImage) {
                $srcW = imagesx($productImage);
                $srcH = imagesy($productImage);
                $maxW = (int) ($stageW * .78);
                $maxH = (int) ($stageH * .72);
                $scale = min($maxW / max(1, $srcW), $maxH / max(1, $srcH));
                $dstW = (int) ($srcW * $scale);
                $dstH = (int) ($srcH * $scale);
                imagecopyresampled(
                    $image,
                    $productImage,
                    $stageX + (int) (($stageW - $dstW) / 2),
                    $stageY + (int) (($stageH - $dstH) / 2),
                    0,
                    0,
                    $dstW,
                    $dstH,
                    $srcW,
                    $srcH
                );
                imagedestroy($productImage);

                return;
            }
        }

        $accent = imagecolorallocate($image, 96, 165, 250);
        imagefilledellipse($image, (int) ($width * .5), (int) ($height * .43), (int) ($stageW * .38), (int) ($stageW * .38), $accent);
        imagefilledrectangle($image, (int) ($width * .39), (int) ($height * .36), (int) ($width * .61), (int) ($height * .52), imagecolorallocate($image, 226, 232, 240));
    }

    private function paintCopy(\GdImage $image, AiImageGeneration $generation, ?Product $product, int $width, int $height): void
    {
        $fontBold = $this->font(true);
        $fontRegular = $this->font(false);
        $white = imagecolorallocate($image, 255, 255, 255);
        $muted = imagecolorallocate($image, 203, 213, 225);
        $blue = imagecolorallocate($image, 96, 165, 250);
        $x = (int) ($width * .08);
        $titleY = (int) ($height * .08);

        $this->text($image, 'AI IMAGE AD', $x, $titleY, (int) max(18, $width * .025), $blue, $fontBold);
        $this->wrapText(
            $image,
            Str::upper($product?->name ?: 'PREMIUM PRODUCT VISUAL'),
            $x,
            $titleY + (int) ($height * .055),
            (int) ($width * .84),
            (int) max(34, $width * .052),
            $white,
            $fontBold,
            2
        );
        $this->wrapText(
            $image,
            Str::limit($generation->prompt ?: 'Cinematic physical scene generated for visual exploration.', 150),
            $x,
            (int) ($height * .76),
            (int) ($width * .84),
            (int) max(20, $width * .03),
            $muted,
            $fontRegular,
            3
        );
    }

    private function productImageBinary(?Product $product): ?string
    {
        $path = trim((string) ($product?->image ?? ''));
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return rescue(function () use ($path): ?string {
                $response = Http::timeout(8)->get($path);

                return $response->ok() ? $response->body() : null;
            }, null, false);
        }

        $absolute = Storage::disk('public')->path(ltrim($path, '/'));

        return File::exists($absolute) ? File::get($absolute) : null;
    }

    private function wrapText(\GdImage $image, string $text, int $x, int $y, int $maxWidth, int $size, int $color, ?string $font, int $maxLines): void
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $line = '';
        $lines = [];

        foreach ($words as $word) {
            $candidate = trim($line . ' ' . $word);
            if ($this->textWidth($candidate, $size, $font) <= $maxWidth || $line === '') {
                $line = $candidate;
                continue;
            }

            $lines[] = $line;
            $line = $word;
            if (count($lines) >= $maxLines) {
                break;
            }
        }

        if ($line !== '' && count($lines) < $maxLines) {
            $lines[] = $line;
        }

        foreach (array_slice($lines, 0, $maxLines) as $index => $lineText) {
            if ($index === $maxLines - 1 && count($words) > count(preg_split('/\s+/u', implode(' ', $lines)) ?: [])) {
                $lineText = rtrim($lineText, ' .,') . '...';
            }
            $this->text($image, $lineText, $x, $y + ($index * (int) ($size * 1.22)), $size, $color, $font);
        }
    }

    private function text(\GdImage $image, string $text, int $x, int $y, int $size, int $color, ?string $font): void
    {
        if ($font && File::exists($font)) {
            imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
            return;
        }

        imagestring($image, 5, $x, $y, $text, $color);
    }

    private function textWidth(string $text, int $size, ?string $font): int
    {
        if ($font && File::exists($font)) {
            $box = imagettfbbox($size, 0, $font, $text);

            return abs(($box[2] ?? 0) - ($box[0] ?? 0));
        }

        return strlen($text) * imagefontwidth(5);
    }

    private function font(bool $bold): ?string
    {
        $candidates = $bold
            ? ['C:\Windows\Fonts\arialbd.ttf', 'C:\Windows\Fonts\segoeuib.ttf']
            : ['C:\Windows\Fonts\arial.ttf', 'C:\Windows\Fonts\segoeui.ttf'];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
