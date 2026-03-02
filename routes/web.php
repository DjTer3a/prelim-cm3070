<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('profile-viewer');
});

Route::post('/quick-login', function () {
    $credentials = [
        'email' => request('email'),
        'password' => request('password'),
    ];

    if (Auth::attempt($credentials)) {
        request()->session()->regenerate();
        return redirect(request('redirect', '/'));
    }

    return back();
});

Route::get('/register', fn() => view('register'));
Route::get('/editor', fn() => view('profile-editor'));
Route::get('/teams', fn() => view('team-editor'));
