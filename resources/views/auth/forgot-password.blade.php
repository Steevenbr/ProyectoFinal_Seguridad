<x-guest-layout>
    <!-- Encabezado -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">Recuperar contraseña</h1>
        <p class="mt-1 text-sm text-gray-600">Te enviaremos un enlace de restablecimiento</p>
    </div>

    <div class="mb-4 text-sm text-gray-600">
        ¿Olvidaste tu contraseña? No te preocupes. Escribe tu correo electrónico y te enviaremos un enlace para restablecerla.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Correo -->
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="ejemplo@correo.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Enviar enlace de recuperación
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
