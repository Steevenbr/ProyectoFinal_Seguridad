<x-guest-layout>
    <!-- Encabezado -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">Confirmar contraseña</h1>
        <p class="mt-1 text-sm text-gray-600">Acceso a zona protegida</p>
    </div>

    <div class="mb-4 text-sm text-gray-600">
        Esta es un área segura de la aplicación. Por favor confirma tu contraseña para continuar.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Contraseña -->
        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                Confirmar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
