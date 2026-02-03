<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

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
