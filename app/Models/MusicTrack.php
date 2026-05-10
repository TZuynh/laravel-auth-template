<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MusicTrack extends Model
{
    protected $fillable = [
        'title',
        'mood',
        'bpm',
        'duration_seconds',
        'file_path',
        'license_type',
        'default_volume',
        'metadata',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bpm' => 'integer',
            'duration_seconds' => 'decimal:3',
            'default_volume' => 'decimal:2',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function videoProjects(): HasMany
    {
        return $this->hasMany(VideoProject::class);
    }
}
