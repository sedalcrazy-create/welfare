@extends('layouts.app')

@section('title', 'افزودن دوره')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-plus-circle"></i> افزودن دوره جدید</h1>
    <a href="{{ route('periods.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i>
        بازگشت
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-calendar-range"></i>
        اطلاعات دوره
    </div>
    <div class="card-body">
        <form action="{{ route('periods.store') }}" method="POST">
            @csrf

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">مرکز رفاهی <span class="text-danger">*</span></label>
                    <select name="center_id" id="center_id" class="form-control @error('center_id') is-invalid @enderror" required>
                        <option value="">انتخاب کنید...</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}"
                                data-stay="{{ $center->stay_duration }}"
                                data-capacity="{{ $center->unit_count }}"
                                {{ old('center_id', $selectedCenter) == $center->id ? 'selected' : '' }}>
                                {{ $center->name }} ({{ $center->city }})
                            </option>
                        @endforeach
                    </select>
                    @error('center_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">فصل (اختیاری)</label>
                    <select name="season_id" id="season_id" class="form-control @error('season_id') is-invalid @enderror">
                        <option value="">بدون فصل</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}" {{ old('season_id') == $season->id ? 'selected' : '' }}>
                                {{ $season->name }} ({{ $season->getTypeLabel() }})
                            </option>
                        @endforeach
                    </select>
                    @error('season_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">تاریخ شروع <span class="text-danger">*</span></label>
                    <input type="text" name="jalali_start_date" id="start_date"
                           class="form-control datepicker @error('jalali_start_date') is-invalid @enderror"
                           value="{{ old('jalali_start_date') }}"
                           placeholder="1404/01/01" required>
                    @error('jalali_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاریخ پایان <span class="text-danger">*</span></label>
                    <input type="text" name="jalali_end_date" id="end_date"
                           class="form-control datepicker @error('jalali_end_date') is-invalid @enderror"
                           value="{{ old('jalali_end_date') }}"
                           placeholder="1404/01/05" required>
                    @error('jalali_end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">ظرفیت (تعداد واحد) <span class="text-danger">*</span></label>
                    <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror"
                           value="{{ old('capacity') }}" min="1" id="capacity" required>
                    <small class="text-muted" id="capacity-hint"></small>
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">وضعیت <span class="text-danger">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                        <option value="open" {{ old('status') === 'open' ? 'selected' : '' }}>باز (آماده ثبت‌نام)</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box mb-4" id="period-info" style="display: none;">
                <div class="info-box-header">
                    <i class="bi bi-info-circle"></i>
                    اطلاعات دوره
                </div>
                <div class="info-box-content">
                    <div class="info-row">
                        <span>مدت اقامت:</span>
                        <strong id="stay-duration">-</strong>
                    </div>
                    <div class="info-row">
                        <span>حداکثر ظرفیت مرکز:</span>
                        <strong id="max-capacity">-</strong>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i>
                    ذخیره دوره
                </button>
                <a href="{{ route('periods.index') }}" class="btn btn-secondary">
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
    .is-invalid { border-color: var(--danger) !important; }
    .invalid-feedback { display: block; color: var(--danger); font-size: 0.85rem; margin-top: 5px; }
    .text-danger { color: var(--danger); }
    .form-actions { display: flex; gap: 12px; padding-top: 20px; border-top: 1px solid var(--border-color); }

    .info-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        overflow: hidden;
    }
    .info-box-header {
        background: #dbeafe;
        padding: 12px 16px;
        font-weight: 600;
        color: #1e40af;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-box-content {
        padding: 16px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #bfdbfe;
    }
    .info-row:last-child { border-bottom: none; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const centerSelect = document.getElementById('center_id');
    const capacityInput = document.getElementById('capacity');
    const periodInfo = document.getElementById('period-info');
    const stayDuration = document.getElementById('stay-duration');
    const maxCapacity = document.getElementById('max-capacity');
    const capacityHint = document.getElementById('capacity-hint');

    centerSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (this.value) {
            const stay = selected.dataset.stay;
            const cap = selected.dataset.capacity;

            stayDuration.textContent = stay + ' شب';
            maxCapacity.textContent = cap + ' واحد';
            capacityHint.textContent = 'حداکثر: ' + cap + ' واحد';
            capacityInput.max = cap;
            if (!capacityInput.value) {
                capacityInput.value = cap;
            }
            periodInfo.style.display = 'block';
        } else {
            periodInfo.style.display = 'none';
            capacityHint.textContent = '';
        }
    });

    // Trigger on load if center is selected
    if (centerSelect.value) {
        centerSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
