@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="mb-3">
        <div class="page-title">Settings</div>
        <div class="page-subtitle">Home / Settings</div>
    </div>

    <div class="card-panel" style="max-width: 700px;">
        <div class="card-panel-title">General Information</div>
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label small fw-semibold">Company Name</label>
                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $setting->company_name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Business Logo</label>
                @if($setting->logo_url)
                    <div class="mb-2"><img src="{{ $setting->logo_url }}" alt="Logo" style="height:48px;"></div>
                @endif
                <input type="file" name="logo" class="form-control" accept="image/*">
                <div class="form-text">Shown on printed receipts. PNG/JPG, max 2MB.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Contact Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $setting->email) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Phone Number(s)</label>
                    <input type="text" name="phone" class="form-control" placeholder="e.g. +252 61 1112222, +252 61 3334444" value="{{ old('phone', $setting->phone) }}">
                    <div class="form-text">Qor hal ama dhowr telefan oo comma ku kala go'an — waa telefanada dukaanka/xarunta laga soo xiriiri karo.</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $setting->address) }}">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Currency</label>
                    <select name="currency" class="form-select">
                        <option value="USD" @selected($setting->currency === 'USD')>USD ($)</option>
                        <option value="SOS" @selected($setting->currency === 'SOS')>SOS (Somali Shilling)</option>
                        <option value="EUR" @selected($setting->currency === 'EUR')>EUR (€)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Timezone</label>
                    <select name="timezone" class="form-select">
                        <option value="Africa/Mogadishu" @selected($setting->timezone === 'Africa/Mogadishu')>Africa/Mogadishu</option>
                        <option value="Africa/Nairobi" @selected($setting->timezone === 'Africa/Nairobi')>Africa/Nairobi</option>
                        <option value="UTC" @selected($setting->timezone === 'UTC')>UTC</option>
                    </select>
                </div>
            </div>

            <hr>
            <div class="card-panel-title" style="font-size:15px;">Notification Preferences</div>

            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="email_notifications" id="emailNotif" value="1" @checked($setting->email_notifications)>
                <label class="form-check-label small" for="emailNotif">Send email notifications for new orders</label>
            </div>
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="low_stock_alerts" id="lowStock" value="1" @checked($setting->low_stock_alerts)>
                <label class="form-check-label small" for="lowStock">Alert me when products are low in stock</label>
            </div>

            <button type="submit" class="btn btn-navy">Save Changes</button>
        </form>
    </div>

    <!-- Database Backup Section -->
    <div class="card-panel mt-4" style="max-width: 700px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <div class="card-panel-title mb-0"><i class="bi bi-database-down me-1"></i> Database Backup & Data Safety</div>
                <div class="small text-muted">Download a complete SQL dump of your Dalmar Furniture system database.</div>
            </div>
            <a href="{{ route('settings.backup') }}" class="btn btn-navy">
                <i class="bi bi-download me-1"></i> Download Backup (.sql)
            </a>
        </div>
    </div>
@endsection
