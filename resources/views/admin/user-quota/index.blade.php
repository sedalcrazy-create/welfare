@extends('layouts.app')

@section('title', 'مدیریت سهمیه کاربران')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">مدیریت سهمیه کاربران</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>نام</th>
                            <th>ایمیل</th>
                            <th>نقش</th>
                            <th>سهمیه کل</th>
                            <th>استفاده شده</th>
                            <th>باقیمانده</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td><code>{{ $user->email }}</code></td>
                                <td>
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
                                </td>
                                <td>
                                    <strong class="text-primary">{{ $user->quota_total }}</strong>
                                </td>
                                <td>
                                    <span class="text-danger">{{ $user->quota_used }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success" style="font-size: 1rem;">
                                        {{ $user->quota_remaining }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-secondary">غیرفعال</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button"
                                                class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editQuotaModal{{ $user->id }}">
                                            <i class="bi bi-pencil"></i> ویرایش سهمیه
                                        </button>
                                        @if($user->quota_used > 0)
                                            <form method="POST"
                                                  action="{{ route('admin.user-quota.reset-used', $user) }}"
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
                                </td>
                            </tr>

                            {{-- Edit Quota Modal --}}
                            <div class="modal fade" id="editQuotaModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.user-quota.update', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="bi bi-pencil"></i> ویرایش سهمیه: {{ $user->name }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle"></i>
                                                    <strong>توجه:</strong> تغییر سهمیه کل بر روی سهمیه باقیمانده تأثیر می‌گذارد.
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">سهمیه فعلی</label>
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <div class="text-center p-2 bg-light rounded">
                                                                <small>کل</small>
                                                                <h5 class="mb-0">{{ $user->quota_total }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="text-center p-2 bg-light rounded">
                                                                <small>استفاده شده</small>
                                                                <h5 class="mb-0 text-danger">{{ $user->quota_used }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="text-center p-2 bg-light rounded">
                                                                <small>باقیمانده</small>
                                                                <h5 class="mb-0 text-success">{{ $user->quota_remaining }}</h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="quota_total{{ $user->id }}" class="form-label">
                                                        سهمیه کل جدید <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number"
                                                           class="form-control"
                                                           id="quota_total{{ $user->id }}"
                                                           name="quota_total"
                                                           value="{{ old('quota_total', $user->quota_total) }}"
                                                           required
                                                           min="0"
                                                           max="1000">
                                                    <div class="form-text">حداکثر: 1000 معرفی‌نامه</div>
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
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    نمایش {{ $users->firstItem() ?? 0 }} تا {{ $users->lastItem() ?? 0 }} از {{ $users->total() }} کاربر
                </div>
                <nav aria-label="صفحه‌بندی">
                    <ul class="pagination mb-0">
                        @if ($users->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        @endif

                        @php
                            $start = max($users->currentPage() - 2, 1);
                            $end = min($start + 4, $users->lastPage());
                            $start = max($end - 4, 1);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $users->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $i }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $users->url($i) }}">{{ $i }}</a>
                                </li>
                            @endif
                        @endfor

                        @if($end < $users->lastPage())
                            @if($end < $users->lastPage() - 1)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->url($users->lastPage()) }}">{{ $users->lastPage() }}</a>
                            </li>
                        @endif

                        @if ($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <i class="bi bi-info-circle"></i> راهنما
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>سهمیه کل:</strong> تعداد کل معرفی‌نامه‌هایی که کاربر می‌تواند صادر کند</li>
                <li><strong>استفاده شده:</strong> تعداد معرفی‌نامه‌های صادر شده توسط کاربر</li>
                <li><strong>باقیمانده:</strong> سهمیه کل - استفاده شده (محاسبه خودکار)</li>
                <li><strong>ریست:</strong> سهمیه استفاده شده را به 0 برمی‌گرداند (برای شروع دوره جدید)</li>
                <li><strong>حداکثر سهمیه:</strong> 1000 معرفی‌نامه به ازای هر کاربر</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pagination {
        gap: 5px;
    }

    .pagination .page-link {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        color: #374151;
        padding: 8px 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
        transform: translateY(-2px);
    }

    .pagination .page-item.active .page-link {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
        font-weight: 700;
    }

    .pagination .page-item.disabled .page-link {
        background: #f9fafb;
        color: #9ca3af;
        border-color: #e5e7eb;
    }
</style>
@endpush
