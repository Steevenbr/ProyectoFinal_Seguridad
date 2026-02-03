<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Si no hay usuario logueado, sigue normal
        if (!$user) return $next($request);

        // Si el usuario NO tiene 2FA activado, sigue normal
        if (empty($user->google2fa_enabled_at) || empty($user->google2fa_secret)) {
            return $next($request);
        }

        // Si ya pasó el challenge 2FA en esta sesión, sigue normal
        if ($request->session()->get('2fa_passed') === true) {
            return $next($request);
        }

        // Evitar bucle: permitir entrar a estas rutas sin haber pasado 2FA
        if ($request->routeIs('2fa.challenge', '2fa.verify', '2fa.setup', '2fa.enable', '2fa.disable')) {
            return $next($request);
        }

        // Si tiene 2FA activo pero aún no pasó el challenge => mandarlo al challenge
        return redirect()->route('2fa.challenge');
    }
}
