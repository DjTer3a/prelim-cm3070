<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

#[ApiResource(
    shortName: 'Context',
    description: 'A Context represents a specific identity presentation for a user (e.g. "work", "personal", "gaming"). Each context has its own attribute values and per-attribute visibility (public/protected/private). To retrieve profile data: GET /api/profiles/{username}/{context-slug}?format=json&lang=en. To create a context: POST /api/profiles/{username}/contexts (auth required). To deactivate: DELETE /api/profiles/{username}/contexts/{slug} (auth required).',
    operations: [
        new GetCollection(
            description: 'List all active contexts (public). Use the slug field with /api/profiles/{username}/{slug} to retrieve profile data.',
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new Get(
            description: 'Get a single context by ID (public). Shows metadata only. For profile data with visibility filtering, use GET /api/profiles/{username}/{slug}.',
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
    ],
)]
class Context extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $query) {
            $query->where('is_active', true);
        });
    }

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
