<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolesController;

Route::prefix('users')->group(function () {
	Route::get('/', [UserController::class, 'index'])->name('users.index');
	Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
	Route::post('/', [UserController::class, 'store'])->name('users.store');
	Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
	Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::prefix('roles')->group(function () {
    Route::get('/permissions', [RolesController::class, 'permissions'])->name('roles.permissions.index');
    Route::get('/', [RolesController::class, 'index'])->name('roles.index');
    // Route::get('/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('/', [RolesController::class, 'store'])->name('roles.store');
    Route::get('/{role}', [RolesController::class, 'show'])->name('roles.show');
    Route::put('/{role}', [RolesController::class, 'update'])->name('roles.update');
    Route::delete('/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
});
