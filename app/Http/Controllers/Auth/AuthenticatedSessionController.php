<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Por defecto: no aprobado
        $request->session()->put('2fa_passed', false);

        $user = $request->user();

        // Si el usuario tiene 2FA activado, obligar challenge
        if (!empty($user->google2fa_enabled_at) && !empty($user->google2fa_secret)) {
            return redirect()->route('2fa.challenge');
        }

        // Si no tiene 2FA, aprobado directo
        $request->session()->put('2fa_passed', true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // 👇 importante: borrar el estado de 2FA en sesión
        $request->session()->forget('2fa_passed');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
