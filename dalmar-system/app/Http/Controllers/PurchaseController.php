<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::with('supplier')
            ->when($request->supplier_id, fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('purchase_number', 'like', "%{$request->search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$request->search}%"));
            })
            ->when($request->from, fn ($q) => $q->whereDate('purchase_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('purchase_date', '<=', $request->to))
            ->latest('purchase_date')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required_with:amount_paid', 'nullable', 'in:cash,bank,mobile_money,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $discount = $data['discount'] ?? 0;
        $tax = $data['tax'] ?? 0;
        $amountPaid = $data['amount_paid'] ?? 0;

        $purchase = DB::transaction(function () use ($data, $discount, $tax, $amountPaid) {
            $subtotal = 0;
            foreach ($data['items'] as $line) {
                $subtotal += ($line['qty'] * $line['unit_cost']) - ($line['discount'] ?? 0);
            }
            $total = max(0, $subtotal - $discount + $tax);

            if ($amountPaid > $total) {
                abort(422, 'Amount paid cannot exceed the purchase total of $'.number_format($total, 2).'.');
            }

            $purchase = Purchase::create([
                'purchase_number' => 'PUR-'.now()->format('Y').'-'.str_pad((Purchase::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'supplier_id' => $data['supplier_id'],
                'user_id' => Auth::id(),
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                $product = Product::lockForUpdate()->findOrFail($line['product_id']);
                $lineDiscount = $line['discount'] ?? 0;
                $lineTotal = ($line['qty'] * $line['unit_cost']) - $lineDiscount;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'qty' => $line['qty'],
                    'unit_cost' => $line['unit_cost'],
                    'discount' => $lineDiscount,
                    'total' => $lineTotal,
                ]);

                $product->applyPurchase((int) $line['qty'], (float) $line['unit_cost']);

                StockMovement::log($product, 'purchase', (int) $line['qty'], $purchase, 'Purchase '.$purchase->purchase_number);
            }

            if ($amountPaid > 0) {
                SupplierPayment::create([
                    'payment_number' => 'SPAY-'.now()->format('Y').'-'.str_pad((SupplierPayment::max('id') + 1), 3, '0', STR_PAD_LEFT),
                    'supplier_id' => $purchase->supplier_id,
                    'purchase_id' => $purchase->id,
                    'amount' => $amountPaid,
                    'method' => $data['payment_method'] ?? 'cash',
                    'paid_at' => now(),
                ]);
            }

            $purchase->refreshStatus();

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase has been recorded and stock updated successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'items.product', 'payments');

        return view('purchases.show', compact('purchase'));
    }
}
