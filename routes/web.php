<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Controllers\TwoFactorSetupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| 2FA (solo requiere login)
|--------------------------------------------------------------------------
| - Setup: configurar 2FA (QR + activar/desactivar)
| - Challenge: pedir OTP antes de entrar a zonas protegidas
*/
Route::middleware('auth')->group(function () {
    // Setup
    Route::get('/2fa/setup', [TwoFactorSetupController::class, 'show'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorSetupController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [TwoFactorSetupController::class, 'disable'])->name('2fa.disable');

    // Challenge
    Route::get('/2fa/challenge', [TwoFactorChallengeController::class, 'show'])->name('2fa.challenge');
    Route::post('/2fa/verify', [TwoFactorChallengeController::class, 'verify'])->name('2fa.verify');
});

/*
|--------------------------------------------------------------------------
| Protected (requiere login + haber pasado 2FA si está activado)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', '2fa'])->group(function () {

    // Dashboards por rol
    Route::get('/dashboard/admin', function () {
        return view('dashboards.admin');
    })->middleware('role:admin')->name('dashboard.admin');

    Route::get('/dashboard/tesorero', function () {
        return view('dashboards.tesorero');
    })->middleware('role:tesorero')->name('dashboard.tesorero');

    Route::get('/dashboard/usuario', function () {
        return view('dashboards.usuario');
    })->middleware('role:usuario')->name('dashboard.usuario');

    // /dashboard redirige según rol
    Route::get('/dashboard', function () {
        $role = auth()->user()->role ?? 'usuario';

        return match ($role) {
            'admin'    => redirect()->route('dashboard.admin'),
            'tesorero' => redirect()->route('dashboard.tesorero'),
            default    => redirect()->route('dashboard.usuario'),
        };
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
