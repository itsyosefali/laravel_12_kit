<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware('api')->group(function () {
	Route::get('/users', [UserController::class, 'index'])->name('users.index');
});