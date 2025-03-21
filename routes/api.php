<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(function () {
    Route::post('register', 'Register')->name('register');
    Route::post('login', 'Login');
    Route::post('forgot-password', 'forgotPassword');
});

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::put('password-change', [UserController::class, 'passwordChange']);
});
