<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeticioneController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUsersController;

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

// Rutas de Administrador (protegidas por auth:api + is_admin)
Route::middleware(['auth:api', 'is_admin'])->prefix('admin')->group(function () {
    // Peticiones
    Route::get('/peticiones',           [AdminController::class, 'indexPeticiones']);
    Route::post('/peticiones',          [AdminController::class, 'createPeticion']);
    Route::get('/peticiones/{id}',      [AdminController::class, 'showPeticion']);
    Route::put('/peticiones/{id}',      [AdminController::class, 'updatePeticion']);
    Route::delete('/peticiones/{id}',   [AdminController::class, 'destroyPeticion']);

    // Usuarios
    Route::get('/users',         [AdminUsersController::class, 'getUsers']);
    Route::post('/users',        [AdminUsersController::class, 'createUser']);
    Route::get('/users/{id}',    [AdminUsersController::class, 'showUser']);
    Route::put('/users/{id}',    [AdminUsersController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminUsersController::class, 'destroyUser']);
});