<?php

namespace App\Enums;

enum VideoSceneStatus: string
{
    case Draft = 'draft';
    case Prompted = 'prompted';
    case Generating = 'generating';
    case Ready = 'ready';
    case Rendering = 'rendering';
    case Completed = 'completed';
    case Failed = 'failed';
}
