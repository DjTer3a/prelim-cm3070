<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TeamController;
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
    return User::select('id', 'name', 'username', 'email', 'profile_photo')
        ->whereIn('username', ['admin', 'imhotep', 'nefertiti', 'tutankhamun', 'cleopatra', 'ramesses'])
        ->get();
});

Route::get('/viewer/contexts', function () {
    return Context::select('id', 'user_id', 'name', 'slug')->get();
});

Route::get('/viewer/attributes', function () {
    return \App\Models\ProfileAttribute::select('id', 'key', 'name', 'translations', 'data_type', 'schema_type', 'is_system')->get();
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
    Route::post('/profiles/{username}/photo', [ProfileController::class, 'uploadPhoto'])
        ->name('api.profiles.uploadPhoto');

    // Attribute creation
    Route::post('/attributes', function (Request $request) {
        $request->validate([
            'key' => 'required|string|max:255|alpha_dash|unique:profile_attributes,key',
            'name' => 'required|string|max:255',
            'data_type' => 'required|string|in:string,email,url,text',
        ]);

        $attribute = \App\Models\ProfileAttribute::create([
            'key' => $request->key,
            'name' => $request->name,
            'data_type' => $request->data_type,
            'schema_type' => null,
            'is_system' => false,
        ]);

        return response()->json($attribute, 201);
    })->name('api.attributes.store');

    // Team routes
    Route::get('/teams', [TeamController::class, 'index'])->name('api.teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('api.teams.store');
    Route::get('/teams/{slug}', [TeamController::class, 'show'])->name('api.teams.show');
    Route::put('/teams/{slug}', [TeamController::class, 'update'])->name('api.teams.update');
    Route::delete('/teams/{slug}', [TeamController::class, 'destroy'])->name('api.teams.destroy');
    Route::post('/teams/{slug}/members', [TeamController::class, 'addMember'])->name('api.teams.addMember');
    Route::delete('/teams/{slug}/members/{username}', [TeamController::class, 'removeMember'])->name('api.teams.removeMember');

    // Invitation routes
    Route::get('/invitations', [TeamController::class, 'pendingInvitations'])->name('api.invitations.index');
    Route::post('/invitations/{slug}/accept', [TeamController::class, 'acceptInvitation'])->name('api.invitations.accept');
    Route::post('/invitations/{slug}/decline', [TeamController::class, 'declineInvitation'])->name('api.invitations.decline');
});
