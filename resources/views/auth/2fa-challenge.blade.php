<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">Verificación 2FA</h1>
        <p class="mt-1 text-sm text-gray-600">Ingresa el código de 6 dígitos de Google Authenticator</p>
    </div>

    <form method="POST" action="{{ route('2fa.verify') }}">
        @csrf

        <div>
            <x-input-label for="one_time_password" value="Código (OTP)" />
            <x-text-input id="one_time_password" name="one_time_password" type="text"
                class="block mt-1 w-full" required autofocus placeholder="123456" />
            <x-input-error :messages="$errors->get('one_time_password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>Verificar</x-primary-button>
        </div>
    </form>
</x-guest-layout>
