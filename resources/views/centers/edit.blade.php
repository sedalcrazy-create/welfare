@extends('layouts.app')

@section('title', 'ویرایش ' . $center->name)

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-pencil-square"></i> ویرایش مرکز رفاهی</h1>
    <a href="{{ route('centers.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i>
        بازگشت به لیست
    </a>
</div>

<!-- Form -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-building"></i>
        ویرایش: {{ $center->name }}
    </div>
    <div class="card-body">
        <form action="{{ route('centers.update', $center) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">نام مرکز <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $center->name) }}" placeholder="مثال: زائرسرای مشهد مقدس">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">شهر <span class="text-danger">*</span></label>
                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                           value="{{ old('city', $center->city) }}" placeholder="مثال: مشهد">
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">نوع مرکز <span class="text-danger">*</span></label>
                    <select name="type" class="form-control @error('type') is-invalid @enderror">
                        <option value="">انتخاب کنید...</option>
                        <option value="religious" {{ old('type', $center->type) === 'religious' ? 'selected' : '' }}>زیارتی</option>
                        <option value="beach" {{ old('type', $center->type) === 'beach' ? 'selected' : '' }}>ساحلی (تفریحی)</option>
                        <option value="mountain" {{ old('type', $center->type) === 'mountain' ? 'selected' : '' }}>کوهستانی (تفریحی)</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">مدت اقامت (شب) <span class="text-danger">*</span></label>
                    <input type="number" name="stay_duration" class="form-control @error('stay_duration') is-invalid @enderror"
                           value="{{ old('stay_duration', $center->stay_duration) }}" min="1" max="30">
                    @error('stay_duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">تلفن</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $center->phone) }}" placeholder="مثال: 051-12345678">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">ساعت ورود <span class="text-danger">*</span></label>
                    <input type="time" name="check_in_time" class="form-control @error('check_in_time') is-invalid @enderror"
                           value="{{ old('check_in_time', substr($center->check_in_time, 0, 5)) }}">
                    @error('check_in_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">ساعت خروج <span class="text-danger">*</span></label>
                    <input type="time" name="check_out_time" class="form-control @error('check_out_time') is-invalid @enderror"
                           value="{{ old('check_out_time', substr($center->check_out_time, 0, 5)) }}">
                    @error('check_out_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">آدرس</label>
                <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                          rows="2" placeholder="آدرس کامل مرکز">{{ old('address', $center->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">توضیحات</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                          rows="3" placeholder="توضیحات تکمیلی درباره مرکز">{{ old('description', $center->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">امکانات</label>
                <div class="amenities-grid">
                    @php
                        $amenities = [
                            'parking' => 'پارکینگ',
                            'wifi' => 'اینترنت WiFi',
                            'restaurant' => 'رستوران',
                            'prayer_room' => 'نمازخانه',
                            'gym' => 'سالن ورزش',
                            'pool' => 'استخر',
                            'playground' => 'زمین بازی',
                            'laundry' => 'خشکشویی',
                            'medical' => 'بهداری',
                            'shop' => 'فروشگاه',
                        ];
                        $selectedAmenities = old('amenities', $center->amenities ?? []);
                    @endphp
                    @foreach($amenities as $key => $label)
                        <label class="amenity-checkbox">
                            <input type="checkbox" name="amenities[]" value="{{ $key }}"
                                   {{ in_array($key, $selectedAmenities) ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <label class="switch-label">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $center->is_active) ? 'checked' : '' }}>
                    <span class="switch-slider"></span>
                    <span class="switch-text">مرکز فعال باشد</span>
                </label>
            </div>

            <!-- Stats Info -->
            <div class="stats-info mb-4">
                <div class="stat-item">
                    <i class="bi bi-door-open"></i>
                    <span>تعداد واحد: <strong>{{ number_format($center->unit_count) }}</strong></span>
                </div>
                <div class="stat-item">
                    <i class="bi bi-bed"></i>
                    <span>تعداد تخت: <strong>{{ number_format($center->bed_count) }}</strong></span>
                </div>
                <div class="stat-item">
                    <i class="bi bi-calendar3"></i>
                    <span>تاریخ ایجاد: <strong>{{ jdate($center->created_at)->format('Y/m/d') }}</strong></span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i>
                    ذخیره تغییرات
                </button>
                <a href="{{ route('centers.show', $center) }}" class="btn btn-info">
                    <i class="bi bi-eye"></i>
                    مشاهده
                </a>
                <a href="{{ route('centers.index') }}" class="btn btn-secondary">
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
        border-color: #dc3545 !important;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 5px;
    }

    .text-danger {
        color: #dc3545;
    }

    .amenities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
        padding: 15px;
        background: #f9fafb;
        border-radius: 12px;
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
    }

    .amenity-checkbox:hover {
        background: #fff4cc;
    }

    .amenity-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #ff8928;
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
        background: linear-gradient(135deg, #28a745 0%, #20863a 100%);
    }

    .switch-label input:checked + .switch-slider::before {
        transform: translateX(24px);
    }

    .switch-text {
        font-weight: 500;
    }

    .stats-info {
        display: flex;
        gap: 30px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #fff8e1 0%, #fff4cc 100%);
        border-radius: 12px;
        flex-wrap: wrap;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
    }

    .stat-item i {
        color: #ff8928;
        font-size: 1.2rem;
    }

    .stat-item strong {
        color: #333;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        padding-top: 20px;
        border-top: 2px solid #fff4cc;
    }

    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }
</style>
@endpush
