# Production AI Video Generation SaaS Architecture

This document defines the production architecture for a Runway/Pika/CapCut-style AI video platform. The current repository is Laravel 11, but the structure is Laravel 12-ready: modular services, repositories, DTOs, queue jobs, policies, form requests, and external AI workers remain compatible with either version.

## 1. System Boundary

Laravel owns SaaS product concerns:

- authentication, authorization, policies, permissions
- user dashboard and admin dashboard
- project, template, timeline, asset, render, billing, usage, and export records
- REST API and webhook endpoints
- queue orchestration, progress tracking, retries, and audit logs

Python FastAPI owns heavy AI/media work:

- ComfyUI workflow execution
- OpenAI/Fal.ai/Replicate provider calls
- XTTS/Coqui/ElevenLabs voice generation
- Whisper subtitle generation
- FFmpeg preprocessing and local render helpers
- GPU worker isolation

Shotstack is the fallback cloud renderer when local FFmpeg/GPU render cannot complete or when a tenant chooses cloud rendering.

## 2. Folder Structure

```text
app/
  DTOs/AiVideo/
  Http/
    Controllers/Api/
    Requests/AiVideo/
  Jobs/AiVideo/
  Models/
  Policies/
  Repositories/
    Contracts/
    Eloquent/
  Services/
    AiVideo/
    AI/
    Rendering/

routes/
  api.php
  web.php

ai-service/
  app/
    api/
    core/
    schemas/
    services/
  storage/
    inputs/
    outputs/
    tmp/
  Dockerfile
  requirements.txt

docker/
  nginx/
  supervisor/

docs/
  production-ai-video-saas.md
```

## 3. Database Design

Core tables:

- `users`: SaaS identity and ownership.
- `projects`: top-level AI video projects.
- `ai_templates`: reusable formats like TikTok AI, faceless, Reddit story, product showcase, anime edit.
- `video_scenes`: ordered scene plan generated from script.
- `scene_assets`: generated image/video/audio/subtitle assets per scene.
- `ai_prompts`: all prompt inputs, optimized prompts, provider payloads, responses, costs, and failures.
- `render_jobs`: queue-visible render execution state.
- `video_renders`: final render attempts and outputs.
- `media_files`: normalized storage layer for uploaded and generated files.
- `voice_generations`: TTS/voice clone records.
- `subtitle_generations`: Whisper/SRT/VTT/karaoke records.
- `subscriptions`: plan and billing state.
- `usage_logs`: provider, GPU, render minute, token, and storage usage.

Existing app tables such as `video_projects`, `exports`, `subtitles`, and `render_jobs` can be migrated into this naming model later. For now the new services are written to integrate with the current `VideoProject` and `RenderJob` models.

## 4. Rendering Pipeline

```text
Prompt
  -> OpenAI script generation
  -> scene splitting
  -> ComfyUI/Fal/Replicate image or video generation
  -> XTTS/Coqui/ElevenLabs voice generation
  -> Whisper subtitle alignment
  -> timeline manifest build
  -> FFmpeg render
  -> Shotstack fallback render
  -> MP4 export and usage logging
```

Professional render rules:

- never render final typography inside AI video generations
- AI video/image providers create motion plates and visual assets
- subtitles, CTA, logo, price, progress, and brand text are deterministic layers
- every external task ID is stored before polling
- output URLs are downloaded into owned storage
- every clip is validated with FFprobe before final composition
- every render step is idempotent and retry-safe

## 5. Queue Architecture

```text
default
  short Laravel tasks

ai-text
  OpenAI script, hook, CTA, prompt optimization

ai-gpu
  Python FastAPI jobs that call ComfyUI, Fal.ai, Replicate, XTTS, Whisper

render
  FFmpeg local render and Shotstack fallback dispatch

webhooks
  Shotstack, Replicate, Fal.ai provider callbacks

maintenance
  cleanup, usage aggregation, expired file deletion
```

All heavy operations are queued. HTTP requests only validate input, create records, dispatch jobs, and return current state.

## 6. Provider Architecture

Laravel provider clients:

- `PythonAiWorkerClient`: internal HTTP client to FastAPI.
- `ShotstackClient`: cloud rendering fallback.
- `OpenAiScriptService`: structured scripts and scene plans.
- `ProviderRouter`: chooses ComfyUI, Fal.ai, Replicate, or Shotstack based on tenant plan, provider health, and asset type.

Python provider services:

- `ComfyUIClient`: queues API-format workflows through `/prompt`, checks `/queue` and `/history`.
- `VoiceService`: XTTS/Coqui placeholder adapter with deterministic fallback audio.
- `SubtitleService`: Whisper adapter with deterministic SRT fallback.
- `FFmpegService`: validates binaries and renders simple timeline manifests.

## 7. Timeline Manifest

Laravel builds a provider-neutral timeline:

```json
{
  "project_id": 1,
  "aspect_ratio": "9:16",
  "fps": 30,
  "width": 1080,
  "height": 1920,
  "scenes": [
    {
      "id": 1,
      "start": 0,
      "duration": 3.0,
      "visual": "https://cdn.example.com/scene-1.mp4",
      "voice": "https://cdn.example.com/scene-1.wav",
      "subtitle": "Hook text",
      "transition": "bloom_cut"
    }
  ],
  "music": "https://cdn.example.com/music.mp3"
}
```

The same manifest can render through FFmpeg locally or Shotstack cloud.

## 8. Deployment

Production services:

- `app`: Laravel HTTP
- `queue`: Laravel Redis workers
- `scheduler`: Laravel scheduler
- `ai-worker`: FastAPI CPU/GPU worker
- `redis`: queue/cache
- `mysql`: database
- `nginx`: edge proxy
- `object-storage`: S3/R2 external service

GPU scale-out:

- route GPU jobs to `ai-gpu`
- run multiple FastAPI worker replicas near ComfyUI/XTTS models
- store only task metadata in DB, large assets in S3/R2
- use signed URLs for downloads and provider handoff

## 9. Production Hardening

- API keys only from environment or secret manager
- policies for every project/media/render access
- per-user and per-plan rate limits
- usage logs for every provider call
- failed job retry with exponential backoff
- webhook signature validation
- provider health checks and fallback routing
- cleanup of temporary render workspaces
- observability for queue latency, GPU saturation, provider error rate, render duration, and cost

