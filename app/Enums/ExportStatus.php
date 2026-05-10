<?php

namespace App\Enums;

enum ExportStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Queued => 'Queued',
            self::Processing => 'Processing',
            self::Ready => 'Ready',
            self::Failed => 'Failed',
            self::Expired => 'Expired',
        };
    }

    public function isDownloadable(): bool
    {
        return $this === self::Ready;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Ready, self::Failed, self::Expired], true);
    }
}
