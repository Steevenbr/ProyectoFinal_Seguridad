<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class UsuarioDashboardController extends Controller
{
    public function index()
    {
        $products = Product::get();
        return view('dashboards.usuario', compact('products'));
    }

    public function buy(Product $product)
    {
        if ($product->stock <= 0) {
            return back()->with('error', 'Producto sin stock.');
        }

        DB::transaction(function () use ($product) {
            Purchase::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => $product->price,
                'total' => $product->price,
            ]);

            $product->decrement('stock', 1);
        });

        return back()->with('success', 'Compra registrada (simulación).');
    }
}
