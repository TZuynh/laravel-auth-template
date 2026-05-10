<?php

namespace App\Enums;

enum AiPromptType: string
{
    case Script = 'script';
    case Scene = 'scene';
    case Image = 'image';
    case Video = 'video';
    case VoiceOver = 'voice_over';
    case Subtitle = 'subtitle';
    case Optimization = 'optimization';
}
