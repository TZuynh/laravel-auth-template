from pathlib import Path
from uuid import uuid4

from app.core.settings import settings
from app.schemas.video import SubtitleRequest, SubtitleResponse


class SubtitleService:
    def generate(self, request: SubtitleRequest) -> SubtitleResponse:
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

