<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            $ip = $request->ip();

            // clave por usuario+ip para evitar ataques distribuidos y también por ip
            $key = strtolower($email).'|'.$ip;

            // 5 intentos por minuto (ajústalo)
            return Limit::perMinute(5)->by($key);
        });

        parent::boot();
    }
}
