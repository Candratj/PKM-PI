<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SecurityController;

// Test routes
Route::get('/test-connection', [SecurityController::class, 'testConnection']);
Route::post('/test-connection', [SecurityController::class, 'testConnection']);

// PIR Detection routes - support both GET and POST
Route::get('/pir-detections', [SecurityController::class, 'pirDetections']);
Route::post('/pir-detections', [SecurityController::class, 'pirDetections']);

// Main API endpoints for ESP32
Route::post('/motion-alert', [SecurityController::class, 'motionAlert']);
Route::post('/alarm-status', [SecurityController::class, 'alarmStatus']);
Route::post('/upload-photo', [SecurityController::class, 'uploadPhoto']);
Route::post('/device-status', [SecurityController::class, 'deviceStatus']);

// Dashboard stats
Route::get('/dashboard-stats', [SecurityController::class, 'getDashboardStats']);

// Alternative routes for compatibility
Route::any('/motion-detection', [SecurityController::class, 'motionAlert']);
Route::any('/pir-sensor', [SecurityController::class, 'pirDetections']);
Route::get('/camera-streams', [SecurityController::class, 'getCameraStreams']);