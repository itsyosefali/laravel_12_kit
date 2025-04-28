<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('/users'users, '')->name('users.index')->middleware(['auth', 'verified']);
Route::get('/users', [UserController::class, 'index'])->name('users.index');

Route::inertia('/users/create', 'Users/create')->name('users.create')->middleware(['auth', 'verified']);
Route::inertia('roles', 'Roles/index')->name('roles.index')->middleware(['auth', 'verified']);

Route::inertia('roles/create', 'Roles/create')->name('roles.create')->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
