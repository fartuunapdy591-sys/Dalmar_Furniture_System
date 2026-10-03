<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::insert([
            ['name' => 'Admin User', 'email' => 'admin@dalmar.com', 'password' => Hash::make('password'), 'role' => 'admin', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales Manager', 'email' => 'sales@dalmar.com', 'password' => Hash::make('password'), 'role' => 'sales_manager', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales Person', 'email' => 'salesperson@dalmar.com', 'password' => Hash::make('password'), 'role' => 'salesperson', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accountant', 'email' => 'accountant@dalmar.com', 'password' => Hash::make('password'), 'role' => 'accountant', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Categories
        $categories = [
            ['name' => 'Living Room', 'description' => 'Furniture for living room', 'status' => 'active'],
            ['name' => 'Bedroom', 'description' => 'Bedroom furniture', 'status' => 'active'],
            ['name' => 'Dining Room', 'description' => 'Dining tables, chairs, etc.', 'status' => 'active'],
            ['name' => 'Office Furniture', 'description' => 'Office tables, chairs, etc.', 'status' => 'active'],
            ['name' => 'Decor & Accessories', 'description' => 'Decorative items', 'status' => 'active'],
        ];
        foreach ($categories as $c) {
            Category::create($c);
        }

        // Products
        $products = [
            ['name' => 'Modern Sofa Set', 'category' => 'Living Room', 'price' => 1200, 'stock' => 12, 'image' => 'product_sofa_modern.jpg'],
            ['name' => 'Dining Table & Seater', 'category' => 'Dining Room', 'price' => 850, 'stock' => 8, 'image' => 'product_dining_table.jpg'],
            ['name' => 'King Size Bed', 'category' => 'Bedroom', 'price' => 950, 'stock' => 6, 'image' => 'product_king_bed.jpg'],
            ['name' => 'Office Chair', 'category' => 'Office Furniture', 'price' => 150, 'stock' => 15, 'image' => 'product_office_chair.jpg'],
            ['name' => 'TV Cabinet', 'category' => 'Living Room', 'price' => 350, 'stock' => 10, 'image' => 'product_tv_cabinet.jpg'],
            ['name' => 'Wardrobe 3-Door', 'category' => 'Bedroom', 'price' => 600, 'stock' => 7, 'image' => 'product_wardrobe.jpg'],
            ['name' => 'Coffee Table', 'category' => 'Living Room', 'price' => 120, 'stock' => 20, 'image' => 'product_coffee_table.jpg'],
            ['name' => 'Bookshelf', 'category' => 'Office Furniture', 'price' => 200, 'stock' => 9, 'image' => 'product_bookshelf.jpg'],
        ];
        foreach ($products as $p) {
            Product::create([
                'name' => $p['name'],
                'category_id' => Category::where('name', $p['category'])->first()->id,
                'price' => $p['price'],
                'stock' => $p['stock'],
                'image' => $p['image'],
                'status' => 'active',
            ]);
        }

        // Customers
        $customers = [
            ['name' => 'Ahmed Hassan', 'email' => 'ahmed@gmail.com', 'phone' => '+252 61 1234567'],
            ['name' => 'Fatima Ali', 'email' => 'fatima@gmail.com', 'phone' => '+252 61 2345678'],
            ['name' => 'Mohamed Yusuf', 'email' => 'mohamed@gmail.com', 'phone' => '+252 61 3456789'],
            ['name' => 'Aisha Abdullahi', 'email' => 'aisha@gmail.com', 'phone' => '+252 61 4567890'],
            ['name' => 'Abdirahman Omar', 'email' => 'omar@gmail.com', 'phone' => '+252 61 5678901'],
        ];
        foreach ($customers as $c) {
            Customer::create([
                'name' => $c['name'],
                'email' => $c['email'],
                'phone' => $c['phone'],
                'status' => 'active',
            ]);
        }

        // Orders + items + payment
        $salesperson = User::where('role', 'salesperson')->first();
        $orderSeeds = [
            ['customer' => 'Ahmed Hassan', 'product' => 'Modern Sofa Set', 'qty' => 1, 'status' => 'completed', 'payment' => 'paid'],
            ['customer' => 'Fatima Ali', 'product' => 'Dining Table & Seater', 'qty' => 1, 'status' => 'processing', 'payment' => 'paid'],
            ['customer' => 'Mohamed Yusuf', 'product' => 'King Size Bed', 'qty' => 1, 'status' => 'completed', 'payment' => 'paid'],
            ['customer' => 'Aisha Abdullahi', 'product' => 'Office Chair', 'qty' => 1, 'status' => 'pending', 'payment' => 'cash'],
            ['customer' => 'Abdirahman Omar', 'product' => 'TV Cabinet', 'qty' => 1, 'status' => 'completed', 'payment' => 'paid'],
        ];

        foreach ($orderSeeds as $i => $seed) {
            $customer = Customer::where('name', $seed['customer'])->first();
            $product = Product::where('name', $seed['product'])->first();
            $total = $product->price * $seed['qty'];

            $order = Order::create([
                'order_number' => 'ORD-2024-'.str_pad($i + 81, 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'user_id' => $salesperson?->id,
                'order_date' => now()->subDays(5 - $i),
                'total_amount' => $total,
                'payment_method' => $seed['payment'],
                'status' => $seed['status'],
                'shipping_address' => 'Mogadishu, Somalia',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $product->price,
                'qty' => $seed['qty'],
                'total' => $total,
            ]);

            $paid = $seed['payment'] === 'paid';

            $receipt = Receipt::create([
                'receipt_number' => 'REC-'.now()->format('Y').'-'.str_pad(($i + 1), 3, '0', STR_PAD_LEFT),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'amount' => $total,
                'issued_date' => $order->order_date,
                'status' => $paid ? 'paid' : 'unpaid',
            ]);

            Payment::create([
                'order_id' => $order->id,
                'receipt_id' => $receipt->id,
                'amount' => $total,
                'method' => $paid ? 'card' : 'cash',
                'status' => $paid ? 'paid' : 'pending',
            ]);
        }
    }
}
