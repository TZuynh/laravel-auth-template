<?php

namespace App\Services\Marketing;

use App\Models\AiImageGeneration;
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
    public function __construct(private readonly AiProviderManager $providers)
    {
    }

    public function generate(User $user, array $data): AiImageGeneration
    {
        $product = !empty($data['product_id'])
            ? Product::query()->find((int) $data['product_id'])
            : null;

        $aspectRatio = (string) ($data['aspect_ratio'] ?? '9:16');
        $format = $this->format($aspectRatio);
        $prompt = $this->basePrompt($product, $data);
        $generation = AiImageGeneration::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'product_id' => $product?->id,
            'provider' => (string) ($data['provider'] ?? 'openai'),
            'model' => $data['model'] ?? null,
            'style' => (string) ($data['style'] ?? 'cinematic'),
            'aspect_ratio' => $aspectRatio,
            'status' => 'running',
            'prompt' => (string) ($data['prompt'] ?? ''),
            'optimized_prompt' => $prompt,
            'negative_prompt' => $this->negativePrompt(),
            'metadata' => [
                'product_name' => $product?->name,
                'audience' => $data['audience'] ?? 'social commerce',
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

    private function basePrompt(?Product $product, array $data): string
    {
        $productName = $product?->name ?: 'premium ecommerce product';
        $brief = $product?->seo_description ?: $product?->category ?: 'high-converting product visual';
        $style = (string) ($data['style'] ?? 'cinematic');
        $audience = (string) ($data['audience'] ?? 'TikTok shoppers');
        $prompt = trim((string) ($data['prompt'] ?? ''));

        return trim(implode("\n", array_filter([
            "Create a premium AI marketing image for: {$productName}.",
            "Product context: {$brief}.",
            "Audience: {$audience}.",
            "Visual style: {$style}.",
            $prompt !== '' ? "Creative direction: {$prompt}." : null,
            'Cinematic ecommerce photography, realistic product lighting, premium reflections, depth of field, social-media ad composition, no flat vector art, no PowerPoint layout.',
        ])));
    }

    private function negativePrompt(): string
    {
        return 'flat slideshow, low resolution, distorted text, messy layout, watermark, duplicate product, cartoon UI mockup';
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
        $this->paintCopy($image, $generation, $product, $width, $height);

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
            'luxury' => [[7, 10, 24], [88, 28, 135], [234, 179, 8]],
            'cyberpunk' => [[2, 6, 23], [14, 165, 233], [217, 70, 239]],
            'minimalist' => [[15, 23, 42], [51, 65, 85], [226, 232, 240]],
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
            Str::limit($generation->prompt ?: 'Cinematic product image generated for marketing campaigns.', 150),
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
