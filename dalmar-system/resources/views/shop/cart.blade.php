@extends('layouts.shop')

@section('title', 'My Order')

@section('content')
    <h3 class="fw-bold mb-4" style="color: var(--navy);">My Order</h3>

    @if(empty($lines))
        <div class="text-center py-5">
            <p class="text-muted mb-3">You haven't added any items yet.</p>
            <a href="{{ route('shop.index') }}" class="btn btn-navy">Browse Furniture</a>
        </div>
    @else
        <div class="card-panel mb-4" style="background:#fff; border-radius: 14px; padding: 20px; box-shadow: 0 2px 10px rgba(16,25,46,.06);">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($lines as $line)
                    <tr>
                        <td>{{ $line['product']->name }}</td>
                        <td>${{ number_format($line['product']->price, 2) }}</td>
                        <td>{{ $line['qty'] }}</td>
                        <td>${{ number_format($line['subtotal'], 2) }}</td>
                        <td>
                            <form action="{{ route('shop.cart.remove', $line['product']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-between fw-bold fs-5 mt-3 pt-3 border-top">
                <span>Total</span>
                <span style="color: var(--navy);">${{ number_format($total, 2) }}</span>
            </div>
        </div>

        <div style="background:#fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(16,25,46,.06); max-width: 520px;">
            <h5 class="fw-bold mb-3" style="color: var(--navy);">Your Details</h5>
            <form action="{{ route('shop.checkout') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Delivery Address</label>
                    <textarea name="address" class="form-control" rows="2" required>{{ old('address') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Payment Method</label>
                    <select name="payment_method" class="form-select" required>
                        @foreach(['cash' => 'Cash', 'e_dahab' => 'e-Dahab', 'sahal' => 'Sahal', 'mycash' => 'MyCash', 'card' => 'Card / Bank'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-navy w-100">Submit Order</button>
            </form>
        </div>
    @endif
@endsection
