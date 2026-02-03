<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

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

    public function verify(Request $request)
    {
        $request->validate([
            'one_time_password' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (empty($user->google2fa_secret)) {
            return back()->withErrors(['one_time_password' => '2FA no está configurado.']);
        }

        $secret = Crypt::decryptString($user->google2fa_secret);

        $valid = app('pragmarx.google2fa')->verifyKey($secret, $request->one_time_password);

        if (!$valid) {
            return back()->withErrors(['one_time_password' => 'Código incorrecto.']);
        }

        // Marca la sesión como aprobada
        $request->session()->put('2fa_passed', true);

        return redirect()->intended(route('dashboard'));
    }
}
