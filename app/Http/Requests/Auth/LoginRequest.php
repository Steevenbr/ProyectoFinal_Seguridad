<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private int $maxAttempts = 5;
    private int $strikeTtlSeconds = 60 * 60 * 24 * 30; // 30 días

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotLocked();

        $credentials = $this->only('email', 'password');

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            $this->handleFailedAttempt();

            // Si handleFailedAttempt no lanzó error por bloqueo, lanzamos auth.failed normal
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $this->clearLockState();
    }

    private function ensureIsNotLocked(): void
    {
        $lockUntil = Cache::get($this->lockUntilKey());

        if ($lockUntil && now()->lessThan($lockUntil)) {
            event(new Lockout($this));

            $secondsLeft = now()->diffInSeconds($lockUntil);

            throw ValidationException::withMessages([
                'email' => "Demasiados intentos. Intenta nuevamente en {$this->humanTime($secondsLeft)}.",
            ]);
        }
    }

    private function handleFailedAttempt(): void
    {
        // ✅ Contador SIEMPRE funcional (get + put) en cualquier driver de cache
        $fails = (int) Cache::get($this->failsKey(), 0);
        $fails++;
        Cache::put($this->failsKey(), $fails, $this->strikeTtlSeconds);

        // Aún no llega al máximo -> muestra contador correcto
        if ($fails < $this->maxAttempts) {
            $remaining = max(0, $this->maxAttempts - $fails); // 1er fallo => 4

            throw ValidationException::withMessages([
                'email' => "Credenciales incorrectas. Te quedan {$remaining} intento(s) antes del bloqueo.",
            ]);
        }

        // Llegó al máximo -> strike y bloqueo progresivo
        $strikes = (int) Cache::get($this->strikesKey(), 0);
        $strikes++;
        Cache::put($this->strikesKey(), $strikes, $this->strikeTtlSeconds);

        $lockSeconds = $this->lockSecondsForStrike($strikes);
        $lockUntil = now()->addSeconds($lockSeconds);

        Cache::put($this->lockUntilKey(), $lockUntil, $lockSeconds);

        // Resetea los 5 intentos para el siguiente ciclo (cuando pase el castigo)
        Cache::forget($this->failsKey());

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'email' => "Cuenta bloqueada por {$this->humanTime($lockSeconds)} debido a demasiados intentos fallidos.",
        ]);
    }

    private function lockSecondsForStrike(int $strike): int
    {
        $map = [
            1 => 60,
            2 => 5 * 60,
            3 => 10 * 60,
            4 => 60 * 60,
            5 => 12 * 60 * 60,
            6 => 24 * 60 * 60,
        ];

        if (isset($map[$strike])) {
            return $map[$strike];
        }

        // 7+ → duplica desde 24h con tope de 7 días
        $base = 24 * 60 * 60;
        $multiplier = 2 ** ($strike - 6);
        $seconds = $base * $multiplier;

        $max = 7 * 24 * 60 * 60;
        return min($seconds, $max);
    }

    private function clearLockState(): void
    {
        Cache::forget($this->failsKey());
        Cache::forget($this->strikesKey());
        Cache::forget($this->lockUntilKey());
    }

    private function throttleId(): string
    {
        return Str::lower($this->input('email')) . '|' . $this->ip();
    }

    private function failsKey(): string
    {
        return 'login:fails:' . $this->throttleId();
    }

    private function strikesKey(): string
    {
        return 'login:strikes:' . $this->throttleId();
    }

    private function lockUntilKey(): string
    {
        return 'login:lock_until:' . $this->throttleId();
    }

    private function humanTime(int $seconds): string
    {
        if ($seconds < 60) return $seconds . 's';
        if ($seconds < 3600) return ceil($seconds / 60) . ' min';
        if ($seconds < 86400) return ceil($seconds / 3600) . ' h';
        return ceil($seconds / 86400) . ' d';
    }
}
