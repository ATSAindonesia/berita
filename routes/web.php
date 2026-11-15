<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsSummaryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return app(AuthController::class)->showLoginForm();
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// News Summary generation routes
Route::get('/summaries/create', [NewsSummaryController::class, 'create'])->name('summaries.create');
Route::post('/summaries', [NewsSummaryController::class, 'store'])->name('summaries.store');
Route::get('/summaries/{summary}', [NewsSummaryController::class, 'show'])->name('summaries.show');
Route::post('/summaries/{summary}/follow-up', [NewsSummaryController::class, 'followUp'])->name('summaries.follow_up');
Route::get('/summaries/status/{sessionId}', [NewsSummaryController::class, 'status'])->name('summaries.status');
Route::get('/summaries/status/{sessionId}/stream', [NewsSummaryController::class, 'stream'])->name('summaries.stream');
Route::post('/summaries/status/{sessionId}/cancel', [NewsSummaryController::class, 'cancel'])->name('summaries.cancel');
