<?php

namespace App\Models;

use App\Enums\AspectRatio;
use App\Enums\VideoProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoProject extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'product_id',
        'ai_template_id',
        'voice_profile_id',
        'music_track_id',
        'title',
        'language',
        'tone',
        'style',
        'aspect_ratio',
        'duration_seconds',
        'ai_model',
        'prompt',
        'optimized_prompt',
        'settings',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'aspect_ratio' => AspectRatio::class,
            'duration_seconds' => 'decimal:3',
            'settings' => 'array',
            'status' => VideoProjectStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function aiTemplate(): BelongsTo
    {
        return $this->belongsTo(AiTemplate::class);
    }

    public function voiceProfile(): BelongsTo
    {
        return $this->belongsTo(VoiceProfile::class);
    }

    public function musicTrack(): BelongsTo
    {
        return $this->belongsTo(MusicTrack::class);
    }

    public function scenes(): HasMany
    {
        return $this->hasMany(VideoScene::class)->orderBy('sort_order');
    }

    public function aiPrompts(): HasMany
    {
        return $this->hasMany(AiPrompt::class);
    }

    public function renderJobs(): HasMany
    {
        return $this->hasMany(RenderJob::class);
    }

    public function exports(): HasMany
    {
        return $this->hasMany(Export::class);
    }

    public function subtitles(): HasMany
    {
        return $this->hasMany(Subtitle::class);
    }
}
