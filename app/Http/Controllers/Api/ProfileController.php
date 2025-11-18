<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ProfileRetrievalService;
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
