<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/test', [DashboardController::class, 'test'])->name('test');
Route::get('/motion-history', [DashboardController::class, 'motionHistory'])->name('motion.history');
Route::get('/alarm-history', [DashboardController::class, 'alarmHistory'])->name('alarm.history');
Route::get('/image-gallery', [DashboardController::class, 'imageGallery'])->name('image.gallery');
Route::get('/live-camera', [DashboardController::class, 'liveCamera'])->name('live.camera');

Route::post('/motion/{id}/read', [DashboardController::class, 'markAsRead'])->name('motion.read');
Route::delete('/motion/{id}', [DashboardController::class, 'deleteMotion'])->name('motion.delete');
Route::delete('/alarm/{id}', [DashboardController::class, 'deleteAlarm'])->name('alarm.delete');
Route::delete('/image/{id}', [DashboardController::class, 'deleteImage'])->name('image.delete');