<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDebtController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Dalmar Furniture and House Interior Management System
|--------------------------------------------------------------------------
*/

// The public marketing homepage. It always shows the storefront's front
// page — logged-in staff can still reach the dashboard from the sidebar
// or the "Dashboard" button that replaces "Login" in the site nav for them.
Route::get('/', [ShopController::class, 'home'])->name('home');

// Public storefront — customers can browse products and place an order
// without logging in. Orders land in the same Orders list staff already use,
// tagged with source "web" so they can be told apart from in-store sales.
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::post('/shop/cart/{product}', [ShopController::class, 'addToCart'])->name('shop.cart.add');
Route::delete('/shop/cart/{product}', [ShopController::class, 'removeFromCart'])->name('shop.cart.remove');
Route::get('/shop/cart', [ShopController::class, 'cart'])->name('shop.cart');
Route::post('/shop/checkout', [ShopController::class, 'checkout'])->name('shop.checkout');
Route::get('/shop/order/{order}', [ShopController::class, 'confirmation'])->name('shop.confirmation');
Route::get('/about', [ShopController::class, 'about'])->name('shop.about');
Route::get('/contact', [ShopController::class, 'contact'])->name('shop.contact');
Route::post('/contact', [ShopController::class, 'contactSubmit'])->name('shop.contact.submit');
Route::get('/track-order', [ShopController::class, 'trackOrder'])->name('shop.track');
Route::post('/track-order', [ShopController::class, 'trackOrderResult'])->name('shop.track.result');

// Guest routes (login only — accounts are created by an administrator
// from the Users page, not via public self-registration)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // A Salesperson may still add a walk-in customer inline from POS/Orders,
    // even though the full customer list is a finance/management screen.
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');

    // Inventory and customer records are for Admin and Sales Manager.
    // Salesperson works from POS/Orders, and Accountant works from
    // Payments and Reports, so neither manages these lists directly.
    Route::middleware('role:admin,sales_manager')->group(function () {
        Route::resource('products', ProductController::class)->except(['create']);
        Route::post('/products/{product}/restock', [ProductController::class, 'restock'])->name('products.restock');
        Route::post('/products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
        Route::resource('categories', CategoryController::class)->except(['create']);
        Route::resource('customers', CustomerController::class)->except(['create', 'store']);

        Route::get('/messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::patch('/messages/{message}/read', [ContactMessageController::class, 'markRead'])->name('messages.read');
        Route::delete('/messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');
    });

    Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/receipts/{receipt}/print', [ReceiptController::class, 'printReceipt'])->name('receipts.print');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

    Route::get('/customer-debts', [CustomerDebtController::class, 'index'])->name('customer-debts.index');
    Route::post('/customer-debts/{customer}/pay', [CustomerDebtController::class, 'pay'])->name('customer-debts.pay');

    // Sales duties: making sales is for Admin, Sales Manager, and Salesperson.
    // Accountants track money but do not create orders or run the till.
    Route::middleware('role:admin,sales_manager,salesperson')->group(function () {
        Route::resource('orders', OrderController::class)->except(['create', 'edit', 'update']);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    });

    // Procurement duties: suppliers, purchases, and expenses are for Admin
    // only. Accountant's job here is Payments and Reports, not procurement.
    Route::middleware('role:admin')->group(function () {
        Route::resource('suppliers', SupplierController::class)->except(['create']);

        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

        Route::resource('expenses', ExpenseController::class)->except(['create']);
    });

    // Reports stay available to Admin, Sales Manager, and Accountant.
    Route::middleware('role:admin,sales_manager,accountant')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['create']);
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
    });
});
