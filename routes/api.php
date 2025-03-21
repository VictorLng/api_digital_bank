<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerAccountController;

Route::controller(UserController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});


Route::middleware('auth:api')->group(function () {

    Route::post('logout', [UserController::class, 'logout']);

    Route::prefix('accounts')->group(function () {

        Route::post('deposit', [CustomerAccountController::class, 'addFunds']);
        Route::post('withdraw', [CustomerAccountController::class, 'makeWithdrawal']);
        Route::post('transfer', [CustomerAccountController::class, 'makeTransfer']);

        Route::get('balance', [CustomerAccountController::class, 'getBalance']);
        Route::get('statement', [CustomerAccountController::class, 'getStatement']);
        Route::get('details', [CustomerAccountController::class, 'getAccountData']);
        Route::get('lookup/{account_number}', [CustomerAccountController::class, 'getAccountDataByNumber']);
    });
});

Route::get('/accounts/statement', [CustomerAccountController::class, 'getStatement'])->middleware('auth:api');

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint não encontrado. Verifique a URL e o método HTTP.'
    ], 404);
});
