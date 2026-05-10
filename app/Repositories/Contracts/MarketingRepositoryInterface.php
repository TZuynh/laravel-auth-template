<?php

namespace App\Repositories\Contracts;

interface MarketingRepositoryInterface
{
    public function aiVideoStudioData(): array;

    public function directorDashboardData(): array;

    public function sceneEditorData(): array;

    public function aiImageStudioData(): array;

    public function renderHistoryData(): array;

    public function exportManagerData(): array;

    public function templateManagerData(): array;
}
