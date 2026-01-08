<?php

namespace App\Services;

use App\Models\User;
use App\Models\Context;
use App\Models\ContextValue;
use App\Models\ProfileAttribute;
use App\Models\AccessLog;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileRetrievalService
{
    /**
     * Get profile data for a user in a specific context.
     *
     * @param User $user The profile owner
     * @param string|null $contextSlug The context to retrieve (e.g., "work", "personal"). If null, uses default context.
     * @param User|null $requester The authenticated user requesting the profile (or null for anonymous)
     * @param string $format Output format: "json", "json-ld"
     * @return array
     */
    public function getProfile(
        User $user,
        ?string $contextSlug = null,
        ?User $requester = null,
        string $format = 'json'
    ): array {
        // If no context specified, use the default context
        if ($contextSlug === null) {
            $context = $user->contexts()
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();

            if (!$context) {
                // Fallback to first active context if no default is set
                $context = $user->contexts()
                    ->where('is_active', true)
                    ->first();
            }

            if (!$context) {
                $this->logAccess($user, 'default', $requester, 404);
                throw new ModelNotFoundException("No active context found for user");
            }
        } else {
            $context = $user->contexts()
                ->where('slug', $contextSlug)
                ->where('is_active', true)
                ->first();

            if (!$context) {
                $this->logAccess($user, $contextSlug, $requester, 404);
                throw new ModelNotFoundException("Context '{$contextSlug}' not found");
            }
        }

        $this->logAccess($user, $context->slug, $requester, 200);

        $values = $context->values()
            ->with('attribute')
            ->get()
            ->filter(fn($v) => $this->canAccess($v, $requester, $user));

        $values = $values->mapWithKeys(fn($v) => [
            $v->attribute->key => [
                'value' => $v->value,
                'visibility' => $v->visibility,
            ]
        ]);

        return match ($format) {
            'json-ld' => $this->formatAsJsonLd($values, $user, $context),
            default => $this->formatAsJson($values, $context),
        };
    }

    /**
     * Update profile attribute values for a specific context.
     */
    public function updateProfile(User $user, string $contextSlug, array $values, User $requester): array
    {
        if (!$requester->is($user)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only the profile owner can edit their profile.');
        }

        $context = $user->contexts()
            ->where('slug', $contextSlug)
            ->where('is_active', true)
            ->first();

        if (!$context) {
            throw new ModelNotFoundException("Context '{$contextSlug}' not found");
        }

        foreach ($values as $key => $data) {
            $attribute = ProfileAttribute::where('key', $key)->first();
            if (!$attribute) {
                continue;
            }

            ContextValue::updateOrCreate(
                [
                    'context_id' => $context->id,
                    'profile_attribute_id' => $attribute->id,
                ],
                [
                    'value' => $data['value'] ?? null,
                    'visibility' => $data['visibility'] ?? 'public',
                ]
            );
        }

        $this->logAccess($user, $context->slug, $requester, 200);

        return $this->getProfile($user, $contextSlug, $requester);
    }

    private function canAccess($value, ?User $requester, User $owner): bool
    {
        return match ($value->visibility) {
            'public' => true,
            'protected' => $requester !== null,
            'private' => ($requester?->is($owner) ?? false) || $this->isTeamMember($requester, $owner),
            default => false,
        };
    }

    private function isTeamMember(?User $requester, User $owner): bool
    {
        if (!$requester) {
            return false;
        }

        return $owner->teams()
            ->whereHas('members', fn($q) => $q->where('users.id', $requester->id))
            ->exists();
    }

    private function logAccess(User $user, string $contextSlug, ?User $requester, int $statusCode): void
    {
        AccessLog::create([
            'user_id' => $user->id,
            'context_slug' => $contextSlug,
            'requester' => $requester?->email ?? 'anonymous',
            'status_code' => $statusCode,
            'created_at' => now(),
        ]);
    }

    /**
     * Format response as plain JSON (default).
     */
    private function formatAsJson(Collection $values, Context $context): array
    {
        return [
            'context' => $context->slug,
            ...$values->toArray(),
        ];
    }

    /**
     * Format response as JSON-LD with semantic web metadata.
     * Uses idm:value and idm:visibility for custom metadata.
     */
    private function formatAsJsonLd(Collection $values, User $user, Context $context): array
    {
        return [
            '@context' => $this->buildContext($context),
            '@type' => 'schema:Person',
            '@id' => "/api/profiles/{$user->username}/{$context->slug}",
            'context' => $context->slug,
            ...$values->toArray(),
        ];
    }

    private function buildContext(Context $context): array
    {
        $baseContext = [
            'schema' => 'https://schema.org/',
            'idm' => 'https://identity-api.com/vocab#',
            'value' => 'idm:value',
            'visibility' => 'idm:visibility',
        ];

        // Add dynamic mappings from attributes
        foreach ($context->values()->with('attribute')->get() as $value) {
            if ($value->attribute->schema_type) {
                $baseContext[$value->attribute->key] = $value->attribute->schema_type;
            }
        }

        return $baseContext;
    }
}
