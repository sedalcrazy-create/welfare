@extends('layouts.app')

@section('title', 'افزودن واحد')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-plus-circle"></i> افزودن واحد جدید</h1>
    <a href="{{ route('units.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i>
        بازگشت به لیست
    </a>
</div>

<!-- Form -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-door-open"></i>
        اطلاعات واحد
    </div>
    <div class="card-body">
        <form action="{{ route('units.store') }}" method="POST">
            @csrf

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">مرکز رفاهی <span class="text-danger">*</span></label>
                    <select name="center_id" class="form-control @error('center_id') is-invalid @enderror" required>
                        <option value="">انتخاب کنید...</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}"
                                {{ old('center_id', $selectedCenter) == $center->id ? 'selected' : '' }}>
                                {{ $center->name }} ({{ $center->city }})
                            </option>
                        @endforeach
                    </select>
                    @error('center_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">شماره واحد <span class="text-danger">*</span></label>
                    <input type="text" name="number" class="form-control @error('number') is-invalid @enderror"
                           value="{{ old('number') }}" placeholder="مثال: 101">
                    @error('number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">نام واحد</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="مثال: اتاق دو تخته VIP">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">نوع واحد <span class="text-danger">*</span></label>
                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="room" {{ old('type') === 'room' ? 'selected' : '' }}>اتاق</option>
                        <option value="suite" {{ old('type') === 'suite' ? 'selected' : '' }}>سوئیت</option>
                        <option value="villa" {{ old('type') === 'villa' ? 'selected' : '' }}>ویلا</option>
                        <option value="apartment" {{ old('type') === 'apartment' ? 'selected' : '' }}>آپارتمان</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">تعداد تخت <span class="text-danger">*</span></label>
                    <input type="number" name="bed_count" class="form-control @error('bed_count') is-invalid @enderror"
                           value="{{ old('bed_count', 2) }}" min="1" max="20" required>
                    @error('bed_count')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">طبقه</label>
                    <input type="number" name="floor" class="form-control @error('floor') is-invalid @enderror"
                           value="{{ old('floor') }}" min="-2" max="50" placeholder="مثال: 1">
                    @error('floor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">بلوک</label>
                    <input type="text" name="block" class="form-control @error('block') is-invalid @enderror"
                           value="{{ old('block') }}" maxlength="10" placeholder="مثال: A">
                    @error('block')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">وضعیت <span class="text-danger">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>در حال تعمیر</option>
                        <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>مسدود</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check-wrapper">
                        <label class="switch-label">
                            <input type="hidden" name="is_management" value="0">
                            <input type="checkbox" name="is_management" value="1" {{ old('is_management') ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                            <span class="switch-text">واحد مدیریتی (غیر قابل رزرو)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">امکانات واحد</label>
                <div class="amenities-grid">
                    @php
                        $amenities = [
                            'ac' => 'کولر/اسپلیت',
                            'tv' => 'تلویزیون',
                            'fridge' => 'یخچال',
                            'bathroom' => 'سرویس بهداشتی',
                            'kitchen' => 'آشپزخانه',
                            'balcony' => 'بالکن',
                            'safe' => 'گاوصندوق',
                            'wifi' => 'WiFi',
                            'phone' => 'تلفن',
                            'wardrobe' => 'کمد لباس',
                            'desk' => 'میز کار',
                            'sofa' => 'مبل',
                        ];
                    @endphp
                    @foreach($amenities as $key => $label)
                        <label class="amenity-checkbox">
                            <input type="checkbox" name="amenities[]" value="{{ $key }}"
                                   {{ in_array($key, old('amenities', [])) ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">توضیحات</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                          rows="3" placeholder="توضیحات اضافی درباره واحد...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i>
                    ذخیره واحد
                </button>
                <a href="{{ route('units.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i>
                    انصراف
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .is-invalid {
        border-color: var(--danger) !important;
    }

    .invalid-feedback {
        display: block;
        color: var(--danger);
        font-size: 0.85rem;
        margin-top: 5px;
    }

    .text-danger {
        color: var(--danger);
    }

    .amenities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
        padding: 15px;
        background: var(--bg-light);
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }

    .amenity-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 8px 12px;
        background: white;
        border-radius: 8px;
        transition: all 0.2s;
        font-size: 0.9rem;
    }

    .amenity-checkbox:hover {
        background: #e3f2fd;
    }

    .amenity-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
    }

    .form-check-wrapper {
        padding-top: 8px;
    }

    .switch-label {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
    }

    .switch-label input[type="checkbox"] {
        display: none;
    }

    .switch-slider {
        width: 50px;
        height: 26px;
        background: #ccc;
        border-radius: 13px;
        position: relative;
        transition: all 0.3s;
    }

    .switch-slider::before {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        background: white;
        border-radius: 50%;
        top: 2px;
        left: 2px;
        transition: all 0.3s;
    }

    .switch-label input:checked + .switch-slider {
        background: var(--primary);
    }

    .switch-label input:checked + .switch-slider::before {
        transform: translateX(24px);
    }

    .switch-text {
        font-weight: 500;
        color: var(--text-dark);
    }

    .form-actions {
        display: flex;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }
</style>
@endpush
