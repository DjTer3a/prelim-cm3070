<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    shortName: 'ProfileAttribute',
    description: 'Defines the types of data stored in profiles (e.g. display_name, email, bio, job_title). Each has a key, name, data_type, schema_type (Schema.org), and translations. Attribute definitions are read-only via API; management is done via the admin panel. Profile values for these attributes are accessed via GET /api/profiles/{username}/{context}.',
    operations: [
        new GetCollection(
            description: 'List all profile attribute definitions (public). Use these keys when updating profiles via PUT /api/profiles/{username}/{context}.',
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new Get(
            description: 'Get a single profile attribute definition by ID (public).',
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
    ],
)]
class ProfileAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'translations',
        'data_type',
        'schema_type',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'translations' => 'array',
    ];

    public function translatedName(string $locale = 'en'): string
    {
        if ($locale !== 'en' && $this->translations && isset($this->translations[$locale])) {
            return $this->translations[$locale];
        }

        return $this->name;
    }

    #[ApiProperty(types: ['https://schema.org/PropertyValue'])]
    public function getSchemaTypeAttribute(): ?string
    {
        return $this->attributes['schema_type'] ?? null;
    }

    public function contextValues(): HasMany
    {
        return $this->hasMany(ContextValue::class);
    }
}
