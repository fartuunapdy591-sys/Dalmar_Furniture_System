<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function home()
    {
        $featured = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->latest()
            ->take(8)
            ->get();

        return view('shop.home', compact('featured'));
    }

    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $products = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->search, fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('shop.index', compact('categories', 'products'));
    }

    public function addToCart(Request $request, Product $product)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        if ($data['qty'] > $product->stock) {
            return back()->with('error', "Only {$product->stock} of {$product->name} available.");
        }

        $cart = session('cart', []);
        $currentQty = $cart[$product->id] ?? 0;
        $cart[$product->id] = min($currentQty + $data['qty'], $product->stock);
        session(['cart' => $cart]);

        return back()->with('success', "{$product->name} added to your order.");
    }

    public function removeFromCart(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Item removed.');
    }

    public function cart()
    {
        [$lines, $total] = $this->cartLines();

        return view('shop.cart', compact('lines', 'total'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,mobile_money,e_dahab'],
        ]);

        [$lines, $total] = $this->cartLines();

        if (empty($lines)) {
            return redirect()->route('shop.index')->with('error', 'Your order is empty. Please add at least one item.');
        }

        $order = DB::transaction(function () use ($data, $lines, $total) {
            $customer = Customer::firstOrCreate(
                ['phone' => $data['phone']],
                ['name' => $data['name'], 'address' => $data['address'], 'status' => 'active']
            );
            $customer->fill(['name' => $data['name'], 'address' => $data['address']])->save();

            $order = Order::create([
                'order_number' => 'ORD-'.now()->format('Y').'-'.str_pad((Order::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'user_id' => null,
                'order_date' => now()->toDateString(),
                'payment_method' => $data['payment_method'],
                'is_credit_sale' => false,
                'shipping_address' => $data['address'],
                'status' => 'pending',
                'source' => 'web',
                'total_amount' => 0,
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'price' => $line['product']->price,
                    'qty' => $line['qty'],
                    'total' => $line['subtotal'],
                ]);

                $line['product']->decrement('stock', $line['qty']);
            }

            $order->update(['total_amount' => $total, 'subtotal' => $total]);

            return $order;
        });

        session()->forget('cart');

        return redirect()->route('shop.confirmation', $order)->with('success', 'Your order has been placed.');
    }

    public function confirmation(Order $order)
    {
        $order->load('items.product', 'customer');

        return view('shop.confirmation', compact('order'));
    }

    public function about()
    {
        return view('shop.about');
    }

    public function contact()
    {
        return view('shop.contact');
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        ContactMessage::create($data);

        return back()->with('success', 'Thank you! Your message has been sent. We will get back to you soon.');
    }

    public function trackOrder()
    {
        return view('shop.track');
    }

    public function trackOrderResult(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $order = Order::with('items.product', 'customer')
            ->where('order_number', $data['order_number'])
            ->whereHas('customer', fn ($q) => $q->where('phone', $data['phone']))
            ->first();

        if (! $order) {
            return back()->withInput()->with('error', 'No order found with that order number and phone number.');
        }

        return view('shop.track', compact('order'));
    }

    private function cartLines(): array
    {
        $cart = session('cart', []);
        $lines = [];
        $total = 0;

        foreach ($cart as $productId => $qty) {
            $product = Product::find($productId);
            if (! $product) {
                continue;
            }
            $subtotal = $product->price * $qty;
            $total += $subtotal;
            $lines[] = ['product' => $product, 'qty' => $qty, 'subtotal' => $subtotal];
        }

        return [$lines, $total];
    }
}
