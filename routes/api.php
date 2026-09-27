<?php

use App\Http\Controllers\AiModelController;
use App\Http\Controllers\BenchmarkController;
use Illuminate\Support\Facades\Route;

Route::prefix('models')->group(function () {
    Route::post('/fetch', [AiModelController::class, 'fetchModels']);
});

Route::prefix('endpoints')->group(function () {
    Route::get('/', [AiModelController::class, 'getSavedEndpoints']);
    Route::post('/', [AiModelController::class, 'saveEndpoint']);
    Route::delete('/{savedEndpoint}', [AiModelController::class, 'deleteEndpoint']);
});

Route::prefix('benchmark')->group(function () {
    Route::post('/run', [BenchmarkController::class, 'runBenchmark']);
    Route::post('/stream', [BenchmarkController::class, 'streamPrompt']);
    Route::get('/suites', [BenchmarkController::class, 'getStrengthSuites']);
    Route::get('/history', [BenchmarkController::class, 'getHistory']);
    Route::delete('/history', [BenchmarkController::class, 'clearHistory']);
    Route::delete('/history/{benchmarkRun}', [BenchmarkController::class, 'deleteRun']);
});
