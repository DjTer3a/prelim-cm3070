<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create(['username' => 'photouser']);
});

test('owner can upload a profile photo', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/profiles/photouser/photo', [
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $response->assertOk()
        ->assertJsonStructure(['url']);

    $this->user->refresh();
    expect($this->user->profile_photo)->not->toBeNull();

    Storage::disk('public')->assertExists(
        collect(Storage::disk('public')->files('profile-photos'))->first()
    );
});

test('unauthenticated user cannot upload photo', function () {
    $response = $this->postJson('/api/profiles/photouser/photo', [
        'photo' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertStatus(401);
});

test('non-owner cannot upload photo to another user profile', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->postJson('/api/profiles/photouser/photo', [
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $response->assertStatus(403);
});

test('upload requires a photo file', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/profiles/photouser/photo', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['photo']);
});

test('upload rejects non-image files', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/profiles/photouser/photo', [
            'photo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['photo']);
});

test('upload rejects files exceeding max size', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/profiles/photouser/photo', [
            'photo' => UploadedFile::fake()->image('large.jpg')->size(3000),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['photo']);
});

test('uploading new photo replaces old local photo', function () {
    // First upload
    $this->actingAs($this->user)
        ->postJson('/api/profiles/photouser/photo', [
            'photo' => UploadedFile::fake()->image('first.jpg'),
        ]);

    $firstFile = collect(Storage::disk('public')->files('profile-photos'))->first();
    Storage::disk('public')->assertExists($firstFile);

    // Second upload
    $this->actingAs($this->user)
        ->postJson('/api/profiles/photouser/photo', [
            'photo' => UploadedFile::fake()->image('second.jpg'),
        ]);

    // Old file should be deleted, new file should exist
    $remainingFiles = Storage::disk('public')->files('profile-photos');
    expect($remainingFiles)->toHaveCount(1);
    expect($remainingFiles[0])->not->toBe($firstFile);
});

test('upload returns 404 for nonexistent username', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/profiles/nonexistent/photo', [
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $response->assertStatus(404);
});
