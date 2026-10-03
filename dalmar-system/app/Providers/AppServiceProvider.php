<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Share live notification data (low-stock products, pending orders)
        // with the main layout so the bell icon works on every page.
        View::composer('layouts.app', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $lowStockQuery = Product::where('status', 'active')
                ->where('stock', '>', 0)
                ->where('stock', '<=', 8);
            $lowStockProducts = (clone $lowStockQuery)->latest('updated_at')->take(5)->get();

            $pendingOrdersQuery = Order::where('status', 'pending');
            $pendingOrders = (clone $pendingOrdersQuery)->with('customer')->latest()->take(5)->get();
            $pendingOrdersCount = $pendingOrdersQuery->count();

            $view->with('notifLowStock', $lowStockProducts);
            $view->with('notifPendingOrders', $pendingOrders);
            $view->with('notifPendingOrdersCount', $pendingOrdersCount);
            $view->with('notifCount', $lowStockQuery->count() + $pendingOrdersCount);
        });
    }
}
