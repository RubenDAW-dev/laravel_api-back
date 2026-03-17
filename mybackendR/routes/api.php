<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeticioneController;
use App\Http\Controllers\CategoriaController;

// Públicas
Route::post('login',    [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::get('peticiones',      [PeticioneController::class, 'index']);
Route::get('peticiones/{id}', [PeticioneController::class, 'show']);
Route::get('categorias', [CategoriaController::class, 'index']);

// Protegidas con JWT
Route::middleware('auth:api')->group(function () {
    Route::get('me',       [AuthController::class, 'me']);
    Route::post('logout',  [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']); // <-- aquí

    Route::post('peticiones',        [PeticioneController::class, 'store']);
    Route::put('peticiones/{id}',    [PeticioneController::class, 'update']);
    Route::delete('peticiones/{id}', [PeticioneController::class, 'destroy']);
    Route::post('peticiones/firmar/{id}', [PeticioneController::class, 'firmar']);

    // Opcionales tuyas
    Route::get('mispeticiones',       [PeticioneController::class, 'listMine']);
    Route::put('peticiones/estado/{id}', [PeticioneController::class, 'cambiarEstado']);
});