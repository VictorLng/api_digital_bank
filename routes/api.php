<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(function () {
    Route::post('register', 'Register');
    Route::post('login', 'Login');
});

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
});
