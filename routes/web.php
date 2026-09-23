<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/design-guide', function () {
    return view('design-guide');
})->name('design.guide');

// Authentication Routes (UI/UX Mock & Entry)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');
