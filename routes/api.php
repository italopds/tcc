<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlarmController;

// Rotas de Alarmes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/babies/{baby}/alarms', [AlarmController::class, 'index']);
    Route::post('/alarms/{alarm}/toggle', [AlarmController::class, 'toggle']);
    Route::post('/alarms', [AlarmController::class, 'store']);
    Route::put('/alarms/{alarm}', [AlarmController::class, 'update']);
    Route::delete('/alarms/{alarm}', [AlarmController::class, 'destroy']);
}); 