@extends('layouts.shop')

@section('title', 'Contact Us')

@section('content')
    <h3 class="fw-bold mb-4 text-center" style="color: var(--navy);">Contact Us</h3>

    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div style="background:#fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(16,25,46,.06); height: 100%;">
                <h6 class="fw-bold mb-3" style="color: var(--navy);">Get In Touch</h6>
                <p class="mb-2"><i class="bi bi-geo-alt-fill me-2" style="color: var(--navy);"></i> {{ $setting->address ?: 'Garowe, Somalia' }}</p>
                <p class="mb-2"><i class="bi bi-telephone-fill me-2" style="color: var(--navy);"></i> {{ $setting->phones ? implode(' / ', $setting->phones) : '+252 XX XXX XXXX' }}</p>
                <p class="mb-2"><i class="bi bi-envelope-fill me-2" style="color: var(--navy);"></i> {{ $setting->email ?: 'info@dalmarfurniture.com' }}</p>
                <p class="mb-0"><i class="bi bi-clock-fill me-2" style="color: var(--navy);"></i> Sat - Thu: 8:00 AM - 6:00 PM</p>
            </div>
        </div>
        <div class="col-md-6">
            <div style="background:#fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 10px rgba(16,25,46,.06);">
                <h6 class="fw-bold mb-3" style="color: var(--navy);">Send Us a Message</h6>
                <form action="{{ route('shop.contact.submit') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Message</label>
                        <textarea name="message" rows="4" class="form-control" required>{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-navy w-100">Send Message</button>
                </form>
            </div>
        </div>
    </div>
@endsection
