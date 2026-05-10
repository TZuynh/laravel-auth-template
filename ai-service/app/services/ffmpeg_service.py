import shlex
import subprocess
from pathlib import Path
from uuid import uuid4

from app.core.settings import settings
from app.schemas.video import TimelineRenderRequest, TimelineRenderResponse


class FFmpegService:
    def render_timeline(self, request: TimelineRenderRequest) -> TimelineRenderResponse:
        output_path = Path(settings().output_dir) / f"render-{request.project_id}-{uuid4()}.mp4"
        duration = sum(scene.duration for scene in request.scenes)
        filters = self._drawtext_filters(request)
        command = [
            settings().ffmpeg_binary,
            "-y",
            "-f",
            "lavfi",
            "-i",
            f"color=c=0x080b18:s={request.width}x{request.height}:d={max(duration, 1)}:r={request.fps}",
            "-vf",
            filters,
            "-an",
            "-c:v",
            "libx264",
            "-pix_fmt",
            "yuv420p",
            "-preset",
            "veryfast",
            "-crf",
            "20",
            str(output_path),
        ]
        self._run(command)

        return TimelineRenderResponse(
            status="ready",
            output_path=str(output_path),
            duration_seconds=duration,
            metadata={"renderer": "ffmpeg", "command": " ".join(shlex.quote(part) for part in command)},
        )

    def _drawtext_filters(self, request: TimelineRenderRequest) -> str:
        filters = [
            "format=yuv420p",
            "eq=contrast=1.08:saturation=1.12:brightness=-0.015",
        ]
        for scene in request.scenes:
            text = self._escape(scene.subtitle or scene.title)
            start = scene.start
            end = scene.start + scene.duration
            font_size = max(28, int(request.height * 0.036))
            y = int(request.height * 0.76)
            filters.append(
                "drawtext="
                f"text='{text}':fontcolor=white:fontsize={font_size}:"
                f"x=(w-text_w)/2:y={y}:box=1:boxcolor=black@0.45:boxborderw=24:"
                f"enable='between(t,{start:.3f},{end:.3f})'"
            )

        return ",".join(filters)

    def _escape(self, text: str) -> str:
        return (
            text.replace("\\", "\\\\")
            .replace("'", "\\'")
            .replace(":", "\\:")
            .replace(",", "\\,")
            .replace("%", "\\%")
            .replace("\n", " ")
        )

    def _run(self, command: list[str]) -> None:
        process = subprocess.run(command, capture_output=True, text=True, timeout=3600, check=False)
        if process.returncode != 0:
            error = process.stderr.strip() or process.stdout.strip() or "FFmpeg command failed"
            raise RuntimeError(error)

