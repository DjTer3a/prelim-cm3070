<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Context;
use App\Models\ContextValue;
use App\Models\ProfileAttribute;
use App\Models\User;
use App\Services\ProfileRetrievalService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileRetrievalService $profileService
    ) {}

    /**
     * Get profile by username and context.
     *
     * GET /api/profiles/{username}/{context?}?format=json|json-ld
     *
     * @param string $username The user's username
     * @param string|null $context The context slug (e.g., "work", "personal", "gaming"). If null, uses default context.
     * @param string $format Response format: "json" (default), "json-ld"
     */
    public function show(Request $request, string $username, ?string $context = null): JsonResponse
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return $this->errorResponse("User '{$username}' not found", 404);
        }

        // Get format parameter (default: json)
        $format = $request->query('format', 'json');
        if (!in_array($format, ['json', 'json-ld'])) {
            $format = 'json';
        }

        // Use Sanctum guard for optional authentication
        $requester = auth('sanctum')->user();

        try {
            $profile = $this->profileService->getProfile(
                $user,
                $context,
                $requester,
                $format
            );

            $contentType = $format === 'json-ld' ? 'application/ld+json' : 'application/json';

            return response()->json($profile)
                ->header('Content-Type', $contentType);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Update profile values for a specific context.
     *
     * PUT /api/profiles/{username}/{context}
     */
    public function update(Request $request, string $username, string $context): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return $this->errorResponse("User '{$username}' not found", 404);
        }

        $request->validate([
            'values' => 'required|array',
            'values.*.value' => 'present',
            'values.*.visibility' => 'required|in:public,protected,private',
        ]);

        try {
            $profile = $this->profileService->updateProfile(
                $user,
                $context,
                $request->input('values'),
                $request->user()
            );
            return response()->json($profile);
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Create a new context for a user.
     *
     * POST /api/profiles/{username}/contexts
     */
    public function createContext(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return $this->errorResponse("User '{$username}' not found", 404);
        }

        if (!$request->user() || !$request->user()->is($user)) {
            return $this->errorResponse('Only the profile owner can manage contexts.', 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash',
        ]);

        $existing = $user->contexts()->where('slug', $request->slug)->first();
        if ($existing) {
            return $this->errorResponse("Context slug '{$request->slug}' already exists for this user.", 422);
        }

        $context = Context::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => $request->slug,
            'is_default' => false,
            'is_active' => true,
        ]);

        return response()->json([
            'id' => $context->id,
            'name' => $context->name,
            'slug' => $context->slug,
            'is_default' => $context->is_default,
            'is_active' => $context->is_active,
        ], 201);
    }

    /**
     * Update context metadata.
     *
     * PUT /api/profiles/{username}/contexts/{context}
     */
    public function updateContext(Request $request, string $username, string $context): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return $this->errorResponse("User '{$username}' not found", 404);
        }

        if (!$request->user() || !$request->user()->is($user)) {
            return $this->errorResponse('Only the profile owner can manage contexts.', 403);
        }

        $ctx = $user->contexts()->where('slug', $context)->first();
        if (!$ctx) {
            return $this->errorResponse("Context '{$context}' not found", 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|alpha_dash',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($request->has('slug') && $request->slug !== $ctx->slug) {
            $existing = $user->contexts()->where('slug', $request->slug)->first();
            if ($existing) {
                return $this->errorResponse("Context slug '{$request->slug}' already exists for this user.", 422);
            }
        }

        $ctx->update($request->only(['name', 'slug', 'is_default']));

        return response()->json([
            'id' => $ctx->id,
            'name' => $ctx->name,
            'slug' => $ctx->slug,
            'is_default' => $ctx->is_default,
            'is_active' => $ctx->is_active,
        ]);
    }

    /**
     * Deactivate a context (soft delete).
     *
     * DELETE /api/profiles/{username}/contexts/{context}
     */
    public function deleteContext(Request $request, string $username, string $context): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return $this->errorResponse("User '{$username}' not found", 404);
        }

        if (!$request->user() || !$request->user()->is($user)) {
            return $this->errorResponse('Only the profile owner can manage contexts.', 403);
        }

        $ctx = $user->contexts()->where('slug', $context)->first();
        if (!$ctx) {
            return $this->errorResponse("Context '{$context}' not found", 404);
        }

        $ctx->update(['is_active' => false]);

        return response()->json(['message' => 'Context deactivated successfully.']);
    }

    /**
     * Delete a specific attribute value from a context.
     *
     * DELETE /api/profiles/{username}/{context}/{attributeKey}
     */
    public function deleteValue(Request $request, string $username, string $context, string $attributeKey): JsonResponse
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return $this->errorResponse("User '{$username}' not found", 404);
        }

        if (!$request->user() || !$request->user()->is($user)) {
            return $this->errorResponse('Only the profile owner can delete attribute values.', 403);
        }

        $ctx = $user->contexts()->where('slug', $context)->where('is_active', true)->first();
        if (!$ctx) {
            return $this->errorResponse("Context '{$context}' not found", 404);
        }

        $attribute = ProfileAttribute::where('key', $attributeKey)->first();
        if (!$attribute) {
            return $this->errorResponse("Attribute '{$attributeKey}' not found", 404);
        }

        $deleted = ContextValue::where('context_id', $ctx->id)
            ->where('profile_attribute_id', $attribute->id)
            ->delete();

        if (!$deleted) {
            return $this->errorResponse('Value not found', 404);
        }

        return response()->json(['message' => 'Attribute value deleted.']);
    }

    /**
     * Return error response in appropriate format.
     */
    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => true,
            'message' => $message,
        ], $status)->header('Content-Type', 'application/json');
    }
}
