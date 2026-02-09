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

    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->nameAttr->id,
        'value' => 'Test User',
        'visibility' => 'public',
        'locale' => 'en',
    ]);

    ContextValue::factory()->create([
        'context_id' => $this->context->id,
        'profile_attribute_id' => $this->emailAttr->id,
        'value' => 'test@example.com',
        'visibility' => 'public',
        'locale' => 'en',
    ]);
});

test('profile returns rdf turtle format', function () {
    $response = $this->get("/api/profiles/{$this->user->username}/work?format=rdf");

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/turtle');
    expect($response->getContent())->toContain('@prefix');
    expect($response->getContent())->toContain('schema:Person');
});

test('profile returns vcard format', function () {
    $response = $this->get("/api/profiles/{$this->user->username}/work?format=vcard");

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/vcard');
    expect($response->getContent())->toContain('BEGIN:VCARD');
    expect($response->getContent())->toContain('VERSION:4.0');
    expect($response->getContent())->toContain('END:VCARD');
});

test('profile returns csv format', function () {
    $response = $this->get("/api/profiles/{$this->user->username}/work?format=csv");

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
    expect($response->getContent())->toContain('key,value,visibility');
});

test('profile returns xml format', function () {
    $response = $this->get("/api/profiles/{$this->user->username}/work?format=xml");

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/xml');
    expect($response->getContent())->toContain('<?xml version="1.0" encoding="UTF-8"?>');
    expect($response->getContent())->toContain('<Person xmlns="https://schema.org/">');
});

test('invalid format falls back to json', function () {
    $response = $this->getJson("/api/profiles/{$this->user->username}/work?format=invalid");

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/json');
    $response->assertJsonPath('context', 'work');
});
