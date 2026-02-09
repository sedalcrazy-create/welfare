@extends('layouts.app')

@section('title', 'ویرایش دوره ' . $period->code)

@section('content')
<div class="page-header">
    <h1><i class="bi bi-pencil-square"></i> ویرایش دوره</h1>
    <a href="{{ route('periods.index', ['center_id' => $period->center_id]) }}" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i>
        بازگشت
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-calendar-range"></i>
        ویرایش: {{ $period->code }}
    </div>
    <div class="card-body">
        <form action="{{ route('periods.update', $period) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">مرکز رفاهی <span class="text-danger">*</span></label>
                    <select name="center_id" id="center_id" class="form-control @error('center_id') is-invalid @enderror" required>
                        <option value="">انتخاب کنید...</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}"
                                data-stay="{{ $center->stay_duration }}"
                                data-capacity="{{ $center->unit_count }}"
                                {{ old('center_id', $period->center_id) == $center->id ? 'selected' : '' }}>
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
                            <option value="{{ $season->id }}" {{ old('season_id', $period->season_id) == $season->id ? 'selected' : '' }}>
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
                           value="{{ old('jalali_start_date', $period->jalali_start_date) }}"
                           placeholder="1404/01/01" required>
                    @error('jalali_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاریخ پایان <span class="text-danger">*</span></label>
                    <input type="text" name="jalali_end_date" id="end_date"
                           class="form-control datepicker @error('jalali_end_date') is-invalid @enderror"
                           value="{{ old('jalali_end_date', $period->jalali_end_date) }}"
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
                           value="{{ old('capacity', $period->capacity) }}" min="1" id="capacity" required>
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">وضعیت <span class="text-danger">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="draft" {{ old('status', $period->status) === 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                        <option value="open" {{ old('status', $period->status) === 'open' ? 'selected' : '' }}>باز</option>
                        <option value="closed" {{ old('status', $period->status) === 'closed' ? 'selected' : '' }}>بسته</option>
                        <option value="completed" {{ old('status', $period->status) === 'completed' ? 'selected' : '' }}>تکمیل شده</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-row mb-4">
                <div class="stat-item">
                    <i class="bi bi-hash"></i>
                    <span>کد: <strong>{{ $period->code }}</strong></span>
                </div>
                <div class="stat-item">
                    <i class="bi bi-bookmark"></i>
                    <span>رزرو شده: <strong>{{ $period->reserved_count }} / {{ $period->capacity }}</strong></span>
                </div>
                <div class="stat-item">
                    <i class="bi bi-calendar"></i>
                    <span>ایجاد: <strong>{{ jdate($period->created_at)->format('Y/m/d') }}</strong></span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i>
                    ذخیره تغییرات
                </button>
                <a href="{{ route('periods.show', $period) }}" class="btn btn-info">
                    <i class="bi bi-eye"></i>
                    مشاهده
                </a>
                <a href="{{ route('periods.index', ['center_id' => $period->center_id]) }}" class="btn btn-secondary">
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
    .btn-info { background: var(--info); color: white; }

    .stats-row {
        display: flex;
        gap: 30px;
        padding: 15px 20px;
        background: var(--bg-light);
        border-radius: 12px;
        flex-wrap: wrap;
        border: 1px solid var(--border-color);
    }
    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
    }
    .stat-item i { color: var(--primary); }
    .stat-item strong { color: var(--text-dark); }
</style>
@endpush
