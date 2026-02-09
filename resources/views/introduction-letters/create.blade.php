@extends('layouts.app')

@section('title', 'صدور معرفی‌نامه جدید')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">صدور معرفی‌نامه جدید</h2>
        <a href="{{ route('introduction-letters.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-right"></i> بازگشت به لیست
        </a>
    </div>

    {{-- Quota Alert --}}
    @if(auth()->user()->quota_remaining <= 0)
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>توجه:</strong> سهمیه شما تمام شده است. امکان صدور معرفی‌نامه جدید وجود ندارد.
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>سهمیه باقیمانده شما:</strong>
            <span class="badge bg-success">{{ auth()->user()->quota_remaining }}</span> معرفی‌نامه
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('introduction-letters.store') }}">
                @csrf

                <div class="row g-3">
                    {{-- Personnel Selection --}}
                    <div class="col-md-6">
                        <label for="personnel_id" class="form-label">
                            انتخاب پرسنل <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('personnel_id') is-invalid @enderror"
                                id="personnel_id"
                                name="personnel_id"
                                required
                                onchange="updatePersonnelInfo(this)">
                            <option value="">-- انتخاب کنید --</option>
                            @if($personnel)
                                <option value="{{ $personnel->id }}" selected>
                                    {{ $personnel->full_name }} - {{ $personnel->national_code }}
                                </option>
                            @else
                                @foreach($approvedPersonnel as $p)
                                    <option value="{{ $p->id }}"
                                            data-name="{{ $p->full_name }}"
                                            data-national-code="{{ $p->national_code }}"
                                            data-phone="{{ $p->phone }}"
                                            data-family-count="{{ $p->family_count }}"
                                            data-preferred-center="{{ $p->preferred_center_id }}"
                                            {{ old('personnel_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->full_name }} - {{ $p->national_code }}
                                        @if($p->phone)
                                            - {{ $p->phone }}
                                        @endif
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('personnel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">فقط پرسنل تأیید شده نمایش داده می‌شود</div>
                    </div>

                    {{-- Center Selection --}}
                    <div class="col-md-6">
                        <label for="center_id" class="form-label">
                            مرکز رفاهی <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('center_id') is-invalid @enderror"
                                id="center_id"
                                name="center_id"
                                required>
                            <option value="">-- انتخاب کنید --</option>
                            @foreach($centers as $center)
                                <option value="{{ $center->id }}"
                                    {{ old('center_id', $personnel?->preferred_center_id) == $center->id ? 'selected' : '' }}>
                                    {{ $center->name }} - {{ $center->city }}
                                    ({{ $center->type === 'religious' ? 'مذهبی' : ($center->type === 'beach' ? 'ساحلی' : 'کوهستانی') }})
                                </option>
                            @endforeach
                        </select>
                        @error('center_id')
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
                               value="{{ old('family_count', $personnel?->family_count ?? 1) }}"
                               required
                               min="1"
                               max="10">
                        @error('family_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">حداقل 1، حداکثر 10 نفر</div>
                    </div>

                    {{-- Assigned User --}}
                    <div class="col-12">
                        <label for="assigned_user_id" class="form-label">
                            <i class="bi bi-person-circle"></i> از سهمیه کدام کاربر کم شود؟
                        </label>
                        <select class="form-select @error('assigned_user_id') is-invalid @enderror"
                                id="assigned_user_id"
                                name="assigned_user_id"
                                onchange="updateUserQuota()">
                            <option value="{{ auth()->id() }}" selected>
                                خودم ({{ auth()->user()->name }})
                            </option>
                            @foreach($users as $u)
                                @if($u->id !== auth()->id())
                                    <option value="{{ $u->id }}"
                                            data-quotas="{{ json_encode($u->centerQuotas->pluck('quota_remaining', 'center_id')) }}">
                                        {{ $u->name }} ({{ $u->email }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('assigned_user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            پیش‌فرض: سهمیه از حساب خودتان کم می‌شود.
                            می‌توانید کاربر دیگری را انتخاب کنید.
                        </div>
                    </div>

                    {{-- Quota Display --}}
                    <div class="col-12" id="quota-display">
                        <div class="alert alert-info">
                            <strong><i class="bi bi-info-circle"></i> سهمیه موجود:</strong>
                            <span id="quota-info">
                                لطفاً مرکز را انتخاب کنید...
                            </span>
                        </div>
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
                                  placeholder="توضیحات تکمیلی...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Personnel Info Display (Dynamic) --}}
                    <div class="col-12" id="personnel-info" style="display: none;">
                        <div class="alert alert-light border">
                            <h6 class="mb-2"><i class="bi bi-person-badge"></i> اطلاعات پرسنل:</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>نام:</strong>
                                    <span id="info-name"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>کد ملی:</strong>
                                    <code id="info-national-code"></code>
                                </div>
                                <div class="col-md-3">
                                    <strong>شماره تماس:</strong>
                                    <span id="info-phone"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>تعداد خانواده:</strong>
                                    <span id="info-family-count"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="mt-4 d-flex gap-2">
                    <button type="submit"
                            class="btn btn-primary"
                            {{ auth()->user()->quota_remaining <= 0 ? 'disabled' : '' }}>
                        <i class="bi bi-check-circle"></i> صدور معرفی‌نامه
                    </button>
                    <a href="{{ route('introduction-letters.index') }}" class="btn btn-secondary">
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
                <li>فقط پرسنلی که درخواستشان تأیید شده است، قابل انتخاب هستند</li>
                <li>کد معرفی‌نامه به صورت خودکار تولید می‌شود (مثال: MHD-0411-0001)</li>
                <li>هر معرفی‌نامه، یک واحد از سهمیه شما کم می‌کند</li>
                <li>در صورت لغو معرفی‌نامه، سهمیه به حساب شما بازگردانده می‌شود</li>
                <li>معرفی‌نامه بعد از صدور، قابل چاپ و ارسال خواهد بود</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // User quotas data
    const userQuotas = @json($users->mapWithKeys(function($u) {
        return [$u->id => $u->centerQuotas->pluck('quota_remaining', 'center_id')];
    }));

    // Current user quotas
    const currentUserQuotas = @json(auth()->user()->centerQuotas->pluck('quota_remaining', 'center_id'));
    userQuotas[{{ auth()->id() }}] = currentUserQuotas;

    function updatePersonnelInfo(select) {
        const option = select.options[select.selectedIndex];

        if (option.value) {
            // Show info panel
            document.getElementById('personnel-info').style.display = 'block';

            // Update fields
            document.getElementById('info-name').textContent = option.dataset.name || '-';
            document.getElementById('info-national-code').textContent = option.dataset.nationalCode || '-';
            document.getElementById('info-phone').textContent = option.dataset.phone || '-';
            document.getElementById('info-family-count').textContent = option.dataset.familyCount || '-';

            // Auto-fill form fields
            if (option.dataset.familyCount) {
                document.getElementById('family_count').value = option.dataset.familyCount;
            }
            if (option.dataset.preferredCenter) {
                document.getElementById('center_id').value = option.dataset.preferredCenter;
                updateQuotaDisplay(); // Update quota when center changes
            }
        } else {
            document.getElementById('personnel-info').style.display = 'none';
        }
    }

    function updateUserQuota() {
        updateQuotaDisplay();
    }

    function updateQuotaDisplay() {
        const userId = document.getElementById('assigned_user_id').value;
        const centerId = document.getElementById('center_id').value;

        if (!userId || !centerId) {
            document.getElementById('quota-info').textContent = 'لطفاً مرکز و کاربر را انتخاب کنید...';
            return;
        }

        const quotas = userQuotas[userId] || {};
        const remaining = quotas[centerId] || 0;

        const centerName = document.getElementById('center_id').options[document.getElementById('center_id').selectedIndex].text;
        const userName = document.getElementById('assigned_user_id').options[document.getElementById('assigned_user_id').selectedIndex].text;

        if (remaining > 0) {
            document.getElementById('quota-info').innerHTML = `
                <span class="text-success">
                    <i class="bi bi-check-circle"></i>
                    ${userName} برای ${centerName}: <strong>${remaining}</strong> معرفی‌نامه باقیمانده دارد ✓
                </span>
            `;
            document.querySelector('button[type="submit"]').disabled = false;
        } else {
            document.getElementById('quota-info').innerHTML = `
                <span class="text-danger">
                    <i class="bi bi-x-circle"></i>
                    ${userName} برای ${centerName} سهمیه‌ای ندارد!
                </span>
            `;
            document.querySelector('button[type="submit"]').disabled = true;
        }
    }

    // Trigger on page load if personnel is pre-selected
    window.addEventListener('DOMContentLoaded', function() {
        const personnelSelect = document.getElementById('personnel_id');
        if (personnelSelect.value) {
            updatePersonnelInfo(personnelSelect);
        }

        // Add event listener to center select
        document.getElementById('center_id').addEventListener('change', updateQuotaDisplay);
    });
</script>
@endpush
@endsection
