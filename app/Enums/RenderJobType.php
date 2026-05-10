<?php

namespace App\Enums;

enum RenderJobType: string
{
    case GenerateScript = 'generate_script';
    case GenerateScenes = 'generate_scenes';
    case GenerateImage = 'generate_image';
    case GenerateVideo = 'generate_video';
    case GenerateVoice = 'generate_voice';
    case GenerateSubtitle = 'generate_subtitle';
    case RenderScene = 'render_scene';
    case RenderFinalVideo = 'render_final_video';
    case ExportVideo = 'export_video';
}
