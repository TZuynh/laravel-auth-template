<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPassword;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    public function getInitialsAttribute(): string
    {
        $initials = collect(explode(' ', trim((string) $this->name)))
            ->filter()
            ->map(fn (string $part): string => mb_substr($part, 0, 1))
            ->take(2)
            ->implode('');

        return $initials !== '' ? mb_strtoupper($initials) : 'AD';
    }

    public function getAvatarUrlAttribute(): string
    {
        $avatar = trim((string) $this->avatar);

        if ($avatar !== '') {
            if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
                return $avatar;
            }

            if (str_starts_with($avatar, 'storage/')) {
                return asset($avatar);
            }

            if (Storage::disk('public')->exists($avatar)) {
                return asset('storage/' . $avatar);
            }
        }

        $initials = e($this->initials);
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160">
  <rect width="160" height="160" rx="32" fill="#2563eb"/>
  <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-family="Arial, sans-serif" font-size="54" font-weight="800">{$initials}</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    public function productAssets(): HasMany
    {
        return $this->hasMany(ProductAsset::class);
    }

    public function videoProjects(): HasMany
    {
        return $this->hasMany(VideoProject::class);
    }
}
