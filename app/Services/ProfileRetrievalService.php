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
     * @param string $format Output format: "json", "json-ld", "rdf", "vcard", "csv", "xml"
     * @param string $locale The locale/language code (default 'en')
     * @return array
     */
    public function getProfile(
        User $user,
        ?string $contextSlug = null,
        ?User $requester = null,
        string $format = 'json',
        string $locale = 'en'
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

        // Try to get values for the requested locale
        $values = $context->values()
            ->with('attribute')
            ->where('locale', $locale)
            ->get()
            ->filter(fn($v) => $this->canAccess($v, $requester, $user));

        // Fallback to 'en' if no values found for the requested locale
        if ($values->isEmpty() && $locale !== 'en') {
            $values = $context->values()
                ->with('attribute')
                ->where('locale', 'en')
                ->get()
                ->filter(fn($v) => $this->canAccess($v, $requester, $user));
        }

        // Build translated labels for attribute keys
        $labels = $values->mapWithKeys(fn($v) => [
            $v->attribute->key => $v->attribute->translatedName($locale),
        ])->toArray();

        $values = $values->mapWithKeys(fn($v) => [
            $v->attribute->key => [
                'value' => $v->value,
                'visibility' => $v->visibility,
            ]
        ]);

        return match ($format) {
            'json-ld' => $this->formatAsJsonLd($values, $user, $context, $labels),
            'rdf' => ['_raw' => $this->formatAsRdf($values, $user, $context), '_content_type' => 'text/turtle'],
            'vcard' => ['_raw' => $this->formatAsVCard($values), '_content_type' => 'text/vcard'],
            'csv' => ['_raw' => $this->formatAsCSV($values), '_content_type' => 'text/csv'],
            'xml' => ['_raw' => $this->formatAsXML($values), '_content_type' => 'application/xml'],
            default => $this->formatAsJson($values, $context, $labels),
        };
    }

    /**
     * Update profile attribute values for a specific context.
     */
    public function updateProfile(User $user, string $contextSlug, array $values, User $requester, string $locale = 'en'): array
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
                    'locale' => $locale,
                ],
                [
                    'value' => $data['value'] ?? null,
                    'visibility' => $data['visibility'] ?? 'public',
                ]
            );
        }

        $this->logAccess($user, $context->slug, $requester, 200);

        return $this->getProfile($user, $contextSlug, $requester, 'json', $locale);
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
    private function formatAsJson(Collection $values, Context $context, array $labels = []): array
    {
        return [
            'context' => $context->slug,
            '_labels' => $labels,
            ...$values->toArray(),
        ];
    }

    /**
     * Format response as JSON-LD with semantic web metadata.
     * Uses idm:value and idm:visibility for custom metadata.
     */
    private function formatAsJsonLd(Collection $values, User $user, Context $context, array $labels = []): array
    {
        return [
            '@context' => $this->buildContext($context),
            '@type' => 'schema:Person',
            '@id' => "/api/profiles/{$user->username}/{$context->slug}",
            'context' => $context->slug,
            '_labels' => $labels,
            ...$values->toArray(),
        ];
    }

    /**
     * Format response as RDF/Turtle with Schema.org vocabulary.
     */
    private function formatAsRdf(Collection $values, User $user, Context $context): string
    {
        $lines = [];
        $lines[] = '@prefix schema: <https://schema.org/> .';
        $profileUri = url("/api/profiles/{$user->username}/{$context->slug}");
        $lines[] = "<{$profileUri}> a schema:Person ;";

        $entries = [];
        foreach ($values as $key => $data) {
            $schemaProperty = $this->mapKeyToSchemaProperty($key);
            $escapedValue = addslashes($data['value']);
            $entries[] = "    schema:{$schemaProperty} \"{$escapedValue}\"";
        }

        if (!empty($entries)) {
            $lines[] = implode(" ;\n", $entries) . ' .';
        } else {
            // Remove the trailing semicolon and close the statement
            $lines[count($lines) - 1] = rtrim($lines[count($lines) - 1], ' ;') . ' .';
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Format response as vCard 4.0.
     */
    private function formatAsVCard(Collection $values): string
    {
        $lines = [];
        $lines[] = 'BEGIN:VCARD';
        $lines[] = 'VERSION:4.0';

        $vcardMap = [
            'display_name' => 'FN',
            'email' => 'EMAIL',
            'phone' => 'TEL',
            'title' => 'TITLE',
            'job_title' => 'TITLE',
            'organization' => 'ORG',
            'url' => 'URL',
            'website' => 'URL',
            'address' => 'ADR',
            'note' => 'NOTE',
            'bio' => 'NOTE',
            'photo' => 'PHOTO',
            'nickname' => 'NICKNAME',
        ];

        foreach ($values as $key => $data) {
            $vcardProp = $vcardMap[$key] ?? strtoupper($key);
            $lines[] = "{$vcardProp}:{$data['value']}";
        }

        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * Format response as CSV with header row.
     */
    private function formatAsCSV(Collection $values): string
    {
        $lines = [];
        $lines[] = 'key,value,visibility';

        foreach ($values as $key => $data) {
            $escapedValue = str_contains($data['value'], ',') || str_contains($data['value'], '"')
                ? '"' . str_replace('"', '""', $data['value']) . '"'
                : $data['value'];
            $lines[] = "{$key},{$escapedValue},{$data['visibility']}";
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Format response as XML with Schema.org structure.
     */
    private function formatAsXML(Collection $values): string
    {
        $lines = [];
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<Person xmlns="https://schema.org/">';

        foreach ($values as $key => $data) {
            $schemaProperty = $this->mapKeyToSchemaProperty($key);
            $escapedValue = htmlspecialchars($data['value'], ENT_XML1, 'UTF-8');
            $lines[] = "  <{$schemaProperty} visibility=\"{$data['visibility']}\">{$escapedValue}</{$schemaProperty}>";
        }

        $lines[] = '</Person>';

        return implode("\n", $lines) . "\n";
    }

    /**
     * Map an attribute key to a Schema.org property name.
     */
    private function mapKeyToSchemaProperty(string $key): string
    {
        $map = [
            'display_name' => 'name',
            'email' => 'email',
            'phone' => 'telephone',
            'job_title' => 'jobTitle',
            'organization' => 'worksFor',
            'website' => 'url',
            'url' => 'url',
            'bio' => 'description',
            'address' => 'address',
            'photo' => 'image',
        ];

        return $map[$key] ?? $key;
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
