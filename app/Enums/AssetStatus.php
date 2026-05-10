<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';

    public function isReady(): bool
    {
        return $this === self::Ready;
    }
}
