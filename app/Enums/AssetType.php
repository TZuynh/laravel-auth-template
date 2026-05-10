<?php

namespace App\Enums;

enum AssetType: string
{
    case ProductImage = 'product_image';
    case ProductVideo = 'product_video';
    case GeneratedImage = 'generated_image';
    case GeneratedVideo = 'generated_video';
    case VoiceOver = 'voice_over';
    case Music = 'music';
    case Subtitle = 'subtitle';
    case Overlay = 'overlay';
}
