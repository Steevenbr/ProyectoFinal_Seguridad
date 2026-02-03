<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        // Google devuelve datos del usuario
        $googleUser = Socialite::driver('google')->user();

        $email = $googleUser->getEmail();
        $providerId = $googleUser->getId();
        $name = $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Usuario');

        // 1) Si ya existe un usuario con ese email, lo usamos
        $user = User::where('email', $email)->first();

        // 2) Si no existe, lo creamos
        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                // password random (porque entrará por Google)
                'password' => Hash::make(str()->random(32)),
                'oauth_provider' => 'google',
                'oauth_provider_id' => $providerId,
            ]);
        } else {
            // Si existe, guardamos proveedor si aún no está vinculado
            if (empty($user->oauth_provider) || empty($user->oauth_provider_id)) {
                $user->oauth_provider = 'google';
                $user->oauth_provider_id = $providerId;
                $user->save();
            }
        }

        // Login en Laravel
        Auth::login($user);

        // Integración con tu 2FA
        session(['2fa_passed' => false]);

        if (!empty($user->google2fa_enabled_at) && !empty($user->google2fa_secret)) {
            return redirect()->route('2fa.challenge');
        }

        session(['2fa_passed' => true]);
        return redirect()->intended(route('dashboard'));
    }
}
