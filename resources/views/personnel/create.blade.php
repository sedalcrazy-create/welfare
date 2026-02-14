@extends('layouts.app')

@section('title', 'افزودن پرسنل جدید')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Header --}}
            <div class="mb-4">
                <a href="{{ route('personnel.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="bi bi-arrow-right"></i> بازگشت
                </a>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-person-plus"></i> افزودن پرسنل جدید
                </h2>
                <p class="text-muted mb-0">اطلاعات پرسنل و همراهان را وارد کنید</p>
            </div>

            <form method="POST" action="{{ route('personnel.store') }}">
                @csrf

                {{-- Personal Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle"></i> اطلاعات شخصی
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    نام کامل <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                       value="{{ old('full_name') }}" required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    کد ملی <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="national_code" class="form-control @error('national_code') is-invalid @enderror"
                                       value="{{ old('national_code') }}" maxlength="10" required>
                                @error('national_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    کد پرسنلی <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="employee_code" class="form-control @error('employee_code') is-invalid @enderror"
                                       value="{{ old('employee_code') }}" required>
                                @error('employee_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    جنسیت
                                </label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">انتخاب کنید...</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>مرد</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>زن</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">نام</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">نام خانوادگی</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">نام پدر</label>
                                <input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">تاریخ تولد (شمسی)</label>
                                <input type="text" name="birth_date" class="form-control"
                                       placeholder="1370/01/01" value="{{ old('birth_date') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">شماره تماس</label>
                                <input type="text" name="phone" class="form-control"
                                       placeholder="09123456789" value="{{ old('phone') }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">ایمیل</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Employment Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-briefcase"></i> اطلاعات استخدامی
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    استان <span class="text-danger">*</span>
                                </label>
                                <select name="province_id" class="form-select @error('province_id') is-invalid @enderror" required>
                                    <option value="">انتخاب کنید...</option>
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

                            <div class="col-md-6">
                                <label class="form-label fw-bold">وضعیت استخدام</label>
                                <select name="employment_status" class="form-select">
                                    <option value="active" {{ old('employment_status') == 'active' ? 'selected' : '' }}>شاغل</option>
                                    <option value="retired" {{ old('employment_status') == 'retired' ? 'selected' : '' }}>بازنشسته</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">سابقه خدمت (سال)</label>
                                <input type="number" name="service_years" class="form-control"
                                       min="0" value="{{ old('service_years', 0) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">تاریخ استخدام</label>
                                <input type="text" name="hire_date" class="form-control"
                                       placeholder="1390/01/01" value="{{ old('hire_date') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">محل خدمت</label>
                                <input type="text" name="service_location" class="form-control" value="{{ old('service_location') }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">دپارتمان</label>
                                <input type="text" name="department" class="form-control" value="{{ old('department') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Veteran Status --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-fill-check"></i> وضعیت ایثارگری
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_isargar" value="1"
                                           class="form-check-input" id="isIsargar"
                                           {{ old('is_isargar') ? 'checked' : '' }}
                                           onchange="toggleIsargarFields()">
                                    <label class="form-check-label fw-bold" for="isIsargar">
                                        ایثارگر است
                                    </label>
                                </div>
                            </div>

                            <div id="isargar-fields" style="display: {{ old('is_isargar') ? 'block' : 'none' }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">نوع ایثارگری</label>
                                        <select name="isargar_type" class="form-select">
                                            <option value="">انتخاب کنید...</option>
                                            <option value="veteran" {{ old('isargar_type') == 'veteran' ? 'selected' : '' }}>جانباز</option>
                                            <option value="freed_pow" {{ old('isargar_type') == 'freed_pow' ? 'selected' : '' }}>آزاده</option>
                                            <option value="martyr_child" {{ old('isargar_type') == 'martyr_child' ? 'selected' : '' }}>فرزند شهید</option>
                                            <option value="martyr_spouse" {{ old('isargar_type') == 'martyr_spouse' ? 'selected' : '' }}>همسر شهید</option>
                                            <option value="martyr_parent" {{ old('isargar_type') == 'martyr_parent' ? 'selected' : '' }}>والدین شهید</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">درصد جانبازی</label>
                                        <input type="number" name="isargar_percentage" class="form-control"
                                               min="0" max="100" value="{{ old('isargar_percentage') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('personnel.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-x-circle"></i> انصراف
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle"></i> ذخیره
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleIsargarFields() {
    const checkbox = document.getElementById('isIsargar');
    const fields = document.getElementById('isargar-fields');
    fields.style.display = checkbox.checked ? 'block' : 'none';
}
</script>
@endsection
