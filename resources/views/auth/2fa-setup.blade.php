<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configurar 2FA (Google Authenticator)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('status'))
                    <div class="mb-4 text-green-700 bg-green-100 p-3 rounded">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($isEnabled)
                    <p class="mb-4 text-sm text-gray-700">
                        ✅ 2FA está <b>ACTIVADO</b> en tu cuenta.
                    </p>

                    <form method="POST" action="{{ route('2fa.disable') }}">
                        @csrf
                        <x-danger-button>Desactivar 2FA</x-danger-button>
                    </form>
                @else
                    <p class="mb-4 text-sm text-gray-700">
                        Escanea este QR con Google Authenticator y luego ingresa el código de 6 dígitos para activar.
                    </p>

                    <div class="mb-4">
                        {!! $qr !!}
                    </div>

                    <form method="POST" action="{{ route('2fa.enable') }}">
                        @csrf

                        <div>
                            <x-input-label for="one_time_password" value="Código (OTP)" />
                            <x-text-input id="one_time_password" name="one_time_password" type="text" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('one_time_password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-primary-button>Activar 2FA</x-primary-button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
