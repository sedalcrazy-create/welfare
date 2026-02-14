@extends('layouts.app')

@section('title', 'جزئیات کاربر')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-right"></i> بازگشت
            </a>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-person-badge"></i> {{ $user->name }}
            </h2>
            <p class="text-muted mb-0">{{ $user->email }}</p>
        </div>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> ویرایش
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- اطلاعات کاربر --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle"></i> اطلاعات شخصی
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">نام کامل</label>
                            <p class="fw-bold mb-0">{{ $user->name }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">ایمیل</label>
                            <p class="fw-bold mb-0 font-monospace">{{ $user->email }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">نقش</label>
                            <p class="mb-0">
                                @foreach($user->roles as $role)
                                    <span class="badge bg-{{ $role->name === 'super_admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : ($role->name === 'provincial_admin' ? 'success' : 'secondary')) }}">
                                        {{ match($role->name) {
                                            'super_admin' => 'مدیر سیستم',
                                            'admin' => 'ادمین',
                                            'provincial_admin' => 'مدیر استانی',
                                            'operator' => 'اپراتور',
                                            'user' => 'کاربر',
                                            default => $role->name
                                        } }}
                                    </span>
                                @endforeach
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">وضعیت</label>
                            <p class="mb-0">
                                @if($user->is_active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> فعال
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> غیرفعال
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">تاریخ عضویت</label>
                            <p class="fw-bold mb-0">{{ $user->created_at->format('Y/m/d H:i') }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small">آخرین بروزرسانی</label>
                            <p class="fw-bold mb-0">{{ $user->updated_at->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- سهمیه مراکز --}}
            @if($user->centerQuotas->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-pie-chart-fill"></i> سهمیه مراکز
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($user->centerQuotas as $quota)
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $quota->center->name }}</h6>
                                            <hr>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">سهمیه کل:</span>
                                                <strong>{{ $quota->total_quota }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">استفاده شده:</span>
                                                <strong class="text-{{ $quota->used_quota > 0 ? 'warning' : 'muted' }}">{{ $quota->used_quota }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">باقی‌مانده:</span>
                                                <strong class="text-{{ $quota->getRemainingQuota() > 0 ? 'success' : 'danger' }}">
                                                    {{ $quota->getRemainingQuota() }}
                                                </strong>
                                            </div>
                                            <div class="progress mt-2" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $quota->used_quota >= $quota->total_quota ? 'danger' : 'success' }}"
                                                     style="width: {{ $quota->total_quota > 0 ? ($quota->used_quota / $quota->total_quota * 100) : 0 }}%">
                                                    {{ $quota->total_quota > 0 ? round($quota->used_quota / $quota->total_quota * 100) : 0 }}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    این کاربر هنوز سهمیه‌ای ندارد.
                    <a href="{{ route('admin.users.edit', $user) }}" class="alert-link">اضافه کردن سهمیه</a>
                </div>
            @endif
        </div>

        {{-- کارت‌های اطلاعاتی --}}
        <div class="col-md-4">
            {{-- خلاصه سهمیه --}}
            <div class="card border-0 shadow-sm mb-3 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="card-title">
                        <i class="bi bi-trophy-fill"></i> مجموع سهمیه
                    </h6>
                    <h2 class="mb-0">{{ $user->centerQuotas->sum('total_quota') }}</h2>
                    <small class="opacity-75">از {{ $user->centerQuotas->count() }} مرکز</small>
                </div>
            </div>

            {{-- استفاده شده --}}
            <div class="card border-0 shadow-sm mb-3 bg-gradient" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <h6 class="card-title">
                        <i class="bi bi-calendar-check-fill"></i> استفاده شده
                    </h6>
                    <h2 class="mb-0">{{ $user->centerQuotas->sum('used_quota') }}</h2>
                    <small class="opacity-75">رزرو انجام شده</small>
                </div>
            </div>

            {{-- باقی‌مانده --}}
            <div class="card border-0 shadow-sm mb-3 bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <h6 class="card-title">
                        <i class="bi bi-hourglass-split"></i> باقی‌مانده
                    </h6>
                    <h2 class="mb-0">{{ $user->centerQuotas->sum('total_quota') - $user->centerQuotas->sum('used_quota') }}</h2>
                    <small class="opacity-75">قابل استفاده</small>
                </div>
            </div>

            {{-- اقدامات سریع --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning-fill"></i> اقدامات سریع
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.users.edit', $user) }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-pencil text-warning"></i> ویرایش اطلاعات
                        </a>

                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.toggle-status', $user) }}"
                                  method="POST"
                                  onsubmit="return confirm('آیا مطمئن هستید؟')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="list-group-item list-group-item-action w-100 text-start border-0">
                                    <i class="bi bi-{{ $user->is_active ? 'toggle-off' : 'toggle-on' }} text-{{ $user->is_active ? 'secondary' : 'success' }}"></i>
                                    {{ $user->is_active ? 'غیرفعال کردن' : 'فعال کردن' }} کاربر
                                </button>
                            </form>

                            <form action="{{ route('admin.users.destroy', $user) }}"
                                  method="POST"
                                  onsubmit="return confirm('آیا از حذف این کاربر مطمئن هستید؟ این عملیات غیرقابل بازگشت است!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="list-group-item list-group-item-action w-100 text-start border-0 text-danger">
                                    <i class="bi bi-trash"></i> حذف کاربر
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
