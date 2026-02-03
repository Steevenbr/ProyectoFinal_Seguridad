<x-guest-layout>
    <!-- Encabezado -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">Verifica tu correo</h1>
        <p class="mt-1 text-sm text-gray-600">Necesitamos confirmar tu dirección de email</p>
    </div>

    <div class="mb-4 text-sm text-gray-600">
        Gracias por registrarte. Antes de comenzar, por favor verifica tu correo electrónico haciendo clic en el enlace que te enviamos.
        Si no recibiste el correo, podemos enviarte otro.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Se ha enviado un nuevo enlace de verificación al correo que registraste.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Reenviar correo de verificación
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Cerrar sesión
            </button>
        </form>
    </div>
</x-guest-layout>
