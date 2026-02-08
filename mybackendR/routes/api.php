<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeticioneController;

// Rutas públicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Rutas protegidas por token
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

// Refresh token (no requiere auth:api estricto)
Route::post('refresh', [AuthController::class, 'refresh'])->middleware('api');

// Rutas de peticiones públicas
Route::get('peticiones', [PeticioneController::class, 'index']);
Route::get('peticiones/{id}', [PeticioneController::class, 'show']);

// Rutas protegidas de peticiones
Route::middleware('auth:api')->group(function () {
    Route::get('mispeticiones', [PeticioneController::class, 'listMine']);
    Route::post('peticiones', [PeticioneController::class, 'store']);
    Route::put('peticiones/{id}', [PeticioneController::class, 'update']);
    Route::delete('peticiones/{id}', [PeticioneController::class, 'destroy']);
    Route::put('peticiones/firmar/{id}', [PeticioneController::class, 'firmar']);
    Route::put('peticiones/estado/{id}', [PeticioneController::class, 'cambiarEstado']);
});
