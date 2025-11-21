<?php

use App\Http\Controllers\Api\ProfileController;
use App\Models\User;
use App\Models\Context;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Simple endpoints for profile viewer (plain JSON)
Route::get('/viewer/users', function () {
    return User::select('id', 'name', 'username')->get();
});

Route::get('/viewer/contexts', function () {
    return Context::select('id', 'user_id', 'name', 'slug')->where('is_active', true)->get();
});

Route::get('/viewer/attributes', function () {
    return \App\Models\ProfileAttribute::select('id', 'key', 'name', 'data_type', 'schema_type')->get();
});

// Profile endpoint: /api/profiles/{username}/{context?}?format=json|json-ld
// When context is omitted, returns the user's default context
Route::get('/profiles/{username}/{context?}', [ProfileController::class, 'show'])
    ->name('api.profiles.show');
