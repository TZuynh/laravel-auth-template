from functools import lru_cache
from pathlib import Path

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    app_name: str = "AI Video Worker"
    api_key: str | None = None
    storage_root: Path = Path("storage")
    ffmpeg_binary: str = "ffmpeg"
    ffprobe_binary: str = "ffprobe"
    whisper_binary: str = "whisper"
    whisper_model: str = "small"

    openai_api_key: str | None = None
    openai_base_url: str = "https://api.openai.com/v1"
    openai_model: str = "gpt-5.5"
    kling_api_key: str | None = None
    wan_api_key: str | None = None
    ltx_api_key: str | None = None

    comfyui_base_url: str = "http://127.0.0.1:8188"
    fal_api_key: str | None = None
    replicate_api_token: str | None = None

    model_config = SettingsConfigDict(env_file=".env", env_prefix="AI_WORKER_", extra="ignore")

    @property
    def output_dir(self) -> Path:
        path = self.storage_root / "outputs"
        path.mkdir(parents=True, exist_ok=True)
        return path

    @property
    def tmp_dir(self) -> Path:
        path = self.storage_root / "tmp"
        path.mkdir(parents=True, exist_ok=True)
        return path


@lru_cache
def settings() -> Settings:
    return Settings()
