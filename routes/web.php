<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TipsController;
use App\Http\Controllers\AlarmController;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

// Rotas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/recursos', [ResourceController::class, 'index'])->name('resources');
Route::get('/sobre', [AboutController::class, 'index'])->name('about');

// Rotas de autenticação (acessíveis apenas para visitantes)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    
    // Registro
    Route::get('/cadastro', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/cadastro', [RegisterController::class, 'register'])->name('register.submit');
});

// Rotas protegidas (acessíveis apenas para usuários autenticados)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/feedings', [DashboardController::class, 'storeFeeding'])->name('feedings.store');
    Route::get('/dashboard/feedings/recent', [DashboardController::class, 'getRecentFeedings'])->name('feedings.recent');
    Route::get('/dashboard/feedings/statistics/{babyId}', [DashboardController::class, 'getFeedingStatistics'])->name('feedings.statistics');
    Route::post('/dashboard/baby', [DashboardController::class, 'storeBaby'])->name('baby.store');
    Route::get('/dashboard/tips/daily', [TipsController::class, 'getDailyTips'])->name('tips.daily');
    
    // Rotas de notificações
    Route::post('/notifications/subscribe', [NotificationController::class, 'subscribe'])->name('notifications.subscribe');
    Route::post('/notifications/send', [NotificationController::class, 'sendNotification'])->name('notifications.send');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');

    // Rotas para alarmes
    Route::get('/dashboard/alarms', [AlarmController::class, 'index'])->name('alarms.index');
    Route::post('/dashboard/alarms', [AlarmController::class, 'store'])->name('alarms.store');
    Route::put('/dashboard/alarms/{alarm}', [AlarmController::class, 'update'])->name('alarms.update');
    Route::delete('/dashboard/alarms/{alarm}', [AlarmController::class, 'destroy'])->name('alarms.destroy');
});

// Rotas de Alarmes
Route::middleware(['auth'])->group(function () {
    Route::get('/babies/{baby}/alarms', [AlarmController::class, 'index'])->name('alarms.index');
    Route::post('/alarms/{alarm}/toggle', [AlarmController::class, 'toggle'])->name('alarms.toggle');
    Route::post('/alarms', [AlarmController::class, 'store'])->name('alarms.store');
    Route::put('/alarms/{alarm}', [AlarmController::class, 'update'])->name('alarms.update');
    Route::delete('/alarms/{alarm}', [AlarmController::class, 'destroy'])->name('alarms.destroy');
});

Route::get('/test-notification', function () {
    $user = Auth::user();
    $baby = $user->babies->first();
    
    if (!$baby) {
        return redirect()->route('dashboard')->with('error', 'Nenhum bebê encontrado.');
    }

    $baby->notify(new \Illuminate\Notifications\Notification([
        'title' => 'Teste',
        'message' => 'Esta é uma notificação de teste'
    ]));

    return redirect()->route('dashboard')->with('success', 'Notificação de teste criada!');
})->middleware(['auth'])->name('test.notification');