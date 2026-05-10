<?php

namespace App\Enums;

enum AiPromptStatus: string
{
    case Draft = 'draft';
    case Queued = 'queued';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
