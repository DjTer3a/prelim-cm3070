<?php

use App\Models\User;
use App\Models\Context;
use App\Models\ProfileAttribute;
use App\Models\ContextValue;

beforeEach(function () {
    $this->user = User::factory()->create([
        'username' => 'testuser',
    ]);
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

test('public profile returns only public attributes', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'Public Name',
        'visibility' => 'public',
    ]);

    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->emailAttr->id,
        'value' => 'secret@email.com',
        'visibility' => 'private',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/work");

    $response->assertOk()
        ->assertJsonPath('display_name.value', 'Public Name')
        ->assertJsonMissing(['email']);
});

test('owner can see private attributes', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->emailAttr->id,
        'value' => 'secret@email.com',
        'visibility' => 'private',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/profiles/{$this->user->username}/work");

    $response->assertOk()
        ->assertJsonPath('email.value', 'secret@email.com');
});

test('other authenticated user cannot see private attributes', function () {
    $otherUser = User::factory()->create();

    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->emailAttr->id,
        'value' => 'secret@email.com',
        'visibility' => 'private',
    ]);

    $response = $this->actingAs($otherUser)
        ->getJson("/api/profiles/{$this->user->username}/work");

    $response->assertOk()
        ->assertJsonMissing(['email']);
});

test('authenticated user can see protected attributes', function () {
    $otherUser = User::factory()->create();

    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->emailAttr->id,
        'value' => 'protected@email.com',
        'visibility' => 'protected',
    ]);

    $response = $this->actingAs($otherUser)
        ->getJson("/api/profiles/{$this->user->username}/work");

    $response->assertOk()
        ->assertJsonPath('email.value', 'protected@email.com');
});

test('unauthenticated user cannot see protected attributes', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->emailAttr->id,
        'value' => 'protected@email.com',
        'visibility' => 'protected',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/work");

    $response->assertOk()
        ->assertJsonMissing(['email']);
});

test('nonexistent context returns 404', function () {
    $response = $this->getJson("/api/profiles/{$this->user->username}/nonexistent");

    $response->assertNotFound()
        ->assertJson(['error' => true]);
});

test('response includes json-ld context', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'Test Name',
        'visibility' => 'public',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/work?format=json-ld");

    $response->assertOk()
        ->assertJsonStructure(['@context', '@type', '@id']);
});

test('inactive context returns 404', function () {
    $inactiveContext = Context::factory()->create([
        'user_id' => $this->user->id,
        'slug' => 'inactive',
        'is_active' => false,
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/inactive");

    $response->assertNotFound();
});

test('omitting context returns default context', function () {
    // Mark the work context as default
    $this->context->update(['is_default' => true]);

    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'Default Context Name',
        'visibility' => 'public',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}");

    $response->assertOk()
        ->assertJsonPath('context', 'work')
        ->assertJsonPath('display_name.value', 'Default Context Name');
});

test('omitting context falls back to first active context when no default set', function () {
    // Ensure no default is set
    $this->context->update(['is_default' => false]);

    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'Fallback Context Name',
        'visibility' => 'public',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}");

    $response->assertOk()
        ->assertJsonPath('context', 'work')
        ->assertJsonPath('display_name.value', 'Fallback Context Name');
});

test('profile retrieval includes profile_photo as public field', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'Test Name',
        'visibility' => 'public',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/work");

    $response->assertOk()
        ->assertJsonPath('profile_photo.value', $this->user->profile_photo)
        ->assertJsonPath('profile_photo.visibility', 'public');
});

test('profile_photo is visible to unauthenticated users', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'Test Name',
        'visibility' => 'public',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/work");

    $response->assertOk()
        ->assertJsonPath('profile_photo.value', $this->user->profile_photo);
});

test('profile without photo does not include profile_photo field', function () {
    $this->user->update(['profile_photo' => null]);

    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'Test Name',
        'visibility' => 'public',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/work");

    $response->assertOk()
        ->assertJsonMissing(['profile_photo']);
});

test('viewer attributes endpoint returns is_system field', function () {
    ProfileAttribute::factory()->create(['key' => 'viewer_test', 'is_system' => true]);

    $response = $this->getJson('/api/viewer/attributes');

    $response->assertOk();
    $attributes = collect($response->json());
    $attr = $attributes->firstWhere('key', 'viewer_test');
    expect($attr)->not->toBeNull();
    expect($attr['is_system'])->toBeTrue();
});
