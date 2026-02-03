<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioDashboardController;
use App\Http\Controllers\TesoreroDashboardController;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

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

    // /dashboard redirige según rol (para que no exista un dashboard único)
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
