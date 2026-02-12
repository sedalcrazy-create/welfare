@extends('layouts.app')

@section('title', 'مدیریت سهمیه - ' . $user->name)

@section('content')
<div class="page-header">
    <h1>
        <i class="bi bi-ticket-perforated"></i>
        مدیریت سهمیه: {{ $user->name }}
    </h1>
    <a href="{{ route('personnel-requests.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i> بازگشت
    </a>
</div>

<div class="row">
    @foreach($quotaSummary as $quota)
    <div class="col-md-4">
        <div class="stat-card {{ $loop->iteration % 3 == 1 ? 'primary' : ($loop->iteration % 3 == 2 ? 'success' : 'warning') }}">
            <i class="stat-icon bi bi-building"></i>
            <div class="stat-value">{{ $quota->quota_remaining }}</div>
            <div class="stat-label">{{ $quota->center->name }}</div>
            <div class="mt-3">
                <div class="d-flex justify-content-between text-muted" style="font-size: 0.85rem;">
                    <span>کل: {{ $quota->quota_total }}</span>
                    <span>استفاده: {{ $quota->quota_used }}</span>
                </div>
                <div class="mt-2 d-flex gap-2">
                    <button type="button"
                            class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#updateQuotaModal{{ $quota->id }}">
                        <i class="bi bi-pencil"></i> ویرایش
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-success"
                            data-bs-toggle="modal"
                            data-bs-target="#increaseQuotaModal{{ $quota->id }}">
                        <i class="bi bi-plus-circle"></i> افزایش
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-warning"
                            data-bs-toggle="modal"
                            data-bs-target="#decreaseQuotaModal{{ $quota->id }}">
                        <i class="bi bi-dash-circle"></i> کاهش
                    </button>
                    @if($quota->quota_used > 0)
                    <form method="POST" action="{{ route('admin.quotas.reset', $quota) }}" style="display: inline;">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('آیا از بازنشانی سهمیه استفاده شده اطمینان دارید؟')">
                            <i class="bi bi-arrow-counterclockwise"></i> ریست
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Update Quota Modal --}}
    <div class="modal fade" id="updateQuotaModal{{ $quota->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 16px;">
                <form method="POST" action="{{ route('admin.quotas.update', $quota) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                        <h5 class="modal-title">ویرایش سهمیه - {{ $quota->center->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            سهمیه فعلی: {{ $quota->quota_total }} | استفاده شده: {{ $quota->quota_used }} | باقیمانده: {{ $quota->quota_remaining }}
                        </div>
                        <div class="mb-3">
                            <label for="quota_total{{ $quota->id }}" class="form-label">
                                سهمیه کل جدید <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="quota_total{{ $quota->id }}"
                                   name="quota_total"
                                   value="{{ $quota->quota_total }}"
                                   required
                                   min="{{ $quota->quota_used }}"
                                   max="1000">
                            <div class="form-text">حداقل باید برابر با سهمیه استفاده شده ({{ $quota->quota_used }}) باشد</div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> ذخیره
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Increase Quota Modal --}}
    <div class="modal fade" id="increaseQuotaModal{{ $quota->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 16px;">
                <form method="POST" action="{{ route('admin.quotas.increase', $quota) }}">
                    @csrf
                    <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                        <h5 class="modal-title">افزایش سهمیه - {{ $quota->center->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="bi bi-arrow-up-circle"></i>
                            سهمیه فعلی: {{ $quota->quota_total }}
                        </div>
                        <div class="mb-3">
                            <label for="amount_increase{{ $quota->id }}" class="form-label">
                                مقدار افزایش <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="amount_increase{{ $quota->id }}"
                                   name="amount"
                                   required
                                   min="1"
                                   max="100"
                                   value="10">
                            <div class="form-text">حداکثر 100 عدد در هر بار</div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> افزایش
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Decrease Quota Modal --}}
    <div class="modal fade" id="decreaseQuotaModal{{ $quota->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 16px;">
                <form method="POST" action="{{ route('admin.quotas.decrease', $quota) }}">
                    @csrf
                    <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                        <h5 class="modal-title">کاهش سهمیه - {{ $quota->center->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-arrow-down-circle"></i>
                            سهمیه فعلی: {{ $quota->quota_total }} | حداکثر قابل کاهش: {{ $quota->quota_total - $quota->quota_used }}
                        </div>
                        <div class="mb-3">
                            <label for="amount_decrease{{ $quota->id }}" class="form-label">
                                مقدار کاهش <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="amount_decrease{{ $quota->id }}"
                                   name="amount"
                                   required
                                   min="1"
                                   max="{{ $quota->quota_total - $quota->quota_used }}"
                                   value="10">
                            <div class="form-text">سهمیه نمی‌تواند کمتر از سهمیه استفاده شده باشد</div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-dash-circle"></i> کاهش
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Allocate New Center Quota --}}
<div class="card">
    <div class="card-header">
        <i class="bi bi-plus-circle"></i> تخصیص سهمیه جدید برای مرکز
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.quotas.allocate', $user) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="center_id" class="form-label">
                        انتخاب مرکز <span class="text-danger">*</span>
                    </label>
                    <select class="form-control @error('center_id') is-invalid @enderror"
                            id="center_id"
                            name="center_id"
                            required>
                        <option value="">-- انتخاب کنید --</option>
                        @foreach($centers as $center)
                            @if(!$quotaSummary->contains('center_id', $center->id))
                                <option value="{{ $center->id }}">{{ $center->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('center_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="quota_total" class="form-label">
                        مقدار سهمیه <span class="text-danger">*</span>
                    </label>
                    <input type="number"
                           class="form-control @error('quota_total') is-invalid @enderror"
                           id="quota_total"
                           name="quota_total"
                           required
                           min="1"
                           max="1000"
                           value="50">
                    @error('quota_total')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle"></i> تخصیص
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Info Card --}}
<div class="card">
    <div class="card-header" style="background: var(--info); color: white;">
        <i class="bi bi-info-circle"></i> راهنما
    </div>
    <div class="card-body">
        <ul class="mb-0">
            <li><strong>سهمیه کل:</strong> تعداد کل معرفی‌نامه‌هایی که این کاربر می‌تواند برای این مرکز صادر کند</li>
            <li><strong>استفاده شده:</strong> تعداد معرفی‌نامه‌های صادر شده</li>
            <li><strong>باقیمانده:</strong> سهمیه قابل استفاده (محاسبه خودکار)</li>
            <li><strong>افزایش/کاهش:</strong> برای تغییرات سریع سهمیه</li>
            <li><strong>ریست:</strong> سهمیه استفاده شده را صفر می‌کند (برای شروع دوره جدید)</li>
            <li>هر کاربر می‌تواند برای هر مرکز سهمیه جداگانه داشته باشد</li>
        </ul>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
