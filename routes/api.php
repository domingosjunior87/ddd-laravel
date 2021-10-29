<?php

use App\UI\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'registrar']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'usuario']);
    Route::put('user', [AuthController::class, 'atualizar']);
    Route::post('logout', [AuthController::class, 'logout']);
});
