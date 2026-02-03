<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Control de Accesos</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="p-6 text-right">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Iniciar sesión
                </a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="ml-2 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Registrarse
                    </a>
                @endif
            @endauth
        </div>

        <main class="flex items-center justify-center py-16">
            <div class="max-w-4xl w-full mx-auto px-6">
                <div class="bg-white shadow-sm rounded-lg p-8">
                    <h1 class="text-3xl font-bold text-gray-800">Sistema de Control de Accesos</h1>
                    <p class="mt-2 text-gray-600">
                        Aplicación web segura con control de roles, autenticación y monitoreo.
                    </p>

                    <div class="mt-6 flex gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center px-5 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-500">
                                Ir al Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center px-5 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-500">
                                Iniciar sesión
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center px-5 py-2 bg-white border border-gray-300 text-gray-700 rounded-md font-semibold hover:bg-gray-50">
                                    Crear cuenta
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
