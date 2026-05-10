import httpx

from app.core.settings import settings
from app.schemas.video import Scene, ScriptRequest, ScriptResponse, SceneSplitRequest, SceneSplitResponse


class ScriptService:
    async def generate(self, request: ScriptRequest) -> ScriptResponse:
        if settings().openai_api_key:
            script = await self._generate_with_openai(request)
        else:
            script = self._fallback_script(request)

        hooks = self._hooks(request)
        cta = "Mua ngay hôm nay." if request.language == "vi" else "Create your video today."

        return ScriptResponse(script=script, hooks=hooks, cta=cta)

    def split(self, request: SceneSplitRequest) -> SceneSplitResponse:
        chunks = [part.strip() for part in request.script.replace("?", ".").replace("!", ".").split(".") if part.strip()]
        defaults = self._default_scenes(request.language)
        scenes: list[Scene] = []

        for index in range(min(request.max_scenes, 4)):
            voice = chunks[index] if index < len(chunks) else defaults[index]["voice_over"]
            scenes.append(
                Scene(
                    index=index + 1,
                    title=defaults[index]["title"],
                    prompt=f"{defaults[index]['prompt']}. Voice-over: {voice}",
                    voice_over=voice,
                    subtitle=voice[:120],
                    duration=defaults[index]["duration"],
                    transition=defaults[index]["transition"],
                )
            )

        return SceneSplitResponse(scenes=scenes)

    async def _generate_with_openai(self, request: ScriptRequest) -> str:
        prompt = (
            "Write a short-form AI video ad script with a strong hook, emotional middle, "
            "scene-ready sentences, and a clear CTA. "
            f"Language={request.language}. Video type={request.video_type}. "
            f"Duration={request.duration_seconds}s. User prompt: {request.prompt}"
        )
        async with httpx.AsyncClient(base_url=settings().openai_base_url, timeout=60) as client:
            response = await client.post(
                "/responses",
                headers={"Authorization": f"Bearer {settings().openai_api_key}"},
                json={
                    "model": settings().openai_model,
                    "input": prompt,
                },
            )
            response.raise_for_status()
            data = response.json()

        text = data.get("output_text")
        if isinstance(text, str) and text.strip():
            return text.strip()

        output = data.get("output", [])
        if output and isinstance(output, list):
            content = output[0].get("content", [])
            if content and isinstance(content, list):
                maybe_text = content[0].get("text")
                if isinstance(maybe_text, str):
                    return maybe_text.strip()

        return self._fallback_script(request)

    def _fallback_script(self, request: ScriptRequest) -> str:
        if request.language == "vi":
            return (
                f"Dừng lại một giây, {request.prompt} đang được kể như một thước phim quảng cáo cao cấp. "
                "Ánh sáng mở ra, sản phẩm bước vào trung tâm khung hình với chuyển động điện ảnh. "
                "Từng chi tiết trở thành một lý do để tin tưởng và muốn sở hữu. "
                "Hãy biến ý tưởng này thành video sẵn sàng cho TikTok hôm nay."
            )

        return (
            f"Pause for one second, {request.prompt} is about to become a cinematic ad. "
            "The light opens and the product steps into the center of the frame. "
            "Every detail becomes a reason to trust it and remember it. "
            "Turn this idea into a social-ready video today."
        )

    def _hooks(self, request: ScriptRequest) -> list[str]:
        if request.language == "vi":
            return ["Dừng cuộn trong 3 giây.", "Một khung hình khiến khách muốn mua.", "Biến sản phẩm thành câu chuyện."]

        return ["Stop the scroll in three seconds.", "One frame that makes people want it.", "Turn a product into a story."]

    def _default_scenes(self, language: str) -> list[dict]:
        if language == "vi":
            return [
                {"title": "Hook mở đầu", "prompt": "cinematic scroll-stopping opener", "voice_over": "Dừng lại một giây.", "duration": 2.5, "transition": "bloom_cut"},
                {"title": "Reveal sản phẩm", "prompt": "premium product reveal with dolly movement", "voice_over": "Sản phẩm xuất hiện như nhân vật chính.", "duration": 3.0, "transition": "parallax_push"},
                {"title": "Chuyển đổi lợi ích", "prompt": "macro detail transformation and emotional benefit", "voice_over": "Từng chi tiết tạo cảm giác tin tưởng.", "duration": 3.0, "transition": "whip_blur"},
                {"title": "CTA kết thúc", "prompt": "brand lockup and final call to action", "voice_over": "Bắt đầu chiến dịch hôm nay.", "duration": 2.5, "transition": "fade"},
            ]

        return [
            {"title": "Hook opening", "prompt": "cinematic scroll-stopping opener", "voice_over": "Pause for one second.", "duration": 2.5, "transition": "bloom_cut"},
            {"title": "Product reveal", "prompt": "premium product reveal with dolly movement", "voice_over": "The product enters like the hero.", "duration": 3.0, "transition": "parallax_push"},
            {"title": "Benefit transformation", "prompt": "macro detail transformation and emotional benefit", "voice_over": "Every detail builds trust.", "duration": 3.0, "transition": "whip_blur"},
            {"title": "CTA ending", "prompt": "brand lockup and final call to action", "voice_over": "Launch the campaign today.", "duration": 2.5, "transition": "fade"},
        ]

