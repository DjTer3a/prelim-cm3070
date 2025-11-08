<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ApiResource(
    shortName: 'User',
    description: 'A user account that can have multiple identity contexts. For profile data with visibility controls, use GET /api/profiles/{username}/{context}. User management is done via the admin panel.',
    operations: [
        new GetCollection(
            description: 'List all users. Returns basic info (id, name, username). Use usernames with /api/profiles/{username}/{context} to access profile data.'
        ),
        new Get(
            description: 'Get a user by ID. For context-specific profile data with visibility filtering, use /api/profiles/{username}/{context} instead.'
        ),
    ],
)]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === config('app.admin_email');
    }

    public function contexts(): HasMany
    {
        return $this->hasMany(Context::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class);
    }
}
