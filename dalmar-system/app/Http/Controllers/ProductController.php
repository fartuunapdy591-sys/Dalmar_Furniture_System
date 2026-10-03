<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::where('status', 'active')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $allProducts = Product::orderBy('name')->get(['id', 'name', 'stock', 'cost']);

        return view('products.index', compact('products', 'categories', 'suppliers', 'allProducts'));
    }

    public function restock(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $previousStock = $product->stock;

        DB::transaction(function () use ($product, $data) {
            $product->applyPurchase((int) $data['quantity'], (float) $data['unit_cost']);

            StockMovement::create([
                'product_id' => $product->id,
                'supplier_id' => $data['supplier_id'] ?? null,
                'type' => 'purchase',
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'],
                'user_id' => Auth::id(),
                'notes' => $data['notes'] ?? 'Manual restock',
            ]);
        });

        $product->refresh();

        ActivityLog::record('stock_restocked', $product, Auth::user()->name." restocked {$product->name}: +{$data['quantity']} units (previous stock {$previousStock}, new stock {$product->stock}).");

        return back()->with('success', "Stock updated for {$product->name}: {$previousStock} \u{2192} {$product->stock}.");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['cost'] = $data['cost'] ?? 0;
        $data['min_stock'] = $data['min_stock'] ?? 5;
        $data['unit'] = $data['unit'] ?? 'pcs';
        $data['created_by'] = Auth::id();

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'));
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product has been added successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', 'active')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['updated_by'] = Auth::id();

        if ($request->hasFile('image')) {
            $this->deleteImage($product->image);
            $data['image'] = $this->storeImage($request->file('image'));
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product has been updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->update(['status' => 'inactive', 'updated_by' => Auth::id()]);

        return back()->with('success', "{$product->name} has been marked inactive. You can restore it any time.");
    }

    public function restore(Product $product)
    {
        $product->update(['status' => 'active', 'updated_by' => Auth::id()]);

        return back()->with('success', "{$product->name} has been restored and is active again.");
    }

    private function storeImage($file): string
    {
        $filename = uniqid('product_').'.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/products'), $filename);

        return $filename;
    }

    private function deleteImage(?string $filename): void
    {
        if ($filename && file_exists(public_path('uploads/products/'.$filename))) {
            unlink(public_path('uploads/products/'.$filename));
        }
    }
}
