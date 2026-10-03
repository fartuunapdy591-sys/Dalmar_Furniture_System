<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const TYPES = [
        'sales' => 'Sales Report',
        'orders' => 'Orders Report',
        'customers' => 'Customers Report',
        'payments' => 'Payments Report',
    ];

    public function index(Request $request)
    {
        $data = $this->buildReport($request);

        return view('reports.index', $data);
    }

    public function export(Request $request): StreamedResponse
    {
        $data = $this->buildReport($request);
        $type = $data['reportType'];
        $filename = 'dalmar-'.$type.'-report-'.now()->format('Y-m-d').'.csv';

        $callback = function () use ($data, $type) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Dalmar Furniture and House Interior - '.self::TYPES[$type]]);
            fputcsv($handle, ['Generated', now()->format('Y-m-d H:i')]);
            fputcsv($handle, ['Period', ($data['from'] ?: 'All time').' to '.($data['to'] ?: 'now')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Summary']);
            foreach ($data['summary'] as $row) {
                fputcsv($handle, [$row['label'], $row['value']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, $data['columns']);
            foreach ($data['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fputcsv($handle, $data['totalsRow']);

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildReport(Request $request): array
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('order_status');
        $paymentMethod = $request->query('payment_method');
        $customerId = $request->query('customer_id');
        $categoryId = $request->query('category_id');
        $reportType = $request->query('report_type', 'sales');
        if (! array_key_exists($reportType, self::TYPES)) {
            $reportType = 'sales';
        }

        // "Category" filters to orders that include at least one item from that category;
        // order-level totals still cover the whole order, since an order can mix categories.
        $inCategory = fn ($q, $column = 'orders.id') => $q->whereExists(function ($sub) use ($categoryId, $column) {
            $sub->selectRaw('1')
                ->from('order_items')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->whereColumn('order_items.order_id', $column)
                ->where('products.category_id', $categoryId);
        });

        $ordersQuery = fn () => Order::query()
            ->when($status, fn ($q) => $q->where('status', $status), fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->when($from, fn ($q) => $q->whereDate('order_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('order_date', '<=', $to))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($categoryId, fn ($q) => $inCategory($q, 'orders.id'));

        $totalSales = (float) $ordersQuery()->sum('total_amount');
        $totalOrders = $ordersQuery()->count();
        $averageOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        [$summary, $columns, $rows, $totalsRow] = match ($reportType) {
            'orders' => $this->buildOrdersReport($ordersQuery, $totalSales, $totalOrders, $averageOrder),
            'customers' => $this->buildCustomersReport($from, $to, $status, $paymentMethod, $categoryId, $customerId, $inCategory),
            'payments' => $this->buildPaymentsReport($from, $to, $paymentMethod, $customerId),
            default => $this->buildSalesReport($from, $to, $status, $paymentMethod, $customerId, $categoryId),
        };

        // For the filter form: the full lists of choices, and echoing back what is selected.
        $filterCategories = Category::orderBy('name')->get(['id', 'name']);
        $filterCustomers = Customer::orderBy('name')->get(['id', 'name']);
        $filterStatuses = ['pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
        $filterPaymentMethods = ['cash' => 'Cash', 'sahal' => 'Sahal', 'e_dahab' => 'e-Dahab', 'mycash' => 'MyCash', 'card' => 'Card'];

        return compact(
            'from', 'to', 'status', 'paymentMethod', 'customerId', 'categoryId', 'reportType',
            'filterCategories', 'filterCustomers', 'filterStatuses', 'filterPaymentMethods',
            'summary', 'columns', 'rows', 'totalsRow'
        ) + ['reportTypes' => self::TYPES, 'setting' => Setting::current()];
    }

    /**
     * @return array{0: array, 1: array, 2: array, 3: array}
     */
    private function buildSalesReport(?string $from, ?string $to, ?string $status, ?string $paymentMethod, ?string $customerId, ?string $categoryId): array
    {
        $itemsQuery = fn () => OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->when($status, fn ($q) => $q->where('orders.status', $status), fn ($q) => $q->where('orders.status', '!=', 'cancelled'))
            ->when($from, fn ($q) => $q->whereDate('orders.order_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('orders.order_date', '<=', $to))
            ->when($paymentMethod, fn ($q) => $q->where('orders.payment_method', $paymentMethod))
            ->when($customerId, fn ($q) => $q->where('orders.customer_id', $customerId))
            ->when($categoryId, fn ($q) => $q->where('products.category_id', $categoryId));

        $totalCogs = (float) $itemsQuery()->sum(DB::raw('order_items.qty * products.cost'));
        $totalRevenue = (float) $itemsQuery()->sum('order_items.total');
        $grossProfit = $totalRevenue - $totalCogs;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        $totalExpenses = (float) Expense::query()
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
            ->sum('amount');

        $netProfit = $grossProfit - $totalExpenses;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        $summary = [
            ['label' => 'Total Revenue', 'value' => '$'.number_format($totalRevenue, 2)],
            ['label' => 'Cost of Goods Sold', 'value' => '$'.number_format($totalCogs, 2)],
            ['label' => 'Gross Profit', 'value' => '$'.number_format($grossProfit, 2).' ('.number_format($grossMargin, 1).'%)'],
            ['label' => 'Operating Expenses', 'value' => '$'.number_format($totalExpenses, 2)],
            ['label' => 'Net Profit', 'value' => '$'.number_format($netProfit, 2).' ('.number_format($netMargin, 1).'%)'],
        ];

        $categories = $itemsQuery()
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select(
                DB::raw("COALESCE(categories.name, 'Uncategorized') as name"),
                DB::raw('SUM(order_items.qty) as qty_sold'),
                DB::raw('SUM(order_items.total) as revenue')
            )
            ->groupBy('name')
            ->orderByDesc('revenue')
            ->get();

        $columns = ['Category', 'Qty Sold', 'Revenue', 'Share'];
        $totalQty = $categories->sum('qty_sold');
        $rows = $categories->map(fn ($c) => [
            $c->name,
            $c->qty_sold,
            '$'.number_format($c->revenue, 2),
            $totalRevenue > 0 ? number_format($c->revenue / $totalRevenue * 100, 1).'%' : '0%',
        ])->all();
        $totalsRow = ['Total', $totalQty, '$'.number_format($totalRevenue, 2), '100%'];

        return [$summary, $columns, $rows, $totalsRow];
    }

    private function buildOrdersReport(\Closure $ordersQuery, float $totalSales, int $totalOrders, float $averageOrder): array
    {
        $summary = [
            ['label' => 'Total Orders', 'value' => $totalOrders],
            ['label' => 'Total Sales', 'value' => '$'.number_format($totalSales, 2)],
            ['label' => 'Average Order', 'value' => '$'.number_format($averageOrder, 2)],
        ];

        $orders = $ordersQuery()->with('customer')->orderBy('order_date')->orderBy('id')->get();

        $columns = ['Order #', 'Date', 'Customer', 'Payment Method', 'Status', 'Total'];
        $rows = $orders->map(fn ($o) => [
            $o->order_number,
            optional($o->order_date)->format('Y-m-d') ?? $o->order_date,
            $o->customer->name ?? '-',
            ucfirst(str_replace('_', ' ', $o->payment_method)),
            ucfirst($o->status),
            '$'.number_format($o->total_amount, 2),
        ])->all();
        $totalsRow = ['', '', '', '', 'Total', '$'.number_format($totalSales, 2)];

        return [$summary, $columns, $rows, $totalsRow];
    }

    private function buildCustomersReport(?string $from, ?string $to, ?string $status, ?string $paymentMethod, ?string $categoryId, ?string $customerId, \Closure $inCategory): array
    {
        $orderFilter = function ($q) use ($status, $from, $to, $paymentMethod, $categoryId, $inCategory) {
            $status ? $q->where('status', $status) : $q->where('status', '!=', 'cancelled');
            $from && $q->whereDate('order_date', '>=', $from);
            $to && $q->whereDate('order_date', '<=', $to);
            $paymentMethod && $q->where('payment_method', $paymentMethod);
            $categoryId && $inCategory($q, 'orders.id');
        };

        $customers = Customer::withCount(['orders' => $orderFilter])
            ->withSum(['orders as period_spent' => $orderFilter], 'total_amount')
            ->when($customerId, fn ($q) => $q->where('id', $customerId))
            ->orderByDesc('period_spent')
            ->get();

        $totalOutstandingDebt = $customers->sum('balance_due');
        $customersWithDebt = $customers->filter(fn ($c) => $c->balance_due > 0)->count();

        $summary = [
            ['label' => 'Total Customers', 'value' => $customers->count()],
            ['label' => 'Customers with Orders in Period', 'value' => $customers->filter(fn ($c) => $c->orders_count > 0)->count()],
            ['label' => 'Customers with Outstanding Debt', 'value' => $customersWithDebt],
            ['label' => 'Total Outstanding Debt', 'value' => '$'.number_format($totalOutstandingDebt, 2)],
        ];

        $columns = ['Customer', 'Phone', 'Orders', 'Total Spent', 'Balance Due'];
        $totalOrders = 0;
        $totalSpent = 0;
        $totalBalance = 0;
        $rows = $customers->map(function ($c) use (&$totalOrders, &$totalSpent, &$totalBalance) {
            $totalOrders += $c->orders_count;
            $totalSpent += (float) ($c->period_spent ?? 0);
            $totalBalance += (float) $c->balance_due;

            return [
                $c->name,
                $c->phone ?? '-',
                $c->orders_count,
                '$'.number_format($c->period_spent ?? 0, 2),
                '$'.number_format($c->balance_due, 2),
            ];
        })->all();
        $totalsRow = ['Total', '', $totalOrders, '$'.number_format($totalSpent, 2), '$'.number_format($totalBalance, 2)];

        return [$summary, $columns, $rows, $totalsRow];
    }

    private function buildPaymentsReport(?string $from, ?string $to, ?string $paymentMethod, ?string $customerId): array
    {
        $paymentsQuery = fn () => Payment::query()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($paymentMethod, fn ($q) => $q->where('method', $paymentMethod))
            ->when($customerId, fn ($q) => $q->whereHas('receipt', fn ($r) => $r->where('customer_id', $customerId)));

        $totalAmount = (float) $paymentsQuery()->sum('amount');
        $paidAmount = (float) $paymentsQuery()->where('status', 'paid')->sum('amount');
        $pendingAmount = (float) $paymentsQuery()->where('status', 'pending')->sum('amount');

        $summary = [
            ['label' => 'Total Payments', 'value' => $paymentsQuery()->count()],
            ['label' => 'Total Amount', 'value' => '$'.number_format($totalAmount, 2)],
            ['label' => 'Paid', 'value' => '$'.number_format($paidAmount, 2)],
            ['label' => 'Pending', 'value' => '$'.number_format($pendingAmount, 2)],
        ];

        $payments = $paymentsQuery()->with('receipt.customer', 'order')->orderBy('created_at')->get();

        $columns = ['Date', 'Receipt #', 'Order #', 'Customer', 'Method', 'Sender Phone', 'Amount', 'Status'];
        $rows = $payments->map(fn ($p) => [
            optional($p->created_at)->format('Y-m-d'),
            $p->receipt->receipt_number ?? '-',
            $p->order->order_number ?? '-',
            $p->receipt->customer->name ?? '-',
            ucfirst(str_replace('_', ' ', $p->method)),
            $p->sender_phone ?? '-',
            '$'.number_format($p->amount, 2),
            ucfirst($p->status),
        ])->all();
        $totalsRow = ['', '', '', '', '', 'Total', '$'.number_format($totalAmount, 2), ''];

        return [$summary, $columns, $rows, $totalsRow];
    }
}
