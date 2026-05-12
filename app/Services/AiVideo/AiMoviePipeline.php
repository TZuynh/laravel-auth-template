<?php

namespace App\Services\AiVideo;

use Illuminate\Support\Str;

class AiMoviePipeline
{
    public function __construct(private readonly OpenAiMoviePlanner $openAiPlanner)
    {
    }

    public function plan(string $prompt, array $options = []): array
    {
        $language = (string) ($options['language'] ?? 'vi');
        $duration = (int) ($options['duration_seconds'] ?? 30);
        $aspectRatio = (string) ($options['aspect_ratio'] ?? '9:16');
        $preset = (array) ($options['preset'] ?? []);
        $editorSettings = (array) ($options['editor_settings'] ?? []);

        $aiPlan = rescue(fn (): ?array => $this->openAiPlanner->plan($prompt, $options), null, false);

        $script = is_array(data_get($aiPlan, 'script'))
            ? data_get($aiPlan, 'script')
            : $this->writeScript($prompt, $language, $duration, $preset);

        $scenes = is_array(data_get($aiPlan, 'scenes'))
            ? $this->normalizeAiScenes(data_get($aiPlan, 'scenes'), $duration, $aspectRatio)
            : $this->splitScenes($prompt, $script, $language, $duration, $aspectRatio, $preset);

        $scenes = $this->applySceneOverrides($scenes, (array) ($options['scene_overrides'] ?? []));
        $scenes = $this->generateScenePrompts($scenes, $prompt, $language, $aspectRatio, $preset, $editorSettings);
        $scenes = $this->attachAssetPlans($scenes, $prompt, $preset);
        $scenes = $this->attachSubtitleCues($scenes);
        $timeline = $this->composeTimeline($scenes, $aspectRatio, $duration, $preset);

        return [
            'pipeline' => [
                'AI Script Writer',
                'Scene Splitter',
                'Scene Prompt Generator',
                'Asset Finder',
                'Voice Generator',
                'Subtitle Generator',
                'Timeline Composer',
                'Transition Engine',
                'Motion Engine',
                'Remotion Timeline Render',
                'FFmpeg Final Encode',
            ],
            'planner' => [
                'provider' => $aiPlan ? 'openai' : 'deterministic_fallback',
                'model' => $aiPlan ? (string) config('ai_video_platform.openai.model', 'gpt-5.5') : 'local-rule-engine',
            ],
            'script' => $script,
            'scenes' => $scenes,
            'timeline' => $timeline,
        ];
    }

    private function writeScript(string $prompt, string $language, int $duration, array $preset): array
    {
        $subject = $this->subject($prompt);
        $style = (string) ($preset['visual_direction'] ?? 'cinematic social video');

        if ($language === 'en') {
            return [
                'hook' => "Stop scrolling. {$subject} changes the first impression in seconds.",
                'problem' => "The old version feels tired, flat, and easy to ignore.",
                'emotion' => "Show the moment where the audience sees the difference and feels the upgrade.",
                'solution' => "Reveal the product benefit with {$style}, clear proof, and a confident rhythm.",
                'cta' => "Make the switch today and turn attention into action.",
                'full' => "{$prompt}. Build a {$duration}s cinematic social video with a sharp 0-3s hook, fast visual pacing, word-by-word subtitles, sound accents, and a direct CTA.",
            ];
        }

        return [
            'hook' => "Dung lai 1 giay. {$subject} tao an tuong dau tien nhanh hon ban nghi.",
            'problem' => "Phien ban cu trong met moi, thieu suc song va de bi luot qua.",
            'emotion' => "Cho thay khoanh khac nguoi xem nhan ra su thay doi va cam thay tin tuong hon.",
            'solution' => "Mo khoa loi ich san pham bang {$style}, bang chung ro va nhip dung chac.",
            'cta' => "Hay bat dau hom nay va bien su chu y thanh hanh dong.",
            'full' => "{$prompt}. Dung video {$duration}s theo timeline dien anh, hook manh 0-3s, pacing nhanh, phu de word-by-word, sound effect va CTA ro rang.",
        ];
    }

    private function splitScenes(string $prompt, array $script, string $language, int $duration, string $aspectRatio, array $preset): array
    {
        $durations = $this->sceneDurations($duration);
        $beats = [
            ['type' => 'hook', 'title' => 'Hook', 'copy' => $script['hook'], 'shot' => 'extreme close up', 'camera' => 'snap_zoom', 'tone' => 'urgent curiosity', 'pacing' => 'fast', 'sfx' => 'impact_hit'],
            ['type' => 'problem', 'title' => 'Problem', 'copy' => $script['problem'], 'shot' => 'close up', 'camera' => 'handheld_push', 'tone' => 'relatable tension', 'pacing' => 'fast', 'sfx' => 'soft_whoosh'],
            ['type' => 'emotion', 'title' => 'Emotion', 'copy' => $script['emotion'], 'shot' => 'medium shot', 'camera' => 'parallax_push', 'tone' => 'emotional lift', 'pacing' => 'medium', 'sfx' => 'rise'],
            ['type' => 'solution', 'title' => 'Solution', 'copy' => $script['solution'], 'shot' => 'hero product shot', 'camera' => 'cinematic_zoom', 'tone' => 'confidence', 'pacing' => 'medium', 'sfx' => 'shine'],
            ['type' => 'cta', 'title' => 'CTA', 'copy' => $script['cta'], 'shot' => 'clean end card', 'camera' => 'slow_dolly_in', 'tone' => 'decisive', 'pacing' => 'clean', 'sfx' => 'button_pop'],
        ];

        $cursor = 0.0;

        return collect($beats)->map(function (array $beat, int $index) use (&$cursor, $durations, $prompt, $language, $aspectRatio, $preset): array {
            $duration = $durations[$index] ?? 3.0;
            $visual = $this->visualForBeat($beat['type'], $prompt, $language);
            $transition = $this->transitionForBeat($beat['type'], $index, $preset);
            $scene = [
                'scene' => $index + 1,
                'sort_order' => $index + 1,
                'type' => $beat['type'],
                'title' => $beat['title'],
                'start' => round($cursor, 3),
                'duration_seconds' => round($duration, 3),
                'shot_type' => $beat['shot'],
                'camera' => $beat['camera'],
                'camera_movement' => $beat['camera'],
                'visual' => $visual,
                'b_roll_direction' => $this->brollForBeat($beat['type'], $prompt),
                'voice_over' => $beat['copy'],
                'subtitle' => $this->subtitle($beat['copy'], $language, $beat['type']),
                'transition' => $transition,
                'sound_effect' => $beat['sfx'],
                'pacing' => $beat['pacing'],
                'emotional_tone' => $beat['tone'],
                'aspect_ratio' => $aspectRatio,
            ];
            $cursor += $duration;

            return $scene;
        })->all();
    }

    private function normalizeAiScenes(array $scenes, int $duration, string $aspectRatio): array
    {
        $cursor = 0.0;
        $sceneCount = max(1, min(8, count($scenes)));

        return collect($scenes)
            ->take(8)
            ->values()
            ->map(function (array $scene, int $index) use (&$cursor, $duration, $aspectRatio, $sceneCount): array {
                $sceneDuration = round(max(1.4, min(30, (float) ($scene['duration_seconds'] ?? ($duration / $sceneCount)))), 3);
                $type = Str::slug((string) ($scene['type'] ?? ($index === 0 ? 'hook' : 'story')), '_');
                $camera = Str::limit((string) ($scene['camera'] ?? $scene['camera_movement'] ?? 'cinematic_zoom'), 80);

                $normalized = [
                    'scene' => (int) ($scene['scene'] ?? $index + 1),
                    'sort_order' => $index + 1,
                    'type' => $type ?: 'story',
                    'title' => Str::limit((string) ($scene['title'] ?? 'Scene ' . ($index + 1)), 120),
                    'start' => round($cursor, 3),
                    'duration_seconds' => $sceneDuration,
                    'shot_type' => (string) ($scene['shot_type'] ?? 'cinematic shot'),
                    'camera' => $camera,
                    'camera_movement' => $camera,
                    'visual' => (string) ($scene['visual'] ?? $scene['title'] ?? 'cinematic visual'),
                    'b_roll_direction' => (string) ($scene['b_roll_direction'] ?? 'cinematic b-roll with depth and foreground motion'),
                    'voice_over' => (string) ($scene['voice_over'] ?? $scene['subtitle'] ?? ''),
                    'subtitle' => (string) ($scene['subtitle'] ?? $scene['voice_over'] ?? $scene['title'] ?? ''),
                    'transition' => (string) ($scene['transition'] ?? ($index === 0 ? 'impact_zoom' : 'smooth_push')),
                    'sound_effect' => (string) ($scene['sound_effect'] ?? ($index === 0 ? 'impact_hit' : 'soft_whoosh')),
                    'pacing' => (string) ($scene['pacing'] ?? ($index === 0 ? 'fast' : 'medium')),
                    'emotional_tone' => (string) ($scene['emotional_tone'] ?? 'cinematic confidence'),
                    'aspect_ratio' => $aspectRatio,
                    'asset_plan' => (array) ($scene['asset_plan'] ?? []),
                    'motion' => (array) ($scene['motion'] ?? []),
                ];

                $cursor += $sceneDuration;

                return $normalized;
            })
            ->all();
    }

    private function generateScenePrompts(array $scenes, string $prompt, string $language, string $aspectRatio, array $preset, array $editorSettings): array
    {
        $visualDirection = (string) data_get($editorSettings, 'visual_direction', data_get($preset, 'visual_direction', 'cinematic commercial video'));
        $subtitleStyle = (string) data_get($editorSettings, 'subtitle_style', data_get($preset, 'subtitle_style', 'big animated captions'));

        return array_map(function (array $scene) use ($prompt, $visualDirection, $subtitleStyle, $aspectRatio): array {
            $base = "{$scene['shot_type']} of {$scene['visual']}; {$scene['b_roll_direction']}; {$scene['emotional_tone']}; aspect {$aspectRatio}; no unreadable text inside source footage";
            $scene['scene_prompt'] = $base;
            $scene['image_prompt'] = "{$base}; premium AI image, sharp details, controlled light, {$visualDirection}";
            $scene['video_prompt'] = "{$base}; AI video plate, camera {$scene['camera']}, cinematic motion, motion blur, depth, {$visualDirection}";
            $scene['negative_prompt'] = 'bad anatomy, distorted face, unreadable letters, watermark, low resolution, frozen flat slideshow';
            $scene['subtitle_style'] = $subtitleStyle;
            $scene['ai_prompt'] = "Generate scene {$scene['scene']} for: {$prompt}. {$base}. Subtitle: {$scene['subtitle']}";
            $scene['motion'] = is_array($scene['motion'] ?? null)
                ? array_replace($this->motionForCamera($scene['camera'], $scene['type']), $scene['motion'])
                : $this->motionForCamera($scene['camera'], $scene['type']);

            return $scene;
        }, $scenes);
    }

    private function attachAssetPlans(array $scenes, string $prompt, array $preset): array
    {
        return array_map(function (array $scene) use ($prompt, $preset): array {
            $needsMotionPlate = in_array($scene['type'], ['hook', 'emotion', 'solution'], true);
            $scene['asset_plan'] = array_replace([
                'primary' => $needsMotionPlate ? 'ai_video_or_stock_footage' : 'ai_image_with_motion',
                'fallback' => 'ai_image_with_ken_burns',
                'search_query' => $this->assetSearchQuery($scene, $prompt),
                'ai_image_provider' => (string) config('ai_video_platform.asset_engines.image', 'openai'),
                'ai_video_provider' => $needsMotionPlate ? (string) config('ai_video_platform.asset_engines.video', 'kling') : null,
                'fallback_video_provider' => (string) config('ai_video_platform.asset_engines.fallback_video', 'wan'),
                'local_motion_provider' => (string) config('ai_video_platform.asset_engines.local_motion', 'ltx'),
                'overlay' => $scene['type'] === 'hook' ? 'impact_flash' : ($scene['type'] === 'cta' ? 'cta_button_glow' : 'subtle_light_leak'),
                'music_cue' => (string) ($preset['music'] ?? 'Neutral Ambient Pulse'),
            ], (array) ($scene['asset_plan'] ?? []));

            return $scene;
        }, $scenes);
    }

    private function attachSubtitleCues(array $scenes): array
    {
        return array_map(function (array $scene): array {
            if (is_array($scene['subtitle_cues'] ?? null) && $scene['subtitle_cues'] !== []) {
                return $scene;
            }

            $words = preg_split('/\s+/u', trim($scene['subtitle'])) ?: [];
            $duration = max(0.8, (float) $scene['duration_seconds']);
            $wordDuration = $duration / max(1, count($words));
            $cursor = 0.0;

            $scene['subtitle_cues'] = collect($words)
                ->take(18)
                ->map(function (string $word) use (&$cursor, $wordDuration): array {
                    $cue = [
                        'word' => trim($word),
                        'start' => round($cursor, 3),
                        'end' => round($cursor + $wordDuration, 3),
                        'emphasis' => mb_strlen($word) >= 6,
                    ];
                    $cursor += $wordDuration;

                    return $cue;
                })
                ->values()
                ->all();

            return $scene;
        }, $scenes);
    }

    private function composeTimeline(array $scenes, string $aspectRatio, int $duration, array $preset): array
    {
        return [
            'output' => [
                'format' => 'mp4',
                'aspect_ratio' => $aspectRatio,
                'fps' => 30,
                'duration_seconds' => $duration,
                'renderer' => 'remotion',
                'final_encoder' => 'ffmpeg',
            ],
            'tracks' => [
                'visuals' => collect($scenes)->map(fn (array $scene): array => [
                    'scene' => $scene['scene'],
                    'start' => $scene['start'],
                    'duration' => $scene['duration_seconds'],
                    'asset_plan' => $scene['asset_plan'],
                    'motion' => $scene['motion'],
                    'transition' => $scene['transition'],
                ])->all(),
                'subtitles' => collect($scenes)->map(fn (array $scene): array => [
                    'scene' => $scene['scene'],
                    'style' => $scene['subtitle_style'],
                    'text' => $scene['subtitle'],
                    'cues' => $scene['subtitle_cues'],
                ])->all(),
                'audio' => collect($scenes)->map(fn (array $scene): array => [
                    'scene' => $scene['scene'],
                    'voice_over' => $scene['voice_over'],
                    'sound_effect' => $scene['sound_effect'],
                    'music_cue' => data_get($scene, 'asset_plan.music_cue'),
                ])->all(),
            ],
            'style' => [
                'visual_direction' => (string) ($preset['visual_direction'] ?? ''),
                'subtitle_style' => (string) ($preset['subtitle_style'] ?? 'big animated captions'),
                'pacing' => (string) ($preset['pacing'] ?? 'fast social pacing'),
            ],
        ];
    }

    private function sceneDurations(int $duration): array
    {
        $weights = [0.12, 0.18, 0.24, 0.31, 0.15];
        $durations = array_map(fn (float $weight): float => round(max(1.6, $duration * $weight), 3), $weights);
        $durations[0] = min(3.0, $durations[0]);
        $diff = $duration - array_sum($durations);
        $durations[3] = round(max(1.6, $durations[3] + $diff), 3);

        return $durations;
    }

    private function transitionForBeat(string $type, int $index, array $preset): string
    {
        $presetTransitions = (array) ($preset['transitions'] ?? []);

        return match ($type) {
            'hook' => 'impact_zoom',
            'problem' => 'whip_pan',
            'emotion' => 'light_leak',
            'solution' => $presetTransitions[$index] ?? 'smooth_push',
            'cta' => 'clean_fade',
            default => $presetTransitions[$index] ?? 'fade',
        };
    }

    private function motionForCamera(string $camera, string $type): array
    {
        return match ($camera) {
            'snap_zoom' => ['engine' => 'ken_burns', 'zoom_start' => 1.0, 'zoom_end' => 1.28, 'shake' => 0.012, 'motion_blur' => true, 'speed_ramp' => 'fast_in'],
            'handheld_push' => ['engine' => 'ken_burns', 'zoom_start' => 1.03, 'zoom_end' => 1.18, 'shake' => 0.008, 'motion_blur' => true, 'speed_ramp' => 'micro_ramp'],
            'parallax_push' => ['engine' => 'parallax', 'zoom_start' => 1.02, 'zoom_end' => 1.16, 'shake' => 0.0, 'motion_blur' => true, 'speed_ramp' => 'slow_to_fast'],
            'cinematic_zoom' => ['engine' => 'ken_burns', 'zoom_start' => 1.0, 'zoom_end' => 1.14, 'shake' => 0.0, 'motion_blur' => true, 'speed_ramp' => 'smooth'],
            default => ['engine' => 'ken_burns', 'zoom_start' => 1.0, 'zoom_end' => $type === 'cta' ? 1.08 : 1.12, 'shake' => 0.0, 'motion_blur' => false, 'speed_ramp' => 'smooth'],
        };
    }

    private function visualForBeat(string $type, string $prompt, string $language): string
    {
        $subject = $this->subject($prompt);

        return match ($type) {
            'hook' => $language === 'en' ? "attention-grabbing close-up of {$subject}" : "canh can gay chu y ve {$subject}",
            'problem' => $language === 'en' ? "problem moment before transformation" : "khoanh khac van de truoc khi thay doi",
            'emotion' => $language === 'en' ? "human reaction and emotional proof" : "phan ung con nguoi va cam xuc tin tuong",
            'solution' => $language === 'en' ? "product benefit reveal with premium light" : "loi ich san pham duoc tiet lo voi anh sang cao cap",
            'cta' => $language === 'en' ? "clean final product hero frame" : "khung ket san pham ro rang va dep",
            default => $subject,
        };
    }

    private function brollForBeat(string $type, string $prompt): string
    {
        $subject = $this->subject($prompt);

        return match ($type) {
            'hook' => "macro detail, quick push-in, bold foreground movement for {$subject}",
            'problem' => "before-state b-roll, close texture, natural human gesture",
            'emotion' => "reaction shot, soft light, visible transformation cue",
            'solution' => "hero b-roll, product detail, clean proof shot",
            'cta' => "end-card composition, readable space for CTA, product centered",
            default => "cinematic b-roll for {$subject}",
        };
    }

    private function assetSearchQuery(array $scene, string $prompt): string
    {
        return trim($this->subject($prompt) . ' ' . $scene['type'] . ' ' . $scene['visual'] . ' ' . $scene['shot_type']);
    }

    private function applySceneOverrides(array $scenes, array $overrides): array
    {
        if ($overrides === []) {
            return $scenes;
        }

        return collect($scenes)->map(function (array $scene, int $index) use ($overrides): array {
            $override = $overrides[$index] ?? null;
            if (!is_array($override)) {
                return $scene;
            }

            foreach ([
                'title' => 'title',
                'subtitle' => 'subtitle',
                'voice_over' => 'voice_over',
                'duration' => 'duration_seconds',
                'transition' => 'transition',
                'camera' => 'camera',
            ] as $from => $to) {
                $value = data_get($override, $from);
                if ($to === 'camera' && $value === 'auto') {
                    continue;
                }

                if ($value !== null && $value !== '') {
                    $scene[$to] = $to === 'duration_seconds'
                        ? round(max(1.2, min(30, (float) $value)), 3)
                        : Str::limit((string) $value, 600);
                }
            }

            $scene['camera_movement'] = $scene['camera'];

            return $scene;
        })->all();
    }

    private function subtitle(string $copy, string $language, string $type): string
    {
        $limit = $type === 'hook' ? 70 : 96;
        $subtitle = Str::limit($copy, $limit, '');

        return $language === 'en' ? $subtitle : Str::ascii($subtitle);
    }

    private function subject(string $prompt): string
    {
        return Str::limit(Str::headline($prompt), 88);
    }
}
