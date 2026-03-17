<?php

use App\Models\User;
use App\Models\Context;
use App\Models\ProfileAttribute;
use App\Models\ContextValue;

test('user can register with valid data', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Hatshepsut',
        'username' => 'hatshepsut',
        'email' => 'hatshepsut@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'username', 'email']])
        ->assertJsonPath('user.username', 'hatshepsut');

    $this->assertDatabaseHas('users', [
        'username' => 'hatshepsut',
        'email' => 'hatshepsut@example.com',
    ]);
});

test('registration requires all fields', function () {
    $response = $this->postJson('/api/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'username', 'email', 'password']);
});

test('registration rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'Test',
        'username' => 'newuser',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('registration rejects duplicate username', function () {
    User::factory()->create(['username' => 'taken']);

    $response = $this->postJson('/api/register', [
        'name' => 'Test',
        'username' => 'taken',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

test('registration creates default context', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Hatshepsut',
        'username' => 'hatshepsut',
        'email' => 'hatshepsut@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);

    $user = User::where('username', 'hatshepsut')->first();
    $this->assertNotNull($user);

    $context = Context::where('user_id', $user->id)->where('is_default', true)->first();
    $this->assertNotNull($context);
    $this->assertEquals('personal', $context->slug);
});

test('registration token works for profile access', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Hatshepsut',
        'username' => 'hatshepsut',
        'email' => 'hatshepsut@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $token = $response->json('token');

    $profileResponse = $this->getJson('/api/profiles/hatshepsut', [
        'Authorization' => "Bearer {$token}",
    ]);

    $profileResponse->assertOk();
});

test('registration response includes profile_photo as gravatar url', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Pharaoh',
        'username' => 'pharaoh',
        'email' => 'pharaoh@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('user.profile_photo', 'https://www.gravatar.com/avatar/' . md5('pharaoh') . '?d=identicon&s=200');
});

test('registration seeds display_name into default context', function () {
    ProfileAttribute::factory()->create([
        'key' => 'display_name',
        'name' => 'Display Name',
    ]);

    $response = $this->postJson('/api/register', [
        'name' => 'Hatshepsut the Great',
        'username' => 'hatshepsut2',
        'email' => 'hatshepsut2@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);

    $user = User::where('username', 'hatshepsut2')->first();
    $context = Context::where('user_id', $user->id)->where('is_default', true)->first();
    $displayNameAttr = ProfileAttribute::where('key', 'display_name')->first();

    $contextValue = ContextValue::where('context_id', $context->id)
        ->where('profile_attribute_id', $displayNameAttr->id)
        ->first();

    expect($contextValue)->not->toBeNull();
    expect($contextValue->value)->toBe('Hatshepsut the Great');
    expect($contextValue->visibility)->toBe('public');
    expect($contextValue->locale)->toBe('en');
});

test('registration succeeds when display_name attribute does not exist', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'NoAttr User',
        'username' => 'noattruser',
        'email' => 'noattr@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
});

test('login with valid credentials returns token and user', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'username', 'email', 'profile_photo']])
        ->assertJsonPath('user.profile_photo', $user->profile_photo);
});

test('login rejects invalid credentials', function () {
    User::factory()->create(['email' => 'user@example.com']);

    $response = $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login requires email and password', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});
