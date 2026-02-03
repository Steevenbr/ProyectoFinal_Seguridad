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

    <div class="py-8">
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
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Gestión de usuarios</h3>
                    <p class="text-sm text-gray-500">Total: {{ $users->total() }}</p>
                </div>

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

        </div>
    </div>
</x-app-layout>
