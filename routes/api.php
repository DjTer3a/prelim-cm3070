<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Models\User;
use App\Models\Context;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.logout');

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

// Authenticated profile editing routes
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/profiles/{username}/{context}', [ProfileController::class, 'update'])
        ->name('api.profiles.update');
    Route::post('/profiles/{username}/contexts', [ProfileController::class, 'createContext'])
        ->name('api.profiles.createContext');
    Route::put('/profiles/{username}/contexts/{context}', [ProfileController::class, 'updateContext'])
        ->name('api.profiles.updateContext');
    Route::delete('/profiles/{username}/contexts/{context}', [ProfileController::class, 'deleteContext'])
        ->name('api.profiles.deleteContext');
    Route::delete('/profiles/{username}/{context}/{attributeKey}', [ProfileController::class, 'deleteValue'])
        ->name('api.profiles.deleteValue');
});
