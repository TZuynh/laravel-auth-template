<?php

namespace App\Enums;

enum AspectRatio: string
{
    case Vertical = '9:16';
    case Landscape = '16:9';
    case Square = '1:1';

    public function dimensions(): array
    {
        return match ($this) {
            self::Vertical => ['width' => 1080, 'height' => 1920],
            self::Landscape => ['width' => 1920, 'height' => 1080],
            self::Square => ['width' => 1080, 'height' => 1080],
        };
    }
}
