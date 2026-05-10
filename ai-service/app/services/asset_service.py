import subprocess
from pathlib import Path
from uuid import uuid4

from app.core.settings import settings
from app.schemas.video import AssetRequest, AssetResponse
from app.services.comfyui_service import ComfyUIService


class AssetService:
    def __init__(self) -> None:
        self.comfyui = ComfyUIService()

    async def generate_image(self, request: AssetRequest) -> AssetResponse:
        if request.provider == "comfyui" and request.workflow:
            queued = await self.comfyui.queue_workflow(request.workflow)
            return AssetResponse(provider="comfyui", status="queued", task_id=queued.get("prompt_id"), metadata=queued)

        return self._write_placeholder_image(request.prompt, request.aspect_ratio)

    async def generate_video(self, request: AssetRequest) -> AssetResponse:
        if request.provider == "comfyui" and request.workflow:
            queued = await self.comfyui.queue_workflow(request.workflow)
            return AssetResponse(provider="comfyui", status="queued", task_id=queued.get("prompt_id"), metadata=queued)

        return self._write_placeholder_video(request.prompt, request.aspect_ratio)

    def _write_placeholder_image(self, prompt: str, aspect_ratio: str) -> AssetResponse:
        width, height = self._dimensions(aspect_ratio)
        path = Path(settings().output_dir) / f"image-{uuid4()}.svg"
        safe_prompt = prompt.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")[:220]
        path.write_text(
            f"""<svg xmlns="http://www.w3.org/2000/svg" width="{width}" height="{height}" viewBox="0 0 {width} {height}">
<defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#080b18"/><stop offset="0.55" stop-color="#3b1b64"/><stop offset="1" stop-color="#0f172a"/></linearGradient></defs>
<rect width="100%" height="100%" fill="url(#g)"/>
<circle cx="{int(width * .72)}" cy="{int(height * .26)}" r="{int(min(width, height) * .22)}" fill="#8b5cf6" opacity=".28"/>
<text x="{int(width * .08)}" y="{int(height * .50)}" fill="#ffffff" font-family="Arial" font-size="{max(28, int(height * .04))}" font-weight="700">AI visual plate</text>
<text x="{int(width * .08)}" y="{int(height * .56)}" fill="#cbd5e1" font-family="Arial" font-size="{max(18, int(height * .022))}">{safe_prompt}</text>
</svg>""",
            encoding="utf-8",
        )

        return AssetResponse(
            provider="local",
            status="ready",
            asset_url=str(path),
            metadata={"asset_type": "image", "note": "Local SVG plate generated because no external provider was configured."},
        )

    def _write_placeholder_video(self, prompt: str, aspect_ratio: str) -> AssetResponse:
        width, height = self._dimensions(aspect_ratio)
        path = Path(settings().output_dir) / f"video-{uuid4()}.mp4"
        text = prompt.replace("\\", "\\\\").replace("'", "\\'").replace(":", "\\:").replace(",", "\\,")[:120]
        command = [
            settings().ffmpeg_binary,
            "-y",
            "-f",
            "lavfi",
            "-i",
            f"color=c=0x080b18:s={width}x{height}:d=5:r=30",
            "-vf",
            f"drawtext=text='{text}':fontcolor=white:fontsize={max(28, int(height * .036))}:x=(w-text_w)/2:y=(h-text_h)/2:box=1:boxcolor=black@0.35:boxborderw=24,format=yuv420p",
            "-an",
            "-c:v",
            "libx264",
            "-preset",
            "veryfast",
            "-crf",
            "22",
            str(path),
        ]
        subprocess.run(command, capture_output=True, text=True, timeout=120, check=True)

        return AssetResponse(
            provider="local",
            status="ready",
            asset_url=str(path),
            metadata={"asset_type": "video", "note": "Local MP4 motion plate generated because no external provider was configured."},
        )

    def _dimensions(self, aspect_ratio: str) -> tuple[int, int]:
        if aspect_ratio == "16:9":
            return 1280, 720
        if aspect_ratio == "1:1":
            return 960, 960
        return 720, 1280
