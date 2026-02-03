<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TesoreroDashboardController extends Controller
{
    public function index()
    {
        // Detalle de compras con joins (usuario + producto)
        $purchases = DB::table('purchases')
            ->join('users', 'purchases.user_id', '=', 'users.id')
            ->join('products', 'purchases.product_id', '=', 'products.id')
            ->select(
                'purchases.id',
                'purchases.created_at',
                'users.name as buyer_name',
                'users.email as buyer_email',
                'products.name as product_name',
                'products.sku as product_sku',
                'purchases.quantity',
                'purchases.unit_price',
                'purchases.total'
            )
            ->orderByDesc('purchases.created_at')
            ->get();

        // Totales
        $totalSales = DB::table('purchases')->sum('total');
        $totalOrders = DB::table('purchases')->count();

        // Ventas del mes actual (según created_at)
        $monthSales = DB::table('purchases')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        // Resumen mensual (SQLite strftime)
        $monthly = DB::table('purchases')
            ->selectRaw("strftime('%Y-%m', created_at) as month, SUM(total) as total_sales, COUNT(*) as orders")
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(6)
            ->get();

        return view('dashboards.tesorero', compact(
            'purchases',
            'totalSales',
            'totalOrders',
            'monthSales',
            'monthly'
        ));
    }
}
