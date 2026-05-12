from typing import Literal

from pydantic import BaseModel, Field, HttpUrl


AspectRatio = Literal["9:16", "16:9", "1:1"]


class ScriptRequest(BaseModel):
    prompt: str = Field(min_length=1, max_length=5000)
    language: Literal["vi", "en"] = "vi"
    video_type: str = "product_showcase"
    duration_seconds: int = Field(default=15, ge=5, le=90)


class ScriptResponse(BaseModel):
    script: str
    hooks: list[str]
    cta: str


class SceneSplitRequest(BaseModel):
    script: str = Field(min_length=1)
    language: Literal["vi", "en"] = "vi"
    max_scenes: int = Field(default=4, ge=1, le=12)


class Scene(BaseModel):
    index: int
    type: str = "scene"
    title: str
    prompt: str
    shot_type: str = "medium shot"
    camera: str = "cinematic_zoom"
    visual: str = ""
    b_roll_direction: str = ""
    voice_over: str
    subtitle: str
    duration: float
    transition: str = "fade"
    sound_effect: str = "soft_whoosh"
    pacing: str = "medium"
    emotional_tone: str = "confident"
    subtitle_cues: list[dict] = []
    asset_plan: dict = {}
    motion: dict = {}


class SceneSplitResponse(BaseModel):
    scenes: list[Scene]


class AssetRequest(BaseModel):
    prompt: str = Field(min_length=1)
    workflow: dict | None = None
    provider: Literal["comfyui", "fal", "replicate", "kling", "wan", "ltx", "minimax", "veo", "local"] = "local"
    aspect_ratio: AspectRatio = "9:16"
    source_image_url: HttpUrl | None = None


class AssetResponse(BaseModel):
    provider: str
    status: str
    asset_url: str | None = None
    task_id: str | None = None
    metadata: dict = {}


class VoiceRequest(BaseModel):
    text: str = Field(min_length=1)
    language: Literal["vi", "en"] = "vi"
    voice: str = "female_south"


class VoiceResponse(BaseModel):
    status: str
    audio_path: str
    duration_seconds: float


class SubtitleRequest(BaseModel):
    text: str = Field(min_length=1)
    language: Literal["vi", "en"] = "vi"
    style: Literal["srt", "vtt", "karaoke"] = "srt"
    audio_path: str | None = None
    use_whisper: bool = True


class SubtitleResponse(BaseModel):
    status: str
    subtitle_path: str
    format: str


class TimelineScene(BaseModel):
    id: int | None = None
    title: str
    start: float = Field(ge=0)
    duration: float = Field(gt=0)
    subtitle: str | None = None
    visual_url: str | None = None
    transition: str = "bloom_cut"


class TimelineRenderRequest(BaseModel):
    project_id: int
    aspect_ratio: AspectRatio = "9:16"
    width: int = Field(default=1080, ge=256, le=3840)
    height: int = Field(default=1920, ge=256, le=3840)
    fps: int = Field(default=30, ge=12, le=60)
    scenes: list[TimelineScene] = Field(min_length=1)
    music_url: str | None = None


class TimelineRenderResponse(BaseModel):
    status: str
    output_path: str
    duration_seconds: float
    metadata: dict = {}


class BulkGenerationRequest(BaseModel):
    prompt: str = Field(min_length=1, max_length=5000)
    style_slug: Literal["ai_studio", "tiktok_viral", "cinematic", "anime", "motivation", "modern_minimal"] = "ai_studio"
    language: Literal["vi", "en"] = "en"
    duration_seconds: int = Field(default=30, ge=10, le=90)
    aspect_ratio: AspectRatio = "9:16"


class BulkVideoOutput(BaseModel):
    title: str
    style: str
    duration: str
    scenes: list[Scene]
    voice: str
    music: str
    subtitle_style: str
    timeline_json: dict


class BulkGenerationResponse(BaseModel):
    videos: list[BulkVideoOutput]
