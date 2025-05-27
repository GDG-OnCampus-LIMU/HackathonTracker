<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProgressController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public endpoint for TouchDesigner
Route::get('/progress', [ProgressController::class, 'getProgress']);

// Team endpoints (authenticated with token)
Route::prefix('team')->group(function () {
    Route::post('/submit', [ProgressController::class, 'submitSolution']);
    Route::get('/challenges', [ProgressController::class, 'getChallenges']);
});

// Admin endpoints (add authentication middleware in production)
Route::prefix('admin')->group(function () {
    Route::post('/update-progress', [ProgressController::class, 'updateProgress']);
});