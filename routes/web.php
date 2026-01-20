<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('profile-viewer');
});

Route::get('/register', fn() => view('register'));
Route::get('/editor', fn() => view('profile-editor'));
Route::get('/teams', fn() => view('team-editor'));
