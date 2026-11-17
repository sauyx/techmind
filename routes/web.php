<?php

use App\Http\Controllers\Admin\CareerController;
use App\Http\Controllers\Admin\CriterionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeedbackExportController;
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

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/criteria', [CriterionController::class, 'index'])->name('criteria.index');
    Route::put('/criteria/{criterion}', [CriterionController::class, 'update'])->name('criteria.update');

    Route::get('/careers', [CareerController::class, 'index'])->name('careers.index');
    Route::get('/careers/create', [CareerController::class, 'create'])->name('careers.create');
    Route::post('/careers', [CareerController::class, 'store'])->name('careers.store');
    Route::get('/careers/{career}/edit', [CareerController::class, 'edit'])->name('careers.edit');
    Route::put('/careers/{career}', [CareerController::class, 'update'])->name('careers.update');

    Route::get('/feedbacks/export', [FeedbackExportController::class, 'export'])->name('feedbacks.export');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});
