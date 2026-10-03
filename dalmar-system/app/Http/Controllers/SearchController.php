<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->get('q'));

        $products = collect();
        $customers = collect();
        $orders = collect();

        if ($query !== '') {
            $products = Product::with('category')
                ->where('name', 'like', "%{$query}%")
                ->limit(8)->get();

            $customers = Customer::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->limit(8)->get();

            $orders = Order::with('customer')
                ->where('order_number', 'like', "%{$query}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$query}%"))
                ->limit(8)->get();
        }

        return view('search.index', compact('query', 'products', 'customers', 'orders'));
    }
}
