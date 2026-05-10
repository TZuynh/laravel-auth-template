# AI Video SaaS Setup

This repository includes a production-oriented AI video SaaS foundation:

- Laravel API routes under `/api/ai-video/*`
- Laravel DTO, Form Request, Service, Repository, and Queue Job scaffold
- Python FastAPI worker in `ai-service/`
- FFmpeg timeline render endpoint
- ComfyUI queue client scaffold
- OpenAI script generation support with deterministic fallback
- local voice/subtitle generation fallback
- Docker compose file for the AI worker and Redis

## Local AI Worker

```bash
docker compose -f docker-compose.ai-video.yml up --build
```

## Laravel Environment

Copy the values from `docs/ai-video-env.example` into your `.env` as needed.

```dotenv
AI_WORKER_BASE_URL=http://127.0.0.1:8088
AI_WORKER_API_KEY=local-worker-key
QUEUE_CONNECTION=redis
```

## Health Check

```bash
curl http://127.0.0.1:8088/api/v1/health
```

## API Flow

1. `POST /api/ai-video/projects`
2. `GET /api/ai-video/projects/{videoProject}`
3. `GET /api/ai-video/projects/{videoProject}/timeline`
4. Queue `RenderTimelineWithPythonWorkerJob` or use the existing FFmpeg render flow.

