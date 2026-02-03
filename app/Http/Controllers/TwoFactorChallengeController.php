<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Services\AuditChain;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // Si no tiene 2FA activo, no debería estar aquí
        if (empty($user->google2fa_enabled_at) || empty($user->google2fa_secret)) {
            $request->session()->put('2fa_passed', true);
            return redirect()->route('dashboard');
        }

        return view('auth.2fa-challenge');
    }

    public function verify(Request $request, AuditChain $chain)
    {
        $request->validate([
            'one_time_password' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (empty($user->google2fa_secret)) {
            // Log de seguridad
            Log::channel('security')->warning('mfa_failed', [
                'ip' => $request->ip(),
                'user_id' => $user?->id,
                'reason' => 'secret_missing',
                'ua' => substr((string) $request->userAgent(), 0, 255),
            ]);

            return back()->withErrors(['one_time_password' => '2FA no está configurado.']);
        }

        $secret = Crypt::decryptString($user->google2fa_secret);

        $valid = app('pragmarx.google2fa')->verifyKey($secret, $request->one_time_password);

        if (!$valid) {
            // Log de seguridad (para Fail2ban / SIEM)
            Log::channel('security')->warning('mfa_failed', [
                'ip' => $request->ip(),
                'user_id' => $user->id,
                'reason' => 'invalid_otp',
                'ua' => substr((string) $request->userAgent(), 0, 255),
            ]);

            return back()->withErrors(['one_time_password' => 'Código incorrecto.']);
        }

        // Marca la sesión como aprobada
        $request->session()->put('2fa_passed', true);

        // Seguridad: regenerar sesión después de completar 2FA
        $request->session()->regenerate();

        // (Opcional y recomendado) Auditoría en tu hash-chain
        $chain->add(
            $user->id,
            'mfa_verified',
            ['user_id' => $user->id],
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->intended(route('dashboard'));
    }
}
