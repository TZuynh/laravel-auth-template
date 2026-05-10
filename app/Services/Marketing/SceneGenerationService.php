<?php

namespace App\Services\Marketing;

class SceneGenerationService
{
    public function generateMarketingScript(array $context): string
    {
        $language = $this->language($context);
        $product = $context['product_name'] ?? 'your product';
        $brief = $context['product_brief'] ?? ($language === 'vi' ? 'sản phẩm nổi bật cho khách hàng hiện đại' : 'a standout product for modern customers');
        $tone = $context['tone'] ?? 'premium';

        if ($language === 'vi') {
            return "Bạn có biết {$product} có thể biến một khoảnh khắc bình thường thành lý do để khách hàng dừng cuộn? "
                . "Mở đầu bằng ánh sáng điện ảnh, đưa sản phẩm vào trung tâm khung hình, nhấn vào {$brief}, "
                . "sau đó kết bằng lời kêu gọi hành động ngắn gọn, cao cấp và dễ nhớ. Tông giọng: {$tone}.";
        }

        return "What if {$product} could turn an ordinary scroll into a premium buying moment? "
            . "Open with cinematic light, place the product at the center of the frame, highlight {$brief}, "
            . "then close with a short memorable call to action. Tone: {$tone}.";
    }

    public function generateScenes(string $script, array $context): array
    {
        $language = $this->language($context);
        $style = $context['style'] ?? 'cinematic';
        $product = $context['product_name'] ?? ($language === 'vi' ? 'sản phẩm' : 'product');
        $sentences = $this->splitScript($script);
        $beats = $this->sceneBeats($language);
        $cameras = $this->cameraMap($style);
        $transitions = ['bloom-light-cut', 'parallax-push', 'whip-pan-glow', 'bloom-light-cut'];

        return collect($beats)->map(function (array $beat, int $index) use ($context, $product, $sentences, $style, $cameras, $transitions): array {
            $voice = $sentences[$index] ?? $beat['voice'];
            $camera = $cameras[$index] ?? 'dolly_in';
            $duration = (float) ($beat['duration'] ?? 2.5);

            return [
                'sort_order' => $index + 1,
                'title' => $beat['title'],
                'cinematic_description' => $this->cinematicDescription($beat['title'], $product, $camera, $style, $context),
                'ai_prompt' => $this->aiPrompt($beat['title'], $product, $voice, $style, $context),
                'image_prompt' => $this->imagePrompt($product, $beat['title'], $style, $context),
                'video_prompt' => $this->videoPrompt($product, $camera, $style, $context),
                'camera_movement' => $camera,
                'voice_over_text' => $voice,
                'duration_seconds' => $duration,
                'transition_type' => $transitions[$index],
                'animation_style' => $this->animationStyle($style),
                'subtitle_text' => $this->subtitle($voice),
            ];
        })->all();
    }

    public function suggestHooks(array $context): array
    {
        $language = $this->language($context);
        $product = $context['product_name'] ?? ($language === 'vi' ? 'sản phẩm này' : 'this product');

        if ($language === 'vi') {
            return [
                "Đừng lướt qua trước khi thấy {$product} được dựng như phim quảng cáo cao cấp.",
                "Một khung hình đủ khiến khách hàng muốn chạm vào {$product}.",
                "Nếu {$product} chỉ có 3 giây để gây ấn tượng, đây là cách mở đầu.",
            ];
        }

        return [
            "Do not scroll before seeing {$product} filmed like a luxury commercial.",
            "One frame that makes customers want to reach for {$product}.",
            "If {$product} had three seconds to impress, this is the opening.",
        ];
    }

    public function suggestCtas(array $context): array
    {
        $language = $this->language($context);

        return $language === 'vi'
            ? ['Mua ngay hôm nay.', 'Khám phá phiên bản premium.', 'Tạo khoảnh khắc đáng nhớ cho đơn hàng tiếp theo.']
            : ['Shop it today.', 'Discover the premium edition.', 'Create a memorable moment with your next order.'];
    }

    private function splitScript(string $script): array
    {
        $parts = preg_split('/(?<=[.!?。！？])\s+/u', trim($script)) ?: [];

        return collect($parts)
            ->map(fn (string $part): string => trim($part))
            ->filter()
            ->values()
            ->take(4)
            ->all();
    }

    private function sceneBeats(string $language): array
    {
        if ($language === 'vi') {
            return [
                ['title' => 'Hook mở đầu', 'voice' => 'Dừng lại một giây, đây không chỉ là một sản phẩm.', 'duration' => 2.2],
                ['title' => 'Product reveal', 'voice' => 'Ánh sáng mở ra, sản phẩm bước vào trung tâm như một nhân vật chính.', 'duration' => 3.0],
                ['title' => 'Feature transformation', 'voice' => 'Từng chi tiết được biến thành cảm giác tin tưởng, cao cấp và muốn sở hữu.', 'duration' => 3.2],
                ['title' => 'CTA kết thúc', 'voice' => 'Chọn sản phẩm hôm nay và biến trải nghiệm mua sắm thành một khoảnh khắc đáng nhớ.', 'duration' => 2.6],
            ];
        }

        return [
            ['title' => 'Hook opening', 'voice' => 'Pause for one second, this is not just another product.', 'duration' => 2.2],
            ['title' => 'Product reveal', 'voice' => 'The light opens, and the product steps into frame like the hero.', 'duration' => 3.0],
            ['title' => 'Feature transformation', 'voice' => 'Every detail becomes a reason to trust it, desire it, and remember it.', 'duration' => 3.2],
            ['title' => 'CTA ending', 'voice' => 'Choose it today and turn a simple purchase into a memorable moment.', 'duration' => 2.6],
        ];
    }

    private function cameraMap(string $style): array
    {
        return match ($style) {
            'viral_tiktok' => ['handheld_push', 'whip_pan', 'snap_zoom', 'dolly_out'],
            'luxury' => ['slow_dolly_in', 'orbit', 'macro_push', 'light_sweep_pullback'],
            'product_showcase' => ['orbit', 'macro_slide', 'parallax_push', 'center_lockup'],
            default => ['dolly_in', 'orbit', 'cinematic_zoom', 'dolly_out'],
        };
    }

    private function cinematicDescription(string $title, string $product, string $camera, string $style, array $context): string
    {
        $character = $context['character'] ?? 'none';
        $gender = $context['gender'] ?? 'neutral';

        return "{$title}: {$camera} camera movement around {$product}, {$style} lighting, layered parallax depth, realistic reflections, motion blur, film grain, character={$character}, gender={$gender}.";
    }

    private function aiPrompt(string $title, string $product, string $voice, string $style, array $context): string
    {
        $aspect = $context['aspect_ratio'] ?? '9:16';

        return "{$title} for {$product}. Style {$style}, aspect {$aspect}. Voice-over: {$voice}. Use cinematic pacing, premium lighting, social media retention, no flat slideshow composition.";
    }

    private function imagePrompt(string $product, string $title, string $style, array $context): string
    {
        return "Ultra realistic {$style} ecommerce commercial still, {$product}, {$title}, dark gallery lighting, premium reflections, shallow depth of field, volumetric rim light, high-end product photography.";
    }

    private function videoPrompt(string $product, string $camera, string $style, array $context): string
    {
        return "Cinematic AI video of {$product}, {$camera}, {$style} commercial, parallax foreground and background, animated light sweep, subtle handheld realism, motion blur, bloom, depth of field, premium color grading.";
    }

    private function animationStyle(string $style): string
    {
        return match ($style) {
            'viral_tiktok' => 'kinetic_typography_snap_cuts',
            'luxury' => 'slow_parallax_light_sweep',
            'product_showcase' => 'macro_orbit_depth_layers',
            default => 'cinematic_parallax',
        };
    }

    private function subtitle(string $voice): string
    {
        return mb_strlen($voice) > 96 ? mb_substr($voice, 0, 93) . '...' : $voice;
    }

    private function language(array $context): string
    {
        return ($context['language'] ?? app()->getLocale()) === 'en' ? 'en' : 'vi';
    }
}
