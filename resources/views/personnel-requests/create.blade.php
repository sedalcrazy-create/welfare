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

    <form method="POST" action="{{ route('personnel-requests.store') }}">
        @csrf

        {{-- اطلاعات سرپرست اصلی --}}
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-fill"></i> اطلاعات سرپرست اصلی
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Employee Code --}}
                    <div class="col-md-6">
                        <label for="employee_code" class="form-label">
                            کد پرسنلی <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('employee_code') is-invalid @enderror"
                               id="employee_code"
                               name="employee_code"
                               value="{{ old('employee_code') }}"
                               required
                               placeholder="مثال: 12345">
                        @error('employee_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">کد پرسنلی بانک ملی</div>
                    </div>

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
            </div>
        </div>

        {{-- همراهان --}}
        <div class="card mt-3">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people-fill"></i> همراهان (اختیاری)</span>
                <button type="button" id="add-family-member" class="btn btn-sm btn-light">
                    <i class="bi bi-plus-circle"></i> افزودن همراه
                </button>
            </div>
            <div class="card-body">
                <div id="family-members-container">
                    {{-- Old family members will be restored here if validation fails --}}
                    @if(old('family_members'))
                        @foreach(old('family_members') as $index => $member)
                        <div class="family-member-row border rounded p-3 mb-3 bg-light" data-index="{{ $index }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">نام و نام خانوادگی <span class="text-danger">*</span></label>
                                    <input type="text" name="family_members[{{ $index }}][full_name]"
                                           class="form-control @error("family_members.{$index}.full_name") is-invalid @enderror"
                                           value="{{ $member['full_name'] ?? '' }}" required>
                                    @error("family_members.{$index}.full_name")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">نسبت <span class="text-danger">*</span></label>
                                    <select name="family_members[{{ $index }}][relation]"
                                            class="form-select @error("family_members.{$index}.relation") is-invalid @enderror" required>
                                        <option value="">انتخاب کنید</option>
                                        <option value="همسر" {{ ($member['relation'] ?? '') === 'همسر' ? 'selected' : '' }}>همسر</option>
                                        <option value="فرزند" {{ ($member['relation'] ?? '') === 'فرزند' ? 'selected' : '' }}>فرزند</option>
                                        <option value="پدر" {{ ($member['relation'] ?? '') === 'پدر' ? 'selected' : '' }}>پدر</option>
                                        <option value="مادر" {{ ($member['relation'] ?? '') === 'مادر' ? 'selected' : '' }}>مادر</option>
                                        <option value="سایر" {{ ($member['relation'] ?? '') === 'سایر' ? 'selected' : '' }}>سایر</option>
                                    </select>
                                    @error("family_members.{$index}.relation")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">کد ملی <span class="text-danger">*</span></label>
                                    <input type="text" name="family_members[{{ $index }}][national_code]"
                                           class="form-control national-code-input @error("family_members.{$index}.national_code") is-invalid @enderror"
                                           value="{{ $member['national_code'] ?? '' }}" maxlength="10" pattern="[0-9]{10}" required>
                                    @error("family_members.{$index}.national_code")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">تاریخ تولد (اختیاری)</label>
                                    <input type="text" name="family_members[{{ $index }}][birth_date]"
                                           class="form-control persian-date" value="{{ $member['birth_date'] ?? '' }}"
                                           placeholder="1370/01/01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">جنسیت <span class="text-danger">*</span></label>
                                    <select name="family_members[{{ $index }}][gender]"
                                            class="form-select @error("family_members.{$index}.gender") is-invalid @enderror" required>
                                        <option value="">انتخاب</option>
                                        <option value="male" {{ ($member['gender'] ?? '') === 'male' ? 'selected' : '' }}>مرد</option>
                                        <option value="female" {{ ($member['gender'] ?? '') === 'female' ? 'selected' : '' }}>زن</option>
                                    </select>
                                    @error("family_members.{$index}.gender")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-sm remove-member w-100">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>

                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle"></i>
                    می‌توانید تا 10 همراه اضافه کنید. تعداد کل افراد به صورت خودکار محاسبه می‌شود.
                </p>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> ثبت درخواست
                    </button>
                    <a href="{{ route('personnel-requests.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> انصراف
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Help Card --}}
    <div class="card mt-3">
        <div class="card-header bg-info text-white">
            <i class="bi bi-info-circle"></i> راهنما
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li>کد پرسنلی و کد ملی باید معتبر باشند</li>
                <li>کد ملی سرپرست باید یونیک باشد (قبلاً ثبت نشده باشد)</li>
                <li>می‌توانید اطلاعات همراهان (همسر، فرزندان، والدین) را وارد کنید</li>
                <li>تعداد کل افراد (سرپرست + همراهان) به صورت خودکار محاسبه می‌شود</li>
                <li>بعد از ثبت، درخواست در وضعیت "در انتظار بررسی" قرار می‌گیرد</li>
                <li>یک کد پیگیری به صورت خودکار تولید می‌شود (REQ-XXXXXXXX)</li>
                <li>فقط پس از تأیید، امکان صدور معرفی‌نامه وجود دارد</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let memberIndex = {{ old('family_members') ? count(old('family_members')) : 0 }};

    // Add family member
    document.getElementById('add-family-member').addEventListener('click', function() {
        const container = document.getElementById('family-members-container');
        const memberHtml = `
            <div class="family-member-row border rounded p-3 mb-3 bg-light" data-index="${memberIndex}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">نام و نام خانوادگی <span class="text-danger">*</span></label>
                        <input type="text" name="family_members[${memberIndex}][full_name]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">نسبت <span class="text-danger">*</span></label>
                        <select name="family_members[${memberIndex}][relation]" class="form-select" required>
                            <option value="">انتخاب کنید</option>
                            <option value="همسر">همسر</option>
                            <option value="فرزند">فرزند</option>
                            <option value="پدر">پدر</option>
                            <option value="مادر">مادر</option>
                            <option value="سایر">سایر</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">کد ملی <span class="text-danger">*</span></label>
                        <input type="text" name="family_members[${memberIndex}][national_code]"
                               class="form-control national-code-input" maxlength="10" pattern="[0-9]{10}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">تاریخ تولد (اختیاری)</label>
                        <input type="text" name="family_members[${memberIndex}][birth_date]"
                               class="form-control persian-date" placeholder="1370/01/01">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">جنسیت <span class="text-danger">*</span></label>
                        <select name="family_members[${memberIndex}][gender]" class="form-select" required>
                            <option value="">انتخاب</option>
                            <option value="male">مرد</option>
                            <option value="female">زن</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-member w-100">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', memberHtml);
        memberIndex++;

        // Re-attach event listeners
        attachInputListeners();
    });

    // Remove family member
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-member')) {
            e.target.closest('.family-member-row').remove();
        }
    });

    // Validate inputs
    function attachInputListeners() {
        // National code validation
        document.querySelectorAll('.national-code-input, #national_code').forEach(input => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
            });
        });

        // Phone number validation
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    }

    // Initial attach
    attachInputListeners();
</script>
@endpush
@endsection
