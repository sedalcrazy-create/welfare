@extends('layouts.app')

@section('title', 'ویرایش درخواست')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">ویرایش درخواست</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('personnel-requests.show', $personnelRequest) }}" class="btn btn-secondary">
                <i class="bi bi-eye"></i> مشاهده
            </a>
            <a href="{{ route('personnel-requests.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-right"></i> بازگشت به لیست
            </a>
        </div>
    </div>

    {{-- Status Alert --}}
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>وضعیت فعلی:</strong>
        @if($personnelRequest->status === 'pending')
            <span class="badge bg-warning">در انتظار بررسی</span>
        @elseif($personnelRequest->status === 'approved')
            <span class="badge bg-success">تأیید شده</span>
        @else
            <span class="badge bg-danger">رد شده</span>
        @endif
        |
        <strong>کد پیگیری:</strong> <code>{{ $personnelRequest->tracking_code }}</code>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('personnel-requests.update', $personnelRequest) }}">
                @csrf
                @method('PUT')

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
                               value="{{ old('full_name', $personnelRequest->full_name) }}"
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
                               value="{{ old('national_code', $personnelRequest->national_code) }}"
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
                               value="{{ old('phone', $personnelRequest->phone) }}"
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
                               value="{{ old('family_count', $personnelRequest->family_count) }}"
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
                                <option value="{{ $center->id }}"
                                    {{ old('preferred_center_id', $personnelRequest->preferred_center_id) == $center->id ? 'selected' : '' }}>
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
                                <option value="{{ $province->id }}"
                                    {{ old('province_id', $personnelRequest->province_id) == $province->id ? 'selected' : '' }}>
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
                                  placeholder="توضیحات تکمیلی در صورت نیاز...">{{ old('notes', $personnelRequest->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">حداکثر 1000 کاراکتر</div>
                    </div>

                    {{-- Registration Info --}}
                    <div class="col-12">
                        <div class="alert alert-light border">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>منبع ثبت:</strong>
                                    @if($personnelRequest->registration_source === 'bale_bot')
                                        <span class="badge bg-info">بات بله</span>
                                    @elseif($personnelRequest->registration_source === 'web')
                                        <span class="badge bg-primary">وب</span>
                                    @else
                                        <span class="badge bg-secondary">دستی</span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <strong>تاریخ ثبت:</strong>
                                    {{ jdate($personnelRequest->created_at)->format('Y/m/d H:i') }}
                                </div>
                                <div class="col-md-4">
                                    <strong>آخرین ویرایش:</strong>
                                    {{ jdate($personnelRequest->updated_at)->format('Y/m/d H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> ذخیره تغییرات
                    </button>
                    <a href="{{ route('personnel-requests.show', $personnelRequest) }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Warning --}}
    <div class="card mt-3 border-warning">
        <div class="card-header bg-warning text-dark">
            <i class="bi bi-exclamation-triangle"></i> توجه
        </div>
        <div class="card-body">
            <p class="mb-0">
                <strong>تذکر مهم:</strong>
                فقط درخواست‌های در حال بررسی (pending) قابل ویرایش هستند.
                پس از تأیید یا رد، امکان ویرایش وجود ندارد.
            </p>
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
