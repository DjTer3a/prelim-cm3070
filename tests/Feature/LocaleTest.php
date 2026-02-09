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

test('profile returns english by default', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'English Name',
        'visibility' => 'public',
        'locale' => 'en',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/work");

    $response->assertOk()
        ->assertJsonPath('display_name.value', 'English Name');
});

test('profile returns specific locale when requested', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'English Name',
        'visibility' => 'public',
        'locale' => 'en',
    ]);

    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'الاسم بالعربي',
        'visibility' => 'public',
        'locale' => 'ar',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/work?lang=ar");

    $response->assertOk()
        ->assertJsonPath('display_name.value', 'الاسم بالعربي');
});

test('profile falls back to english when locale not available', function () {
    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'English Name',
        'visibility' => 'public',
        'locale' => 'en',
    ]);

    $response = $this->getJson("/api/profiles/{$this->user->username}/work?lang=fr");

    $response->assertOk()
        ->assertJsonPath('display_name.value', 'English Name');
});
