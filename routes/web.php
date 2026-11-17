<?php

use App\Http\Controllers\Public\ResultController;
use App\Http\Controllers\Public\TestController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('/tes', [TestController::class, 'create'])
    ->name('tests.create');

Route::post('/tes', [TestController::class, 'store'])
    ->name('tests.store')
    ->middleware('throttle:30,1');

Route::get('/hasil/{token}', [ResultController::class, 'show'])
    ->name('results.show');

Route::get('/hasil/{token}/pdf', [ResultController::class, 'downloadPdf'])
    ->name('results.pdf')
    ->middleware('throttle:20,1');

Route::post('/hasil/{token}/feedback', [ResultController::class, 'storeFeedback'])
    ->name('results.feedback')
    ->middleware('throttle:20,1');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::inertia('admin', 'admin/dashboard')->name('admin.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});
