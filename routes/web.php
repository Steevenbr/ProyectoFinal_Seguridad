<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Controllers\TwoFactorSetupController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioDashboardController;
use App\Http\Controllers\TesoreroDashboardController;
use App\Http\Controllers\AdminDashboardController;

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
| OAuth 2.0 Google (solo invitados)
|--------------------------------------------------------------------------
| - /auth/google -> redirige a Google
| - /auth/google/callback -> vuelve a tu app
*/
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])
    ->middleware('guest')
    ->name('auth.google');

Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
    ->middleware('guest')
    ->name('auth.google.callback');

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
    Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin')
        ->name('dashboard.admin');

    Route::patch('/dashboard/admin/users/{user}/role', [AdminDashboardController::class, 'updateRole'])
        ->middleware('role:admin')
        ->name('admin.users.role');

    Route::delete('/dashboard/admin/users/{user}', [AdminDashboardController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('admin.users.destroy');

    Route::get('/dashboard/tesorero', [TesoreroDashboardController::class, 'index'])
    ->middleware('role:tesorero')
    ->name('dashboard.tesorero');

    Route::get('/dashboard/usuario', [UsuarioDashboardController::class, 'index'])
        ->middleware('role:usuario')
        ->name('dashboard.usuario');

    Route::post('/dashboard/usuario/comprar/{product}', [UsuarioDashboardController::class, 'buy'])
        ->middleware('role:usuario')
        ->name('usuario.buy');

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
