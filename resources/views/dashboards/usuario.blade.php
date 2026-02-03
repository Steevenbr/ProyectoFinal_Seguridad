<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Usuario
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Banner / Bienvenida --}}
            <div class="bg-gradient-to-r from-gray-900 to-gray-700 text-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-200">Bienvenido</p>
                        <h3 class="text-2xl font-bold">{{ auth()->user()->name }}</h3>
                        <p class="text-sm text-gray-200 mt-1">
                            Catálogo gamer
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <a href="#catalogo" class="px-4 py-2 rounded bg-white/10 hover:bg-white/20 transition">
                            Ver catálogo
                        </a>
                    </div>
                </div>
            </div>

            {{-- Alertas --}}
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

            {{-- Estadísticas rápidas --}}
            @php
                $total = $products->count();
                $inStock = $products->where('stock', '>', 0)->count();
                $minPrice = $products->min('price');
                $maxPrice = $products->max('price');
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Productos</p>
                    <p class="text-2xl font-bold mt-1">{{ $total }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Disponibles</p>
                    <p class="text-2xl font-bold mt-1">{{ $inStock }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Precio mínimo</p>
                    <p class="text-2xl font-bold mt-1">
                        {{ $minPrice !== null ? '$'.number_format($minPrice, 2) : '-' }}
                    </p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Precio máximo</p>
                    <p class="text-2xl font-bold mt-1">
                        {{ $maxPrice !== null ? '$'.number_format($maxPrice, 2) : '-' }}
                    </p>
                </div>
            </div>

            {{-- Barra de búsqueda (visual por ahora) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6" id="catalogo">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold">Catálogo</h3>
                        <p class="text-sm text-gray-500">Elige un producto y confirma la compra.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <input
                            type="text"
                            placeholder="Buscar producto (visual)"
                            class="w-full sm:w-80 rounded border-gray-300 focus:border-gray-800 focus:ring-gray-800"
                        >
                        <select class="w-full sm:w-52 rounded border-gray-300 focus:border-gray-800 focus:ring-gray-800">
                            <option>Todos</option>
                            <option>En stock</option>
                            <option>Sin stock</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Cards de productos --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($products as $p)
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="p-6 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">{{ $p->name }}</h4>
                                    <p class="text-sm text-gray-500">SKU: {{ $p->sku ?? '-' }}</p>
                                </div>

                                @if($p->stock > 0)
                                    <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-800">
                                        En stock ({{ $p->stock }})
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded bg-red-100 text-red-800">
                                        Sin stock
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-700">
                                {{ $p->specs ?? 'Sin especificaciones registradas.' }}
                            </p>

                            <div class="flex items-center justify-between pt-3 border-t">
                                <div>
                                    <p class="text-xs text-gray-500">Precio</p>
                                    <p class="text-xl font-bold">${{ number_format($p->price, 2) }}</p>
                                </div>

                                <form method="POST" action="{{ route('usuario.buy', $p) }}">
                                    @csrf
                                    <button
                                        class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-black transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        @disabled($p->stock <= 0)
                                        onclick="return confirm('¿Confirmar compra de {{ $p->name }} por ${{ number_format($p->price, 2) }}?')"
                                    >
                                        Comprar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow-sm sm:rounded-lg p-10 text-center text-gray-500 lg:col-span-3">
                        <p class="text-lg font-semibold text-gray-700">No hay productos registrados.</p>
                        <p class="text-sm mt-2">
                            Si ya insertaste productos en DBeaver y aquí no aparecen, casi seguro Laravel y DBeaver están usando archivos SQLite distintos.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
