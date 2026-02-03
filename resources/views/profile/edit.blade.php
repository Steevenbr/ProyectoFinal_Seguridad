<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- ✅ NUEVO BLOQUE: 2FA EN PROFILE --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl space-y-2">
                    <h2 class="text-lg font-medium text-gray-900">Seguridad (2FA)</h2>
                    <p class="text-sm text-gray-600">
                        Activa la autenticación en dos pasos con Google Authenticator.
                    </p>

                    <x-primary-button
                        x-data=""
                        x-on:click.prevent="$dispatch('open-modal', 'twofa-setup')"
                    >
                        Configurar 2FA
                    </x-primary-button>
                </div>
            </div>

            {{-- MODAL --}}
            <x-modal name="twofa-setup" focusable>
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900">Configurar 2FA</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Escanea el QR y escribe el código de 6 dígitos para activar.
                    </p>

                    <div class="mt-4">
                        <iframe
                            src="{{ url('/2fa/setup?embed=1') }}"
                            class="w-full border rounded"
                            style="height: 540px;"
                        ></iframe>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close-modal', 'twofa-setup')">
                            Cerrar
                        </x-secondary-button>
                    </div>
                </div>
            </x-modal>
            {{-- ✅ FIN 2FA --}}

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
