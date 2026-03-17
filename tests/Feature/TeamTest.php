<?php

use App\Models\User;
use App\Models\Team;
use App\Models\Context;
use App\Models\ProfileAttribute;
use App\Models\ContextValue;

test('user can create a team', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/teams', [
        'name' => 'Royal Court',
        'slug' => 'royal-court',
        'description' => 'The royal court team',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('name', 'Royal Court')
        ->assertJsonPath('slug', 'royal-court');

    $this->assertDatabaseHas('teams', ['slug' => 'royal-court', 'owner_id' => $user->id]);
    $this->assertDatabaseHas('team_user', ['team_id' => $response->json('id'), 'user_id' => $user->id, 'role' => 'owner']);
});

test('team owner can add members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create(['username' => 'newmember']);

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner']);

    $response = $this->actingAs($owner)->postJson("/api/teams/{$team->slug}/members", [
        'username' => 'newmember',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('team_user', ['team_id' => $team->id, 'user_id' => $member->id, 'status' => 'pending']);
});

test('team owner can remove members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create(['username' => 'removable']);

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team->members()->attach($member->id, ['role' => 'member', 'status' => 'accepted']);

    $response = $this->actingAs($owner)->deleteJson("/api/teams/{$team->slug}/members/{$member->username}");

    $response->assertOk();
    $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $member->id]);
});

test('non-owner cannot add members', function () {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();
    $newMember = User::factory()->create(['username' => 'someone']);

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team->members()->attach($nonOwner->id, ['role' => 'member', 'status' => 'accepted']);

    $response = $this->actingAs($nonOwner)->postJson("/api/teams/{$team->slug}/members", [
        'username' => 'someone',
    ]);

    $response->assertStatus(403);
});

test('non-owner cannot delete team', function () {
    $owner = User::factory()->create();
    $nonOwner = User::factory()->create();

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team->members()->attach($nonOwner->id, ['role' => 'member', 'status' => 'accepted']);

    $response = $this->actingAs($nonOwner)->deleteJson("/api/teams/{$team->slug}");

    $response->assertStatus(403);
});

test('team member can see private attributes of other team members', function () {
    $owner = User::factory()->create(['username' => 'teamowner']);
    $member = User::factory()->create(['username' => 'teammember']);

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team->members()->attach($member->id, ['role' => 'member', 'status' => 'accepted']);

    $context = Context::factory()->create([
        'user_id' => $owner->id,
        'slug' => 'work',
        'is_active' => true,
    ]);

    $attr = ProfileAttribute::factory()->create([
        'key' => 'phone',
        'name' => 'Phone',
    ]);

    ContextValue::factory()->create([
        'context_id' => $context->id,
        'profile_attribute_id' => $attr->id,
        'value' => '+20 100 123 4567',
        'visibility' => 'private',
    ]);

    $response = $this->actingAs($member)->getJson('/api/profiles/teamowner/work');

    $response->assertOk()
        ->assertJsonPath('phone.value', '+20 100 123 4567');
});

test('non-team-member still cannot see private attributes', function () {
    $owner = User::factory()->create(['username' => 'profileowner']);
    $stranger = User::factory()->create();

    $context = Context::factory()->create([
        'user_id' => $owner->id,
        'slug' => 'work',
        'is_active' => true,
    ]);

    $attr = ProfileAttribute::factory()->create([
        'key' => 'phone',
        'name' => 'Phone',
    ]);

    ContextValue::factory()->create([
        'context_id' => $context->id,
        'profile_attribute_id' => $attr->id,
        'value' => '+20 100 123 4567',
        'visibility' => 'private',
    ]);

    $response = $this->actingAs($stranger)->getJson('/api/profiles/profileowner/work');

    $response->assertOk()
        ->assertJsonMissing(['phone']);
});

test('removing member revokes private attribute access', function () {
    $owner = User::factory()->create(['username' => 'revokeowner']);
    $member = User::factory()->create(['username' => 'revokemember']);

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team->members()->attach($member->id, ['role' => 'member', 'status' => 'accepted']);

    $context = Context::factory()->create([
        'user_id' => $owner->id,
        'slug' => 'work',
        'is_active' => true,
    ]);

    $attr = ProfileAttribute::factory()->create([
        'key' => 'phone',
        'name' => 'Phone',
    ]);

    ContextValue::factory()->create([
        'context_id' => $context->id,
        'profile_attribute_id' => $attr->id,
        'value' => '+20 100 123 4567',
        'visibility' => 'private',
    ]);

    // Member can see private attribute
    $response = $this->actingAs($member)->getJson('/api/profiles/revokeowner/work');
    $response->assertOk()->assertJsonPath('phone.value', '+20 100 123 4567');

    // Remove member
    $team->members()->detach($member->id);

    // Member can no longer see private attribute
    $response = $this->actingAs($member)->getJson('/api/profiles/revokeowner/work');
    $response->assertOk()->assertJsonMissing(['phone']);
});

test('pending member cannot see private attributes', function () {
    $owner = User::factory()->create(['username' => 'pendingowner']);
    $member = User::factory()->create(['username' => 'pendingmember']);

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team->members()->attach($member->id, ['role' => 'member', 'status' => 'pending']);

    $context = Context::factory()->create([
        'user_id' => $owner->id,
        'slug' => 'work',
        'is_active' => true,
    ]);

    $attr = ProfileAttribute::factory()->create([
        'key' => 'phone',
        'name' => 'Phone',
    ]);

    ContextValue::factory()->create([
        'context_id' => $context->id,
        'profile_attribute_id' => $attr->id,
        'value' => '+20 100 123 4567',
        'visibility' => 'private',
    ]);

    $response = $this->actingAs($member)->getJson('/api/profiles/pendingowner/work');

    $response->assertOk()
        ->assertJsonMissing(['phone']);
});

test('invited user can accept invitation', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team->members()->attach($member->id, ['role' => 'member', 'status' => 'pending']);

    $response = $this->actingAs($member)->postJson("/api/invitations/{$team->slug}/accept");

    $response->assertOk()
        ->assertJsonPath('message', 'Invitation accepted.');

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
        'status' => 'accepted',
    ]);
});

test('invited user can decline invitation', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team->members()->attach($member->id, ['role' => 'member', 'status' => 'pending']);

    $response = $this->actingAs($member)->postJson("/api/invitations/{$team->slug}/decline");

    $response->assertOk()
        ->assertJsonPath('message', 'Invitation declined.');

    $this->assertDatabaseMissing('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
    ]);
});

test('user can list pending invitations', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team1 = Team::factory()->create(['owner_id' => $owner->id]);
    $team1->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team1->members()->attach($member->id, ['role' => 'member', 'status' => 'pending']);

    $team2 = Team::factory()->create(['owner_id' => $owner->id]);
    $team2->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team2->members()->attach($member->id, ['role' => 'member', 'status' => 'pending']);

    $response = $this->actingAs($member)->getJson('/api/invitations');

    $response->assertOk()
        ->assertJsonCount(2);
});

test('after accepting invitation member can see private attributes', function () {
    $owner = User::factory()->create(['username' => 'acceptowner']);
    $member = User::factory()->create(['username' => 'acceptmember']);

    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $team->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $team->members()->attach($member->id, ['role' => 'member', 'status' => 'pending']);

    $context = Context::factory()->create([
        'user_id' => $owner->id,
        'slug' => 'work',
        'is_active' => true,
    ]);

    $attr = ProfileAttribute::factory()->create([
        'key' => 'phone',
        'name' => 'Phone',
    ]);

    ContextValue::factory()->create([
        'context_id' => $context->id,
        'profile_attribute_id' => $attr->id,
        'value' => '+20 100 123 4567',
        'visibility' => 'private',
    ]);

    // Cannot see while pending
    $response = $this->actingAs($member)->getJson('/api/profiles/acceptowner/work');
    $response->assertOk()->assertJsonMissing(['phone']);

    // Accept invitation
    $this->actingAs($member)->postJson("/api/invitations/{$team->slug}/accept");

    // Can see after accepting
    $response = $this->actingAs($member)->getJson('/api/profiles/acceptowner/work');
    $response->assertOk()->assertJsonPath('phone.value', '+20 100 123 4567');
});

test('team index only shows accepted teams', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $acceptedTeam = Team::factory()->create(['owner_id' => $owner->id, 'name' => 'Accepted Team']);
    $acceptedTeam->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $acceptedTeam->members()->attach($member->id, ['role' => 'member', 'status' => 'accepted']);

    $pendingTeam = Team::factory()->create(['owner_id' => $owner->id, 'name' => 'Pending Team']);
    $pendingTeam->members()->attach($owner->id, ['role' => 'owner', 'status' => 'accepted']);
    $pendingTeam->members()->attach($member->id, ['role' => 'member', 'status' => 'pending']);

    $response = $this->actingAs($member)->getJson('/api/teams');

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.name', 'Accepted Team');
});
