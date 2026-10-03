<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        $totalOrders = Order::count();
        $totalSales = Order::where('status', '!=', 'cancelled')->sum('total_amount');

        $recentOrders = Order::with('customer')
            ->latest('order_date')
            ->take(5)
            ->get();

        // Sales overview for the last 6 months, used to feed the line chart.
        $salesOverview = Order::where('status', '!=', 'cancelled')
            ->selectRaw("DATE_FORMAT(order_date, '%b') as month, SUM(total_amount) as total")
            ->groupBy('month')
            ->orderBy(DB::raw('MIN(order_date)'))
            ->take(6)
            ->pluck('total', 'month');

        // Sales split by category, used to feed the donut chart.
        $salesByCategory = Category::withCount('products')->get()
            ->pluck('products_count', 'name');

        return view('dashboard.index', compact(
            'totalProducts', 'totalCustomers', 'totalOrders', 'totalSales',
            'recentOrders', 'salesOverview', 'salesByCategory'
        ));
    }
}
