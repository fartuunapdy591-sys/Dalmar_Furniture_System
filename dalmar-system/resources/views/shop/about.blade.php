@extends('layouts.shop')

@section('title', 'About Us')

@section('content')
    <div class="text-center mb-5">
        <h3 class="fw-bold" style="color: var(--navy);">About Dalmar Furniture &amp; House Interior</h3>
        <p class="text-muted">Quality furniture, trusted service.</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div style="background:#fff; border-radius: 14px; padding: 28px; box-shadow: 0 2px 10px rgba(16,25,46,.06); height: 100%;">
                <h5 class="fw-bold mb-3" style="color: var(--navy);">Who We Are</h5>
                <p class="mb-0">
                    Dalmar Furniture &amp; House Interior has been furnishing homes and offices with quality,
                    long-lasting furniture. From bedroom sets to office pieces, we source and craft furniture
                    that fits every style and budget.
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div style="background:#fff; border-radius: 14px; padding: 28px; box-shadow: 0 2px 10px rgba(16,25,46,.06); height: 100%;">
                <h5 class="fw-bold mb-3" style="color: var(--navy);">Our Promise</h5>
                <p class="mb-0">
                    We stand behind every piece we sell. Browse our collection online, place your order,
                    and our team will reach out to confirm delivery details and get your furniture to you
                    quickly and safely.
                </p>
            </div>
        </div>
    </div>

    <div class="row g-4 text-center mb-5">
        <div class="col-md-4">
            <i class="bi bi-truck fs-1" style="color: var(--navy);"></i>
            <h6 class="fw-bold mt-2">Fast Delivery</h6>
            <p class="text-muted small">We deliver to your doorstep across the city.</p>
        </div>
        <div class="col-md-4">
            <i class="bi bi-shield-check fs-1" style="color: var(--navy);"></i>
            <h6 class="fw-bold mt-2">Quality Guaranteed</h6>
            <p class="text-muted small">Every product is checked for quality before it ships.</p>
        </div>
        <div class="col-md-4">
            <i class="bi bi-headset fs-1" style="color: var(--navy);"></i>
            <h6 class="fw-bold mt-2">Friendly Support</h6>
            <p class="text-muted small">Reach out any time, we're happy to help.</p>
        </div>
    </div>

    <div class="mb-5">
        <h5 class="fw-bold text-center mb-4" style="color: var(--navy);">How To Order</h5>
        <div class="row g-4 text-center">
            @foreach([
                ['1', 'Choose', 'Browse the shop and add the furniture you like to your order.'],
                ['2', 'Order', 'Enter your details and pick how you want to pay: Cash, Mobile Money or e-Dahab.'],
                ['3', 'Receive', 'Our team confirms with you and delivers to your address.'],
            ] as [$step, $title, $text])
                <div class="col-md-4">
                    <div style="width:44px; height:44px; line-height:44px; border-radius:50%; background: var(--navy); color:#fff; font-weight:700; margin:0 auto;">{{ $step }}</div>
                    <h6 class="fw-bold mt-2">{{ $title }}</h6>
                    <p class="text-muted small">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>

    @php $waNumber = ltrim(preg_replace('/\D+/', '', (string) $setting->phone), '0'); @endphp
    <div class="mb-5" style="background:#fff; border-radius: 14px; padding: 28px; box-shadow: 0 2px 10px rgba(16,25,46,.06);">
        <h5 class="fw-bold mb-3" style="color: var(--navy);">Visit Us</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <p class="mb-2"><i class="bi bi-geo-alt-fill me-2" style="color: var(--navy);"></i> {{ $setting->address ?: 'Garowe, Somalia' }}</p>
                <p class="mb-2"><i class="bi bi-telephone-fill me-2" style="color: var(--navy);"></i> {{ $setting->phone ?: '+252 XX XXX XXXX' }}</p>
                <p class="mb-0"><i class="bi bi-clock-fill me-2" style="color: var(--navy);"></i> Sat - Thu: 8:00 AM - 6:00 PM</p>
            </div>
            <div class="col-md-6 d-flex align-items-center gap-2 flex-wrap">
                @if($waNumber)
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="btn btn-success"><i class="bi bi-whatsapp me-1"></i> WhatsApp Us</a>
                @endif
                <a href="{{ route('shop.contact') }}" class="btn btn-outline-secondary">Send a Message</a>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('shop.index') }}" class="btn btn-navy">Browse Our Furniture</a>
    </div>
@endsection
