<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ApiResource(
    shortName: 'User',
    description: 'A user account with multiple identity contexts. To register: POST /api/register. To authenticate: POST /api/login (returns Bearer token). For profile data: GET /api/profiles/{username}/{context}?format=json|json-ld|rdf|vcard|csv|xml&lang=en. To update profiles: PUT /api/profiles/{username}/{context} (auth required).',
    operations: [
        new GetCollection(
            description: 'List all users (public). Use the username field with /api/profiles/{username}/{context} to retrieve profile data.',
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new Get(
            description: 'Get a single user by ID (public). For context-specific profile data with visibility controls, use GET /api/profiles/{username}/{context} instead.',
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
    ],
)]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'profile_photo',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'teams',
        'pendingInvitations',
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

    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('role', 'status')->withTimestamps();
    }

    public function pendingInvitations(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('role', 'status')->withTimestamps()->wherePivot('status', 'pending');
    }
}
