<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CredentialController;
use App\Http\Controllers\Api\VaultController;
use Illuminate\Support\Facades\Route;

// Auth routes (public - NOT prefixed with v1)
Route::get('/auth/google', [AuthController::class, 'googleLogin']);
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);

Route::prefix('v1')->group(function () {
    // Auth routes (public)
    Route::post('/auth/verify-token', [AuthController::class, 'verifyToken']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Public vault routes to match current frontend (no token required)
    Route::get('/vaults', [VaultController::class, 'index']);
    Route::post('/vaults', [VaultController::class, 'store']);
    Route::delete('/vaults/{id}', [VaultController::class, 'destroy']);

    // Credentials (keep public for now if needed later)
    Route::get('/vaults/{vault_id}/credentials', [CredentialController::class, 'index']);
    Route::post('/credentials', [CredentialController::class, 'store']);
    Route::get('/credentials/{id}', [CredentialController::class, 'show']);
    Route::put('/credentials/{id}', [CredentialController::class, 'update']);
    Route::delete('/credentials/{id}', [CredentialController::class, 'destroy']);
});