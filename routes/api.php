<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CredentialController;
use App\Http\Controllers\Api\VaultController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth routes (public)
    Route::get('/auth/google', [AuthController::class, 'googleLogin']);
    Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);
    Route::post('/auth/verify-token', [AuthController::class, 'verifyToken']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Vaults
        Route::get('/vaults', [VaultController::class, 'index']);
        Route::post('/vaults', [VaultController::class, 'store']);
        Route::delete('/vaults/{id}', [VaultController::class, 'destroy']);

        // Credentials
        Route::get('/vaults/{vault_id}/credentials', [CredentialController::class, 'index']);
        Route::post('/credentials', [CredentialController::class, 'store']);
        Route::get('/credentials/{id}', [CredentialController::class, 'show']);
        Route::put('/credentials/{id}', [CredentialController::class, 'update']);
        Route::delete('/credentials/{id}', [CredentialController::class, 'destroy']);
    });
});