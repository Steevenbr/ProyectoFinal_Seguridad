<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Tesorero
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Total ganado</p>
                    <p class="text-2xl font-bold mt-1">${{ number_format($totalSales ?? 0, 2) }}</p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Compras registradas</p>
                    <p class="text-2xl font-bold mt-1">{{ $totalOrders ?? 0 }}</p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Total del mes</p>
                    <p class="text-2xl font-bold mt-1">${{ number_format($monthSales ?? 0, 2) }}</p>
                </div>
            </div>

            {{-- Resumen mensual --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-3">Resumen mensual (últimos 6)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="text-left p-3">Mes</th>
                                <th class="text-right p-3">Ventas</th>
                                <th class="text-right p-3">Órdenes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($monthly as $m)
                                <tr>
                                    <td class="p-3">{{ $m->month }}</td>
                                    <td class="p-3 text-right">${{ number_format($m->total_sales, 2) }}</td>
                                    <td class="p-3 text-right">{{ $m->orders }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-4 text-center text-gray-500">
                                        Sin ventas aún.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Detalle --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-3">Detalle de compras</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="text-left p-3">Fecha</th>
                                <th class="text-left p-3">Comprador</th>
                                <th class="text-left p-3">Producto</th>
                                <th class="text-right p-3">Cant.</th>
                                <th class="text-right p-3">P. Unit</th>
                                <th class="text-right p-3">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($purchases as $p)
                                <tr>
                                    <td class="p-3 text-gray-600">
                                        {{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="p-3">
                                        <div class="font-medium">{{ $p->buyer_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $p->buyer_email }}</div>
                                    </td>
                                    <td class="p-3">
                                        <div class="font-medium">{{ $p->product_name }}</div>
                                        <div class="text-xs text-gray-500">SKU: {{ $p->product_sku ?? '-' }}</div>
                                    </td>
                                    <td class="p-3 text-right">{{ $p->quantity }}</td>
                                    <td class="p-3 text-right">${{ number_format($p->unit_price, 2) }}</td>
                                    <td class="p-3 text-right font-semibold">${{ number_format($p->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-gray-500">
                                        Aún no hay compras registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
