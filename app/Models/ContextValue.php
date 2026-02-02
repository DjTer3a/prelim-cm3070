<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// NOTE: ContextValue is NOT exposed as an API resource for security.
// Profile data should be accessed via /api/profiles/{username}/{context}
// which applies visibility filtering (public/protected/private).
// Direct value management is done via the Filament admin panel.

class ContextValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'context_id',
        'profile_attribute_id',
        'value',
        'visibility',
        'locale',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProfileAttribute::class, 'profile_attribute_id');
    }
}
