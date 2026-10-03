<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $receipt->receipt_number }} | Dalmar Furniture</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace, -apple-system, sans-serif;
        }
        body {
            background: #f1f3f5;
            color: #000;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .actions-bar {
            width: 80mm;
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }
        .btn {
            flex: 1;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid #ccc;
            background: #fff;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            color: #333;
        }
        .btn-print {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }
        .receipt-container {
            width: 80mm;
            background: #fff;
            padding: 15px 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-radius: 4px;
            font-size: 12px;
            line-height: 1.35;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .receipt-title {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .receipt-sub {
            font-size: 10px;
            color: #444;
            margin-top: 2px;
        }
        .divider {
            border-top: 1px dashed #555;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 6px 0;
        }
        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .totals-table {
            width: 100%;
            font-size: 12px;
        }
        .totals-table td {
            padding: 2px 0;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 12px;
            font-size: 11px;
        }
        .barcode {
            margin: 8px auto 4px auto;
            letter-spacing: 4px;
            font-size: 16px;
            font-weight: bold;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }
            .actions-bar {
                display: none !important;
            }
            .receipt-container {
                width: 100%;
                max-width: 80mm;
                box-shadow: none;
                border-radius: 0;
                padding: 4px 6px;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="actions-bar">
        <button class="btn btn-print" onclick="window.print()">Print Receipt</button>
        <a href="{{ route('receipts.show', $receipt) }}" class="btn">Back to Receipt</a>
    </div>

    <div class="receipt-container">
        <div class="receipt-header">
            <div class="receipt-title">{{ $setting->company_name ?? 'DALMAR FURNITURE' }}</div>
            <div class="receipt-sub">Furniture & House Interior Management</div>
            @if($setting->phone)
                <div class="receipt-sub">Tel: {{ $setting->phone }}</div>
            @endif
            @if($setting->address)
                <div class="receipt-sub">{{ $setting->address }}</div>
            @endif
        </div>

        <div class="divider"></div>

        <div class="info-row">
            <span>Receipt #:</span>
            <span class="fw-bold">{{ $receipt->payments->first()?->receipt_number ?? $receipt->receipt_number }}</span>
        </div>
        <div class="info-row">
            <span>Order #:</span>
            <span>{{ $receipt->order->order_number ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span>Date & Time:</span>
            <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span>Cashier:</span>
            <span>{{ $receipt->order->user->name ?? auth()->user()->name }}</span>
        </div>
        <div class="info-row">
            <span>Customer:</span>
            <span class="fw-bold">{{ $receipt->customer->name ?? 'Walk-in Customer' }}</span>
        </div>
        @if($receipt->customer && $receipt->customer->phone !== 'N/A')
        <div class="info-row">
            <span>Phone:</span>
            <span>{{ $receipt->customer->phone }}</span>
        </div>
        @endif

        <div class="divider"></div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Item</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-end" style="width: 15%;">Price</th>
                    <th class="text-end" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipt->order->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Product' }}</td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td class="text-end">${{ number_format($item->price, 2) }}</td>
                        <td class="text-end fw-bold">${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-end">${{ number_format($receipt->order->subtotal ?? $receipt->amount, 2) }}</td>
            </tr>
            @if(($receipt->order->discount_amount ?? 0) > 0)
            <tr>
                <td>Discount ({{ $receipt->order->discount_label }}):</td>
                <td class="text-end">-${{ number_format($receipt->order->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="fw-bold" style="font-size: 14px;">
                <td style="padding-top: 4px;">TOTAL:</td>
                <td class="text-end" style="padding-top: 4px;">${{ number_format($receipt->amount, 2) }}</td>
            </tr>
        </table>

        <div class="double-divider"></div>

        <table class="totals-table">
            @php
                $paid = $receipt->payments->where('status', 'paid')->sum('amount');
                $latestPayment = $receipt->payments->where('status', 'paid')->last();
            @endphp
            <tr>
                <td>Paid Amount:</td>
                <td class="text-end fw-bold">${{ number_format($paid, 2) }}</td>
            </tr>
            @if($latestPayment)
            <tr style="font-size: 10px; color: #444;">
                <td>Method:</td>
                <td class="text-end text-uppercase">{{ str_replace('_', ' ', $latestPayment->method) }}</td>
            </tr>
            @if($latestPayment->sender_phone)
            <tr style="font-size: 10px; color: #444;">
                <td>Sender Phone:</td>
                <td class="text-end">{{ $latestPayment->sender_phone }}</td>
            </tr>
            @endif
            @endif
            <tr>
                <td>Balance Due:</td>
                <td class="text-end fw-bold {{ $receipt->balance_due > 0 ? 'text-danger' : '' }}">
                    ${{ number_format($receipt->balance_due, 2) }}
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="receipt-footer">
            <div class="barcode">*{{ $receipt->receipt_number }}*</div>
            <p class="fw-bold">Mahadsanid! / Thank you!</p>
            <p style="font-size: 9px; color: #666; margin-top: 3px;">Alaabta la qaatay lama celin karo 3 maalmood ka dib.</p>
        </div>
    </div>

</body>
</html>
