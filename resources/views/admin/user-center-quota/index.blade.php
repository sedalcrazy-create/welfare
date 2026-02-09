@extends('layouts.app')

@section('title', 'مدیریت سهمیه به تفکیک مرکز')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="bi bi-ticket-perforated"></i>
            مدیریت سهمیه به تفکیک مرکز
        </h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="bi bi-info-circle"></i> راهنما
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>سهمیه به تفکیک مرکز:</strong> هر کاربر می‌تواند سهمیه جداگانه برای هر مرکز رفاهی داشته باشد</li>
                <li><strong>مثال:</strong> یوزر A می‌تواند 2 سهمیه از مشهد، 3 سهمیه از بابلسر و 2 سهمیه از چادگان داشته باشد</li>
                <li><strong>ویرایش:</strong> روی دکمه "ویرایش" کلیک کنید تا سهمیه مرکز مورد نظر را تغییر دهید</li>
                <li><strong>ریست:</strong> برای بازگشت سهمیه استفاده شده به 0 (شروع دوره جدید)</li>
            </ul>
        </div>
    </div>

    @foreach($users as $user)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $user->name }}</strong>
                <span class="text-muted">({{ $user->email }})</span>
                @foreach($user->roles as $role)
                    @php
                        $roleColors = [
                            'super_admin' => 'danger',
                            'admin' => 'primary',
                            'provincial_admin' => 'info',
                            'operator' => 'secondary',
                            'user' => 'success'
                        ];
                        $color = $roleColors[$role->name] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }}">{{ $role->name }}</span>
                @endforeach
            </div>
            <div>
                @if(!$user->is_active)
                    <span class="badge bg-secondary">غیرفعال</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($centers as $center)
                    @php
                        $quota = $user->centerQuotas->where('center_id', $center->id)->first();
                        $total = $quota ? $quota->quota_total : 0;
                        $used = $quota ? $quota->quota_used : 0;
                        $remaining = $total - $used;
                        $percentage = $total > 0 ? round(($used / $total) * 100) : 0;
                    @endphp
                    <div class="col-md-4">
                        <div class="card h-100 quota-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-building"></i>
                                        {{ $center->name }}
                                    </h6>
                                </div>

                                <div class="quota-stats mb-3">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="stat-box">
                                                <div class="stat-value text-primary">{{ $total }}</div>
                                                <div class="stat-label">کل</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-box">
                                                <div class="stat-value text-danger">{{ $used }}</div>
                                                <div class="stat-label">استفاده</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="stat-box">
                                                <div class="stat-value text-success">{{ $remaining }}</div>
                                                <div class="stat-label">باقی</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $percentage > 80 ? 'danger' : ($percentage > 50 ? 'warning' : 'success') }}"
                                         role="progressbar"
                                         style="width: {{ $percentage }}%"
                                         aria-valuenow="{{ $percentage }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button"
                                            class="btn btn-sm btn-primary flex-fill"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editQuotaModal{{ $user->id }}{{ $center->id }}">
                                        <i class="bi bi-pencil"></i> ویرایش
                                    </button>
                                    @if($used > 0)
                                        <form method="POST"
                                              action="{{ route('admin.user-center-quota.reset', [$user, $center]) }}"
                                              style="display: inline;"
                                              onsubmit="return confirm('آیا از بازنشانی سهمیه استفاده شده اطمینان دارید؟')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="bi bi-arrow-counterclockwise"></i> ریست
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editQuotaModal{{ $user->id }}{{ $center->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.user-center-quota.update', [$user, $center]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-pencil"></i>
                                            ویرایش سهمیه: {{ $user->name }} - {{ $center->name }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i>
                                            <strong>وضعیت فعلی:</strong>
                                            کل: {{ $total }} | استفاده شده: {{ $used }} | باقیمانده: {{ $remaining }}
                                        </div>

                                        <div class="mb-3">
                                            <label for="quota_total{{ $user->id }}{{ $center->id }}" class="form-label">
                                                سهمیه کل جدید <span class="text-danger">*</span>
                                            </label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="quota_total{{ $user->id }}{{ $center->id }}"
                                                   name="quota_total"
                                                   value="{{ old('quota_total', $total) }}"
                                                   required
                                                   min="0"
                                                   max="1000">
                                            <div class="form-text">حداکثر: 1000 معرفی‌نامه</div>
                                        </div>

                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            توجه: اگر سهمیه کل را کمتر از استفاده شده قرار دهید، باقیمانده منفی می‌شود.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> ذخیره
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="mt-4 d-flex justify-content-between align-items-center">
        <div class="text-muted">
            نمایش {{ $users->firstItem() ?? 0 }} تا {{ $users->lastItem() ?? 0 }} از {{ $users->total() }} کاربر
        </div>
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .quota-card {
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
    }

    .quota-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    .stat-box {
        padding: 10px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 2px;
    }

    .progress {
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        transition: width 0.6s ease;
    }
</style>
@endpush
