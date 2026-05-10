<?php

namespace App\Enums;

enum VideoProjectStatus: string
{
    case Draft = 'draft';
    case Generating = 'generating';
    case Ready = 'ready';
    case Rendering = 'rendering';
    case Completed = 'completed';
    case Failed = 'failed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Generating => 'Generating',
            self::Ready => 'Ready',
            self::Rendering => 'Rendering',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Archived => 'Archived',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Archived], true);
    }
}
