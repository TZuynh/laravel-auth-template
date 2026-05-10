import math
import wave
from pathlib import Path
from uuid import uuid4

from app.core.settings import settings
from app.schemas.video import VoiceRequest, VoiceResponse


class VoiceService:
    def generate(self, request: VoiceRequest) -> VoiceResponse:
        words = max(1, len(request.text.split()))
        duration = max(1.2, min(90.0, words / 2.6))
        path = Path(settings().output_dir) / f"voice-{uuid4()}.wav"
        self._write_tone(path, duration, request.voice)

        return VoiceResponse(status="ready", audio_path=str(path), duration_seconds=duration)

    def _write_tone(self, path: Path, duration: float, voice: str) -> None:
        sample_rate = 48000
        frequency = 170 if voice.startswith("male") else 220
        amplitude = 8000
        frames = int(sample_rate * duration)

        with wave.open(str(path), "w") as wav:
            wav.setnchannels(1)
            wav.setsampwidth(2)
            wav.setframerate(sample_rate)

            for i in range(frames):
                envelope = min(1.0, i / (sample_rate * 0.08), (frames - i) / (sample_rate * 0.08))
                value = int(amplitude * envelope * math.sin(2 * math.pi * frequency * i / sample_rate))
                wav.writeframesraw(value.to_bytes(2, byteorder="little", signed=True))

