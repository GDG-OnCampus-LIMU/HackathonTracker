<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamPortalController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('team.login');
});

// Team Portal Routes
Route::prefix('team')->group(function () {
    Route::get('/login', [TeamPortalController::class, 'showLogin'])->name('team.login');
    Route::post('/login', [TeamPortalController::class, 'login'])->name('team.login.submit');
    Route::get('/dashboard', [TeamPortalController::class, 'dashboard'])->name('team.dashboard');
    Route::post('/submit', [TeamPortalController::class, 'submitSolution'])->name('team.submit');
    Route::get('/logout', [TeamPortalController::class, 'logout'])->name('team.logout');
});

// Admin Routes 
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});