<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    shortName: 'ProfileAttribute',
    description: 'ProfileAttributes define the types of data that can be stored in a profile (e.g., "display_name", "email", "bio", "job_title"). Each attribute has a key, name, data type, and Schema.org type for semantic web compatibility. Attribute management is done via the admin panel.',
    operations: [
        new GetCollection(
            description: 'List all available profile attribute types. These define what data can be stored in profiles.'
        ),
        new Get(
            description: 'Get a single profile attribute definition by ID.'
        ),
    ],
)]
class ProfileAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'data_type',
        'schema_type',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

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
