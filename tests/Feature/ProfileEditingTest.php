<?php

use App\Models\User;
use App\Models\Context;
use App\Models\ProfileAttribute;
use App\Models\ContextValue;

beforeEach(function () {
    $this->user = User::factory()->create(['username' => 'edituser']);
    $this->context = Context::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Work',
        'slug' => 'work',
        'is_active' => true,
    ]);
    $this->nameAttr = ProfileAttribute::factory()->create([
        'key' => 'display_name',
        'name' => 'Display Name',
        'schema_type' => 'https://schema.org/name',
    ]);
    $this->emailAttr = ProfileAttribute::factory()->create([
        'key' => 'email',
        'name' => 'Email',
        'schema_type' => 'https://schema.org/email',
    ]);
});

test('owner can update own profile values', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'Old Name',
        'visibility' => 'public',
    ]);

    $response = $this->actingAs($this->user)->putJson("/api/profiles/edituser/work", [
        'values' => [
            'display_name' => ['value' => 'New Name', 'visibility' => 'public'],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('display_name.value', 'New Name');

    $this->assertDatabaseHas('context_values', [
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => json_encode('New Name'),
    ]);
});

test('owner can change visibility of attributes', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->emailAttr->id,
        'value' => 'test@example.com',
        'visibility' => 'public',
    ]);

    $response = $this->actingAs($this->user)->putJson("/api/profiles/edituser/work", [
        'values' => [
            'email' => ['value' => 'test@example.com', 'visibility' => 'private'],
        ],
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('context_values', [
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->emailAttr->id,
        'visibility' => 'private',
    ]);
});

test('other user cannot edit someone else profile', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)->putJson("/api/profiles/edituser/work", [
        'values' => [
            'display_name' => ['value' => 'Hacked', 'visibility' => 'public'],
        ],
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot edit profile', function () {
    $response = $this->putJson("/api/profiles/edituser/work", [
        'values' => [
            'display_name' => ['value' => 'Hacked', 'visibility' => 'public'],
        ],
    ]);

    $response->assertStatus(401);
});

test('owner can create new context', function () {
    $response = $this->actingAs($this->user)->postJson("/api/profiles/edituser/contexts", [
        'name' => 'Gaming',
        'slug' => 'gaming',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('slug', 'gaming');

    $this->assertDatabaseHas('contexts', [
        'user_id' => $this->user->id,
        'slug' => 'gaming',
        'is_active' => true,
    ]);
});

test('owner can update context metadata', function () {
    $response = $this->actingAs($this->user)->putJson("/api/profiles/edituser/contexts/work", [
        'name' => 'Professional',
    ]);

    $response->assertOk()
        ->assertJsonPath('name', 'Professional');
});

test('owner can deactivate context', function () {
    $response = $this->actingAs($this->user)->deleteJson("/api/profiles/edituser/contexts/work");

    $response->assertOk();

    $this->assertDatabaseHas('contexts', [
        'id' => $this->context->id,
        'is_active' => false,
    ]);
});

test('cannot create duplicate context slug', function () {
    $response = $this->actingAs($this->user)->postJson("/api/profiles/edituser/contexts", [
        'name' => 'Work Duplicate',
        'slug' => 'work',
    ]);

    $response->assertStatus(422);
});
