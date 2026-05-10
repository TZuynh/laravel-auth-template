<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoiceProfile extends Model
{
    protected $fillable = [
        'provider',
        'provider_voice_id',
        'name',
        'gender',
        'language',
        'tone',
        'sample_path',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function videoProjects(): HasMany
    {
        return $this->hasMany(VideoProject::class);
    }
}
