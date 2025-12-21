<?php

use App\Models\User;
use App\Models\Context;

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
