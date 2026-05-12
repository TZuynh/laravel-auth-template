from app.schemas.video import BulkGenerationRequest, BulkGenerationResponse, BulkVideoOutput, Scene


class BulkVideoService:
    def generate(self, request: BulkGenerationRequest) -> BulkGenerationResponse:
        preset = self._preset(request.style_slug)
        scenes = self._scenes(request, preset)

        return BulkGenerationResponse(
            videos=[
                BulkVideoOutput(
                    title=f"{preset['name']} - {request.prompt[:54]}",
                    style=preset["name"],
                    duration=f"{request.duration_seconds}s",
                    scenes=scenes,
                    voice=preset["voice"],
                    music=preset["music"],
                    subtitle_style=preset["subtitle_style"],
                    timeline_json=self._timeline(request, preset, scenes),
                )
            ]
        )

    def _preset(self, slug: str) -> dict:
        return next(
            (preset for preset in self._presets() if preset["slug"] == slug),
            self._presets()[0],
        )

    def _scenes(self, request: BulkGenerationRequest, preset: dict) -> list[Scene]:
        beats = [
            {
                "type": "hook",
                "title": "Hook",
                "voice": f"Stop scrolling. {request.prompt[:96]}",
                "shot": "extreme close up",
                "camera": "snap_zoom",
                "transition": "impact_zoom",
                "sfx": "impact_hit",
                "tone": "urgent curiosity",
                "pacing": "fast",
            },
            {
                "type": "problem",
                "title": "Problem",
                "voice": "Show the before moment and make the pain point instantly visible.",
                "shot": "close up",
                "camera": "handheld_push",
                "transition": "whip_pan",
                "sfx": "soft_whoosh",
                "tone": "relatable tension",
                "pacing": "fast",
            },
            {
                "type": "emotion",
                "title": "Emotion",
                "voice": "Reveal the emotional shift and make the viewer feel the upgrade.",
                "shot": "medium shot",
                "camera": "parallax_push",
                "transition": "light_leak",
                "sfx": "rise",
                "tone": "emotional lift",
                "pacing": "medium",
            },
            {
                "type": "solution",
                "title": "Solution",
                "voice": f"Present the solution with {preset['visual_direction']}.",
                "shot": "hero product shot",
                "camera": "cinematic_zoom",
                "transition": "smooth_push",
                "sfx": "shine",
                "tone": "confidence",
                "pacing": "medium",
            },
            {
                "type": "cta",
                "title": "CTA",
                "voice": "End with a direct call to action.",
                "shot": "clean end card",
                "camera": "slow_dolly_in",
                "transition": "clean_fade",
                "sfx": "button_pop",
                "tone": "decisive",
                "pacing": "clean",
            },
        ]
        durations = self._durations(request.duration_seconds)

        return [
            Scene(
                index=index + 1,
                type=beat["type"],
                title=beat["title"],
                prompt=f"{beat['shot']} of {request.prompt}. {preset['visual_direction']}. Camera {beat['camera']}. No text baked into footage.",
                shot_type=beat["shot"],
                camera=beat["camera"],
                visual=f"{beat['type']} visual for {request.prompt}",
                b_roll_direction=f"{beat['shot']}, camera {beat['camera']}, premium social video b-roll",
                voice_over=beat["voice"],
                subtitle=beat["voice"][:120],
                duration=durations[index],
                transition=beat["transition"],
                sound_effect=beat["sfx"],
                pacing=beat["pacing"],
                emotional_tone=beat["tone"],
                subtitle_cues=self._subtitle_cues(beat["voice"], durations[index]),
                asset_plan={
                    "primary": "ai_video_or_stock_footage" if beat["type"] in ["hook", "emotion", "solution"] else "ai_image_with_motion",
                    "fallback": "ai_image_with_ken_burns",
                    "search_query": f"{request.prompt} {beat['type']} {beat['shot']}",
                    "overlay": "impact_flash" if beat["type"] == "hook" else "subtle_light_leak",
                },
                motion=self._motion(beat["camera"]),
            )
            for index, beat in enumerate(beats)
        ]

    def _timeline(self, request: BulkGenerationRequest, preset: dict, scenes: list[Scene]) -> dict:
        cursor = 0.0
        timeline_scenes = []
        for scene in scenes:
            timeline_scenes.append(
                {
                    "start": round(cursor, 3),
                    "duration": scene.duration,
                    "title": scene.title,
                    "type": scene.type,
                    "subtitle": scene.subtitle,
                    "transition": scene.transition,
                    "camera": scene.camera,
                    "motion": scene.motion,
                    "asset_plan": scene.asset_plan,
                    "sound_effect": scene.sound_effect,
                    "visual_style": preset["visual_direction"],
                }
            )
            cursor += scene.duration

        return {
            "aspect_ratio": preset.get("aspect_ratio", request.aspect_ratio),
            "fps": 30,
            "music": preset["music"],
            "subtitle_style": preset["subtitle_style"],
            "scenes": timeline_scenes,
        }

    def _presets(self) -> list[dict]:
        return [
            {
                "slug": "ai_studio",
                "name": "AI Studio",
                "aspect_ratio": "9:16",
                "voice": "female_south",
                "music": "Neutral Ambient Pulse",
                "subtitle_style": "clean bold captions",
                "pacing": "balanced 2-3 second cuts",
                "visual_direction": "clean premium visuals, smooth camera moves, product-first framing, natural cinematic light",
                "transitions": ["fade", "zoom", "wipeLeft", "slideUp"],
            },
            {
                "slug": "tiktok_viral",
                "name": "TikTok Viral",
                "aspect_ratio": "9:16",
                "voice": "female_south",
                "music": "Trending TikTok Pulse",
                "subtitle_style": "animated bold captions",
                "pacing": "fast cuts and zoom hits",
                "visual_direction": "high contrast social visuals, fast cuts, snap zooms, animated captions",
                "transitions": ["whipRight", "zoom", "wipeLeft", "slideUp"],
            },
            {
                "slug": "cinematic",
                "name": "Cinematic",
                "aspect_ratio": "16:9",
                "voice": "ai_en",
                "music": "Epic Cinematic Rise",
                "subtitle_style": "small lower-third cinema subtitles",
                "pacing": "slow emotional reveal",
                "visual_direction": "movie lighting, slow motion, dramatic transitions, volumetric atmosphere",
                "transitions": ["fade", "wipeLeft", "fade", "slideLeft"],
            },
            {
                "slug": "anime",
                "name": "Anime",
                "aspect_ratio": "9:16",
                "voice": "ai_en",
                "music": "Anime Energy Loop",
                "subtitle_style": "outlined anime captions with glow",
                "pacing": "dynamic motion and speed ramps",
                "visual_direction": "anime color grading, cel-shaded highlights, glow effects, impact frames",
                "transitions": ["wipeRight", "zoom", "wipeLeft", "fade"],
            },
            {
                "slug": "motivation",
                "name": "Motivational Shorts",
                "aspect_ratio": "9:16",
                "voice": "male_north",
                "music": "Epic Cinematic Rise",
                "subtitle_style": "large quote captions with emphasis words",
                "pacing": "emotional build with bold quote hits",
                "visual_direction": "cinematic discipline routine, sunrise contrast, epic typography",
                "transitions": ["fade", "slideUp", "wipeLeft", "fade"],
            },
            {
                "slug": "modern_minimal",
                "name": "Modern Minimal",
                "aspect_ratio": "1:1",
                "voice": "female_south",
                "music": "Lo-fi Chill Commerce",
                "subtitle_style": "clean typography with soft highlight bar",
                "pacing": "smooth measured pacing",
                "visual_direction": "clean typography, smooth transitions, soft colors, generous negative space",
                "transitions": ["fade", "slideLeft", "fade", "slideRight"],
            },
        ]

    def _durations(self, total: int) -> list[float]:
        weights = [0.12, 0.18, 0.24, 0.31, 0.15]
        durations = [max(1.6, round(total * weight, 3)) for weight in weights]
        durations[0] = min(3.0, durations[0])
        durations[3] = round(max(1.6, durations[3] + (total - sum(durations))), 3)
        return durations

    def _subtitle_cues(self, text: str, duration: float) -> list[dict]:
        words = [word for word in text.split() if word][:18]
        if not words:
            return []
        step = duration / len(words)
        return [
            {
                "word": word,
                "start": round(index * step, 3),
                "end": round((index + 1) * step, 3),
                "emphasis": len(word) >= 6,
            }
            for index, word in enumerate(words)
        ]

    def _motion(self, camera: str) -> dict:
        if camera == "snap_zoom":
            return {"engine": "ken_burns", "zoom_start": 1.0, "zoom_end": 1.28, "shake": 0.012, "motion_blur": True}
        if camera == "handheld_push":
            return {"engine": "ken_burns", "zoom_start": 1.03, "zoom_end": 1.18, "shake": 0.008, "motion_blur": True}
        if camera == "parallax_push":
            return {"engine": "parallax", "zoom_start": 1.02, "zoom_end": 1.16, "motion_blur": True}
        return {"engine": "ken_burns", "zoom_start": 1.0, "zoom_end": 1.12, "motion_blur": False}
