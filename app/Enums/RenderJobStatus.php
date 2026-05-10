<?php

namespace App\Enums;

enum RenderJobStatus: string
{
    case Queued = 'queued';
    case Preparing = 'preparing';
    case GeneratingAssets = 'generating_assets';
    case Rendering = 'rendering';
    case MixingAudio = 'mixing_audio';
    case Exporting = 'exporting';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Queued',
            self::Preparing => 'Preparing',
            self::GeneratingAssets => 'Generating assets',
            self::Rendering => 'Rendering',
            self::MixingAudio => 'Mixing audio',
            self::Exporting => 'Exporting',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isRunning(): bool
    {
        return in_array($this, [
            self::Preparing,
            self::GeneratingAssets,
            self::Rendering,
            self::MixingAudio,
            self::Exporting,
        ], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Cancelled], true);
    }
}
