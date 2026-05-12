from fastapi import APIRouter, Depends, Header, HTTPException, status

from app.core.settings import settings
from app.schemas.video import (
    AssetRequest,
    AssetResponse,
    BulkGenerationRequest,
    BulkGenerationResponse,
    SceneSplitRequest,
    SceneSplitResponse,
    ScriptRequest,
    ScriptResponse,
    SubtitleRequest,
    SubtitleResponse,
    TimelineRenderRequest,
    TimelineRenderResponse,
    VoiceRequest,
    VoiceResponse,
)
from app.services.asset_service import AssetService
from app.services.bulk_service import BulkVideoService
from app.services.ffmpeg_service import FFmpegService
from app.services.script_service import ScriptService
from app.services.subtitle_service import SubtitleService
from app.services.voice_service import VoiceService

router = APIRouter(prefix="/api/v1")


def require_worker_key(x_ai_worker_key: str | None = Header(default=None)) -> None:
    expected = settings().api_key
    if expected and x_ai_worker_key != expected:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid AI worker API key.")


@router.get("/health")
def health() -> dict:
    return {"status": "ok", "service": settings().app_name}


@router.post("/script/generate", response_model=ScriptResponse, dependencies=[Depends(require_worker_key)])
async def generate_script(payload: ScriptRequest) -> ScriptResponse:
    return await ScriptService().generate(payload)


@router.post("/bulk/generate", response_model=BulkGenerationResponse, dependencies=[Depends(require_worker_key)])
def generate_bulk_videos(payload: BulkGenerationRequest) -> BulkGenerationResponse:
    return BulkVideoService().generate(payload)


@router.post("/scenes/split", response_model=SceneSplitResponse, dependencies=[Depends(require_worker_key)])
def split_scenes(payload: SceneSplitRequest) -> SceneSplitResponse:
    return ScriptService().split(payload)


@router.post("/assets/image", response_model=AssetResponse, dependencies=[Depends(require_worker_key)])
async def generate_image(payload: AssetRequest) -> AssetResponse:
    return await AssetService().generate_image(payload)


@router.post("/assets/video", response_model=AssetResponse, dependencies=[Depends(require_worker_key)])
async def generate_video(payload: AssetRequest) -> AssetResponse:
    return await AssetService().generate_video(payload)


@router.post("/voice/generate", response_model=VoiceResponse, dependencies=[Depends(require_worker_key)])
def generate_voice(payload: VoiceRequest) -> VoiceResponse:
    return VoiceService().generate(payload)


@router.post("/subtitles/generate", response_model=SubtitleResponse, dependencies=[Depends(require_worker_key)])
def generate_subtitles(payload: SubtitleRequest) -> SubtitleResponse:
    return SubtitleService().generate(payload)


@router.post("/render/timeline", response_model=TimelineRenderResponse, dependencies=[Depends(require_worker_key)])
def render_timeline(payload: TimelineRenderRequest) -> TimelineRenderResponse:
    return FFmpegService().render_timeline(payload)
