<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teams = $request->user()->teams()->wherePivot('status', 'accepted')->with('owner:id,name,username')->get()
            ->map(fn($team) => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'description' => $team->description,
                'owner_id' => $team->owner_id,
                'owner' => $team->owner->only(['id', 'name', 'username']),
                'role' => $team->pivot->role,
            ]);

        return response()->json($teams);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:teams,slug',
            'description' => 'nullable|string|max:1000',
        ]);

        $team = Team::create([
            'owner_id' => $request->user()->id,
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
        ]);

        $team->members()->attach($request->user()->id, ['role' => 'owner', 'status' => 'accepted']);

        return response()->json([
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'description' => $team->description,
        ], 201);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $team = Team::where('slug', $slug)->first();
        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        $isOwner = $request->user() && $team->owner_id === $request->user()->id;

        $membersQuery = $team->members()->select('users.id', 'users.name', 'users.username');

        if (!$isOwner) {
            $membersQuery->wherePivot('status', 'accepted');
        }

        $members = $membersQuery->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'username' => $m->username,
                'role' => $m->pivot->role,
                'status' => $m->pivot->status,
            ]);

        return response()->json([
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'description' => $team->description,
            'owner' => $team->owner->only(['id', 'name', 'username']),
            'members' => $members,
        ]);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $team = Team::where('slug', $slug)->first();
        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        if ($team->owner_id !== $request->user()->id) {
            return $this->errorResponse('Only the team owner can update the team.', 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|alpha_dash|unique:teams,slug,' . $team->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $team->update($request->only(['name', 'slug', 'description']));

        return response()->json([
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'description' => $team->description,
        ]);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $team = Team::where('slug', $slug)->first();
        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        if ($team->owner_id !== $request->user()->id) {
            return $this->errorResponse('Only the team owner can delete the team.', 403);
        }

        $team->delete();

        return response()->json(['message' => 'Team deleted successfully.']);
    }

    public function addMember(Request $request, string $slug): JsonResponse
    {
        $team = Team::where('slug', $slug)->first();
        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        if ($team->owner_id !== $request->user()->id) {
            return $this->errorResponse('Only the team owner can add members.', 403);
        }

        $request->validate([
            'username' => 'required|string|exists:users,username',
        ]);

        $user = User::where('username', $request->username)->first();

        if ($team->members()->where('users.id', $user->id)->exists()) {
            return $this->errorResponse('User is already a team member.', 422);
        }

        $team->members()->attach($user->id, ['role' => 'member', 'status' => 'pending']);

        return response()->json(['message' => "User '{$request->username}' has been invited to the team."], 201);
    }

    public function removeMember(Request $request, string $slug, string $username): JsonResponse
    {
        $team = Team::where('slug', $slug)->first();
        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        if ($team->owner_id !== $request->user()->id) {
            return $this->errorResponse('Only the team owner can remove members.', 403);
        }

        $user = User::where('username', $username)->first();
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        if ($user->id === $team->owner_id) {
            return $this->errorResponse('Cannot remove the team owner.', 422);
        }

        $team->members()->detach($user->id);

        return response()->json(['message' => 'Member removed from team.']);
    }

    public function pendingInvitations(Request $request): JsonResponse
    {
        $invitations = $request->user()->pendingInvitations()->with('owner:id,name,username')->get()
            ->map(fn($team) => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'description' => $team->description,
                'owner' => $team->owner->only(['id', 'name', 'username']),
            ]);

        return response()->json($invitations);
    }

    public function acceptInvitation(Request $request, string $slug): JsonResponse
    {
        $team = Team::where('slug', $slug)->first();
        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        $membership = $team->members()->where('users.id', $request->user()->id)
            ->wherePivot('status', 'pending')
            ->first();

        if (!$membership) {
            return $this->errorResponse('No pending invitation found.', 404);
        }

        $team->members()->updateExistingPivot($request->user()->id, ['status' => 'accepted']);

        return response()->json(['message' => 'Invitation accepted.']);
    }

    public function declineInvitation(Request $request, string $slug): JsonResponse
    {
        $team = Team::where('slug', $slug)->first();
        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        $membership = $team->members()->where('users.id', $request->user()->id)
            ->wherePivot('status', 'pending')
            ->first();

        if (!$membership) {
            return $this->errorResponse('No pending invitation found.', 404);
        }

        $team->members()->detach($request->user()->id);

        return response()->json(['message' => 'Invitation declined.']);
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => true,
            'message' => $message,
        ], $status);
    }
}
