<?php

use App\Models\User;
use App\Models\ProfileAttribute;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can create a custom attribute', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/attributes', [
            'key' => 'twitter_handle',
            'name' => 'Twitter Handle',
            'data_type' => 'string',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('key', 'twitter_handle')
        ->assertJsonPath('name', 'Twitter Handle')
        ->assertJsonPath('data_type', 'string')
        ->assertJsonPath('is_system', false);

    $this->assertDatabaseHas('profile_attributes', [
        'key' => 'twitter_handle',
        'is_system' => false,
    ]);
});

test('unauthenticated user cannot create attribute', function () {
    $response = $this->postJson('/api/attributes', [
        'key' => 'test',
        'name' => 'Test',
        'data_type' => 'string',
    ]);

    $response->assertStatus(401);
});

test('attribute creation requires all fields', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/attributes', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['key', 'name', 'data_type']);
});

test('attribute key must be unique', function () {
    ProfileAttribute::factory()->create(['key' => 'existing_key']);

    $response = $this->actingAs($this->user)
        ->postJson('/api/attributes', [
            'key' => 'existing_key',
            'name' => 'Duplicate',
            'data_type' => 'string',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['key']);
});

test('attribute key must be alpha_dash', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/attributes', [
            'key' => 'invalid key!',
            'name' => 'Bad Key',
            'data_type' => 'string',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['key']);
});

test('attribute data_type must be valid', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/attributes', [
            'key' => 'valid_key',
            'name' => 'Valid',
            'data_type' => 'integer',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['data_type']);
});

test('created attribute has schema_type null and is_system false', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/attributes', [
            'key' => 'custom_field',
            'name' => 'Custom Field',
            'data_type' => 'text',
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('profile_attributes', [
        'key' => 'custom_field',
        'schema_type' => null,
        'is_system' => false,
    ]);
});
