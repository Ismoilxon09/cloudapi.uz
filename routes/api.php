<?php

use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\EmbeddingsController;
use App\Http\Controllers\Api\V1\ImagesController;
use App\Http\Controllers\Api\V1\ModelsController;
use Illuminate\Support\Facades\Route;

// ==========================================
// OpenAI-compatible API endpoints
// ==========================================
Route::prefix('v1')->middleware(['proxy.auth', 'proxy.ratelimit'])->group(function () {
    // Chat completions (with streaming)
    Route::post('/chat/completions', [ChatController::class, 'completions']);

    // Embeddings
    Route::post('/embeddings', [EmbeddingsController::class, 'create']);

    // Image generation
    Route::post('/images/generations', [ImagesController::class, 'generate']);

    // Models list (public-ish)
    Route::get('/models', [ModelsController::class, 'index']);
    Route::get('/models/{model}', [ModelsController::class, 'show'])->where('model', '.*');
});

// Auth endpoint (Sanctum)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});