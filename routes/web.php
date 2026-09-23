<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/design-guide', function () {
    return view('design-guide');
})->name('design.guide');
