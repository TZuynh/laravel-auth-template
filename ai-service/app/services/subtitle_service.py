import shutil
import subprocess
from pathlib import Path
from uuid import uuid4

from app.core.settings import settings
from app.schemas.video import SubtitleRequest, SubtitleResponse


class SubtitleService:
    def generate(self, request: SubtitleRequest) -> SubtitleResponse:
        if request.use_whisper and request.audio_path:
            whisper = self._generate_with_whisper(request)
            if whisper is not None:
                return whisper

        extension = "vtt" if request.style == "vtt" else "srt"
        path = Path(settings().output_dir) / f"subtitle-{uuid4()}.{extension}"
        chunks = self._chunks(request.text)

        if extension == "vtt":
            content = "WEBVTT\n\n" + "\n\n".join(
                f"{self._ts(i * 2, True)} --> {self._ts((i + 1) * 2, True)}\n{chunk}"
                for i, chunk in enumerate(chunks)
            )
        else:
            content = "\n\n".join(
                f"{i + 1}\n{self._ts(i * 2)} --> {self._ts((i + 1) * 2)}\n{chunk}"
                for i, chunk in enumerate(chunks)
            )

        path.write_text(content + "\n", encoding="utf-8")
        return SubtitleResponse(status="ready", subtitle_path=str(path), format=extension)

    def _generate_with_whisper(self, request: SubtitleRequest) -> SubtitleResponse | None:
        whisper_binary = shutil.which(settings().whisper_binary)
        audio_path = Path(request.audio_path or "")

        if whisper_binary is None or not audio_path.exists():
            return None

        output_dir = Path(settings().output_dir) / f"whisper-{uuid4()}"
        output_dir.mkdir(parents=True, exist_ok=True)
        language = "Vietnamese" if request.language == "vi" else "English"
        command = [
            whisper_binary,
            str(audio_path),
            "--model",
            settings().whisper_model,
            "--language",
            language,
            "--output_format",
            "vtt" if request.style == "vtt" else "srt",
            "--output_dir",
            str(output_dir),
            "--fp16",
            "False",
        ]

        try:
            subprocess.run(command, capture_output=True, text=True, timeout=900, check=True)
        except (subprocess.SubprocessError, OSError):
            return None

        extension = "vtt" if request.style == "vtt" else "srt"
        subtitle = next(output_dir.glob(f"*.{extension}"), None)
        if subtitle is None:
            return None

        return SubtitleResponse(status="ready", subtitle_path=str(subtitle), format=extension)

    def _chunks(self, text: str) -> list[str]:
        words = text.split()
        if not words:
            return [text]

        return [" ".join(words[index:index + 9]) for index in range(0, len(words), 9)]

    def _ts(self, seconds: int, vtt: bool = False) -> str:
        hours = seconds // 3600
        minutes = (seconds % 3600) // 60
        secs = seconds % 60
        sep = "." if vtt else ","
        return f"{hours:02d}:{minutes:02d}:{secs:02d}{sep}000"
