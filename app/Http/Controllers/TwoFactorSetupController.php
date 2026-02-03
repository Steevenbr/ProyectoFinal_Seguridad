<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class TwoFactorSetupController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // Si ya está activado, solo muestra estado
        $isEnabled = !empty($user->google2fa_enabled_at) && !empty($user->google2fa_secret);

        // Si no hay secret aún, lo generamos para mostrar QR (pero NO lo activamos todavía)
        if (!$isEnabled && empty($user->google2fa_secret)) {
            $secret = app('pragmarx.google2fa')->generateSecretKey();
            $user->google2fa_secret = Crypt::encryptString($secret);
            $user->save();
        } else {
            $secret = $isEnabled
                ? Crypt::decryptString($user->google2fa_secret)
                : Crypt::decryptString($user->google2fa_secret);
        }

        $company = config('app.name', 'Laravel');
        $email = $user->email;

        // Generar QR inline (data URI) para mostrar en la vista
        $qr = app('pragmarx.google2fa')->getQRCodeInline($company, $email, $secret);

        return view('auth.2fa-setup', [
            'isEnabled' => $isEnabled,
            'qr' => $qr,
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'one_time_password' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (empty($user->google2fa_secret)) {
            return back()->withErrors(['one_time_password' => 'No hay secret generado. Recarga la página.']);
        }

        $secret = Crypt::decryptString($user->google2fa_secret);

        $valid = app('pragmarx.google2fa')->verifyKey($secret, $request->one_time_password);

        if (!$valid) {
            return back()->withErrors(['one_time_password' => 'Código incorrecto. Intenta de nuevo.']);
        }

        $user->google2fa_enabled_at = now();
        $user->save();

        // Para no tener líos luego, dejamos marcada la sesión como aprobada
        $request->session()->put('2fa_passed', true);

        return redirect()->route('2fa.setup')->with('status', '2FA activado correctamente.');
    }

    public function disable(Request $request)
    {
        $user = $request->user();

        // Desactivar: borra enabled_at (y opcionalmente el secret)
        $user->google2fa_enabled_at = null;
        $user->google2fa_secret = null;
        $user->save();

        $request->session()->forget('2fa_passed');

        return redirect()->route('2fa.setup')->with('status', '2FA desactivado.');
    }
}
