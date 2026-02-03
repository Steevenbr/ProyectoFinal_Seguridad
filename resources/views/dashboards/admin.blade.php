{{-- resources/views/dashboards/admin.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Administrador
            </h2>

            <form method="GET" action="{{ route('dashboard.admin') }}" class="flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Buscar (nombre, email, rol)"
                    class="w-72 rounded border-gray-300 focus:border-gray-800 focus:ring-gray-800"
                />
                <button class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-black transition">
                    Buscar
                </button>
            </form>
        </div>
    </x-slot>

    {{-- Evita “flash” del modal antes de que cargue Alpine --}}
    <style>[x-cloak]{ display:none !important; }</style>

    @php
        // Solo reabrir el modal si el POST de "crear usuario" falló (no por errores de cambiar rol)
        $openCreateModal = old('_create_user') == '1' && $errors->hasAny(['name','email','role','password','password_confirmation']);
    @endphp

    <div
        class="py-8"
        x-data="{ openCreateUser: {{ $openCreateModal ? 'true' : 'false' }} }"
        @keydown.escape.window="openCreateUser = false"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                {{-- Header del bloque: título + total --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Gestión de usuarios</h3>
                        <p class="text-sm text-gray-500">Total: {{ $users->total() }}</p>
                    </div>

                    {{-- Botón debajo del título --}}
                    <div class="mt-3">
                        <button
                            type="button"
                            class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-black transition"
                            @click="openCreateUser = true"
                        >
                            + Nuevo usuario
                        </button>
                    </div>
                </div>

                {{-- Tabla usuarios (TU DISEÑO ORIGINAL) --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="text-left p-3">ID</th>
                                <th class="text-left p-3">Nombre</th>
                                <th class="text-left p-3">Email</th>
                                <th class="text-left p-3">Rol</th>
                                <th class="text-left p-3">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($users as $u)
                                <tr>
                                    <td class="p-3 text-gray-600">{{ $u->id }}</td>
                                    <td class="p-3 font-medium">{{ $u->name }}</td>
                                    <td class="p-3 text-gray-600">{{ $u->email }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 rounded text-xs
                                            @if($u->role === 'admin') bg-purple-100 text-purple-800
                                            @elseif($u->role === 'tesorero') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif
                                        ">
                                            {{ $u->role }}
                                        </span>
                                    </td>

                                    <td class="p-3">
                                        <div class="flex flex-col sm:flex-row gap-2">

                                            {{-- Cambiar rol --}}
                                            <form method="POST" action="{{ route('admin.users.role', $u) }}" class="flex gap-2 items-center">
                                                @csrf
                                                @method('PATCH')

                                                <select name="role" class="rounded border-gray-300 text-sm">
                                                    <option value="usuario" @selected($u->role === 'usuario')>usuario</option>
                                                    <option value="tesorero" @selected($u->role === 'tesorero')>tesorero</option>
                                                    <option value="admin" @selected($u->role === 'admin')>admin</option>
                                                </select>

                                                <button
                                                    class="px-3 py-2 rounded bg-gray-900 text-white hover:bg-black transition"
                                                    onclick="return confirm('¿Cambiar rol de {{ $u->name }}?')"
                                                >
                                                    Guardar
                                                </button>
                                            </form>

                                            {{-- Eliminar --}}
                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition"
                                                    onclick="return confirm('¿Eliminar a {{ $u->name }}?')"
                                                >
                                                    Eliminar
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-500">
                                        No hay usuarios registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>

            {{-- MODAL CREAR USUARIO --}}
            <div
                x-cloak
                x-show="openCreateUser"
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center"
                aria-modal="true"
                role="dialog"
            >
                {{-- overlay --}}
                <div class="absolute inset-0 bg-black/50" @click="openCreateUser = false"></div>

                {{-- caja modal --}}
                <div
                    class="relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg"
                    @click.stop
                >
                    <div class="flex items-center justify-between px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold">Crear nuevo usuario</h3>

                        {{-- X: IMPORTANTE type="button" para que NO haga submit --}}
                        <button
                            type="button"
                            class="text-gray-500 hover:text-gray-800 text-xl leading-none"
                            @click="openCreateUser = false"
                            aria-label="Cerrar"
                        >
                            ×
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.users.store') }}" class="p-6">
                        @csrf

                        {{-- marca para reabrir modal si falla esta validación --}}
                        <input type="hidden" name="_create_user" value="1">

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="mt-1 w-full rounded border-gray-300 focus:border-gray-800 focus:ring-gray-800"
                                >
                                @error('name')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="mt-1 w-full rounded border-gray-300 focus:border-gray-800 focus:ring-gray-800"
                                >
                                @error('email')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Rol</label>
                                <select
                                    name="role"
                                    class="mt-1 w-full rounded border-gray-300 focus:border-gray-800 focus:ring-gray-800"
                                >
                                    <option value="usuario" @selected(old('role', 'usuario') === 'usuario')>usuario</option>
                                    <option value="tesorero" @selected(old('role') === 'tesorero')>tesorero</option>
                                    <option value="admin" @selected(old('role') === 'admin')>admin</option>
                                </select>
                                @error('role')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                                <input
                                    type="password"
                                    name="password"
                                    class="mt-1 w-full rounded border-gray-300 focus:border-gray-800 focus:ring-gray-800"
                                >
                                @error('password')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    class="mt-1 w-full rounded border-gray-300 focus:border-gray-800 focus:ring-gray-800"
                                >
                                @error('password_confirmation')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            {{-- Cancelar: type="button" para NO enviar --}}
                            <button
                                type="button"
                                class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-50 transition"
                                @click="openCreateUser = false"
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-black transition"
                            >
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- FIN MODAL --}}

        </div>
    </div>
</x-app-layout>
