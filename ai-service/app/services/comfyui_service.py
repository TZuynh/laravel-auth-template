import uuid

import httpx

from app.core.settings import settings


class ComfyUIService:
    async def queue_workflow(self, workflow: dict, client_id: str | None = None) -> dict:
        client_id = client_id or str(uuid.uuid4())
        async with httpx.AsyncClient(base_url=settings().comfyui_base_url, timeout=120) as client:
            response = await client.post("/prompt", json={"prompt": workflow, "client_id": client_id})
            response.raise_for_status()
            return response.json()

    async def history(self, prompt_id: str) -> dict:
        async with httpx.AsyncClient(base_url=settings().comfyui_base_url, timeout=30) as client:
            response = await client.get(f"/history/{prompt_id}")
            response.raise_for_status()
            return response.json()

    async def queue(self) -> dict:
        async with httpx.AsyncClient(base_url=settings().comfyui_base_url, timeout=30) as client:
            response = await client.get("/queue")
            response.raise_for_status()
            return response.json()

