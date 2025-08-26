<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DialogController;
use App\Http\Controllers\MessageController;


Route::post('/auth', [AuthController::class, 'auth'])->name('auth');
Route::post('/auth/confirm', [AuthController::class, 'confirmAuth'])->name('auth.confirm');
Route::get('/auth/status', [AuthController::class, 'checkAuth'])->name('auth.status');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::post('/auth/cleanup', [AuthController::class, 'cleanup'])->name('auth.cleanup');


Route::middleware('auth.telegram')->group(function () {
    // Диалоги
    Route::get('/dialogs', [DialogController::class, 'index'])->name('dialogs.index');

    Route::get('/dialogs/{dialogId}/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/dialogs/{dialogId}/send', [MessageController::class, 'store'])->name('messages.send');
    Route::get('/dialogs/{dialogId}/messages/{messageId}', [MessageController::class, 'show'])->name('messages.show');
});


Route::get('/test', function () {
    return response()->json(['message' => 'Telegram API is working!']);
})->name('telegram.test');
