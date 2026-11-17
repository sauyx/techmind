<?php

use App\Http\Controllers\Public\ResultController;
use App\Http\Controllers\Public\TestController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::post('/tests', [TestController::class, 'store'])
    ->name('tests.store')
    ->middleware('throttle:10,1');

Route::get('/tests/{token}', [TestController::class, 'show'])
    ->name('tests.show');

Route::post('/tests/{token}/answers', [TestController::class, 'submit'])
    ->name('tests.submit')
    ->middleware('throttle:10,1');

Route::get('/results/{token}', [ResultController::class, 'show'])
    ->name('results.show');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::inertia('admin', 'admin/dashboard')->name('admin.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});
