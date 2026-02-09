@extends('layouts.app')

@section('title', 'ثبت درخواست جدید')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">ثبت درخواست جدید</h2>
        <a href="{{ route('personnel-requests.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-right"></i> بازگشت به لیست
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('personnel-requests.store') }}">
                @csrf

                <div class="row g-3">
                    {{-- Full Name --}}
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">
                            نام و نام خانوادگی <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('full_name') is-invalid @enderror"
                               id="full_name"
                               name="full_name"
                               value="{{ old('full_name') }}"
                               required
                               placeholder="مثال: علی احمدی">
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- National Code --}}
                    <div class="col-md-6">
                        <label for="national_code" class="form-label">
                            کد ملی <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('national_code') is-invalid @enderror"
                               id="national_code"
                               name="national_code"
                               value="{{ old('national_code') }}"
                               required
                               maxlength="10"
                               pattern="[0-9]{10}"
                               placeholder="1234567890">
                        @error('national_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">کد ملی 10 رقمی بدون خط تیره</div>
                    </div>

                    {{-- Phone --}}
                    <div class="col-md-6">
                        <label for="phone" class="form-label">
                            شماره تماس <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('phone') is-invalid @enderror"
                               id="phone"
                               name="phone"
                               value="{{ old('phone') }}"
                               required
                               placeholder="09123456789">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Family Count --}}
                    <div class="col-md-6">
                        <label for="family_count" class="form-label">
                            تعداد خانواده <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control @error('family_count') is-invalid @enderror"
                               id="family_count"
                               name="family_count"
                               value="{{ old('family_count', 1) }}"
                               required
                               min="1"
                               max="10">
                        @error('family_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">حداقل 1، حداکثر 10 نفر</div>
                    </div>

                    {{-- Preferred Center --}}
                    <div class="col-md-6">
                        <label for="preferred_center_id" class="form-label">
                            مرکز مورد نظر <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('preferred_center_id') is-invalid @enderror"
                                id="preferred_center_id"
                                name="preferred_center_id"
                                required>
                            <option value="">-- انتخاب کنید --</option>
                            @foreach($centers as $center)
                                <option value="{{ $center->id }}" {{ old('preferred_center_id') == $center->id ? 'selected' : '' }}>
                                    {{ $center->name }} - {{ $center->city }}
                                </option>
                            @endforeach
                        </select>
                        @error('preferred_center_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Province --}}
                    <div class="col-md-6">
                        <label for="province_id" class="form-label">
                            استان / اداره امور (اختیاری)
                        </label>
                        <select class="form-select @error('province_id') is-invalid @enderror"
                                id="province_id"
                                name="province_id">
                            <option value="">-- انتخاب کنید --</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('province_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="col-12">
                        <label for="notes" class="form-label">
                            یادداشت / توضیحات (اختیاری)
                        </label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes"
                                  name="notes"
                                  rows="3"
                                  maxlength="1000"
                                  placeholder="توضیحات تکمیلی در صورت نیاز...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">حداکثر 1000 کاراکتر</div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> ثبت درخواست
                    </button>
                    <a href="{{ route('personnel-requests.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Help Card --}}
    <div class="card mt-3">
        <div class="card-header bg-info text-white">
            <i class="bi bi-info-circle"></i> راهنما
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li>کد ملی باید 10 رقم و یونیک باشد (قبلاً ثبت نشده باشد)</li>
                <li>بعد از ثبت، درخواست در وضعیت "در انتظار بررسی" قرار می‌گیرد</li>
                <li>یک کد پیگیری به صورت خودکار تولید می‌شود (REQ-XXXXXXXX)</li>
                <li>می‌توانید درخواست را تأیید یا رد کنید</li>
                <li>فقط پس از تأیید، امکان صدور معرفی‌نامه وجود دارد</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Validate national code length
    document.getElementById('national_code').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });

    // Phone number validation
    document.getElementById('phone').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
@endpush
@endsection
