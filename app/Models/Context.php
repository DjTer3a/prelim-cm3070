<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    shortName: 'Context',
    description: 'A Context represents a specific identity presentation for a user (e.g., "work", "personal", "gaming"). Each context can have different attribute values and visibility settings. To get profile data, use GET /api/profiles/{username}/{context-slug}. Context management is done via the admin panel.',
    operations: [
        new GetCollection(
            description: 'List all contexts. To get actual profile data with visibility filtering, use /api/profiles/{username}/{context-slug}.'
        ),
        new Get(
            description: 'Get a single context metadata by ID. For profile data, use /api/profiles/{username}/{context-slug}.'
        ),
    ],
)]
class Context extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ContextValue::class);
    }
}
