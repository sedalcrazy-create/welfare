@extends('layouts.app')

@section('title', 'لیست درخواست‌ها')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">لیست درخواست‌ها</h2>
        <a href="{{ route('personnel-requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> ثبت درخواست جدید
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('personnel-requests.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select">
                        <option value="">همه</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>در انتظار</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>تأیید شده</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>رد شده</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">مرکز</label>
                    <select name="center_id" class="form-select">
                        <option value="">همه</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}" {{ request('center_id') == $center->id ? 'selected' : '' }}>
                                {{ $center->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">منبع ثبت</label>
                    <select name="source" class="form-select">
                        <option value="">همه</option>
                        <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>دستی</option>
                        <option value="bale_bot" {{ request('source') === 'bale_bot' ? 'selected' : '' }}>بات بله</option>
                        <option value="web" {{ request('source') === 'web' ? 'selected' : '' }}>وب</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">جستجو</label>
                    <input type="text" name="search" class="form-control" placeholder="نام، کد ملی، کد پیگیری" value="{{ request('search') }}">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> جستجو
                    </button>
                    <a href="{{ route('personnel-requests.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> پاک کردن فیلترها
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="card">
        <div class="card-body">
            @if($requests->isEmpty())
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle fs-3"></i>
                    <p class="mb-0 mt-2">درخواستی یافت نشد</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>کد پیگیری</th>
                                <th>نام و نام خانوادگی</th>
                                <th>کد ملی</th>
                                <th>تعداد خانواده</th>
                                <th>مرکز مورد نظر</th>
                                <th>منبع</th>
                                <th>وضعیت</th>
                                <th>تاریخ ثبت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    <td>
                                        <code>{{ $request->tracking_code }}</code>
                                    </td>
                                    <td>{{ $request->full_name }}</td>
                                    <td dir="ltr" class="text-start">{{ $request->national_code }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $request->family_count }} نفر</span>
                                    </td>
                                    <td>{{ $request->preferredCenter?->name ?? '-' }}</td>
                                    <td>
                                        @if($request->registration_source === 'bale_bot')
                                            <span class="badge bg-primary">بات بله</span>
                                        @elseif($request->registration_source === 'manual')
                                            <span class="badge bg-secondary">دستی</span>
                                        @else
                                            <span class="badge bg-info">وب</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($request->status === 'pending')
                                            <span class="badge bg-warning text-dark">در انتظار</span>
                                        @elseif($request->status === 'approved')
                                            <span class="badge bg-success">تأیید شده</span>
                                        @else
                                            <span class="badge bg-danger">رد شده</span>
                                        @endif
                                    </td>
                                    <td dir="ltr" class="text-start">{{ jdate($request->created_at)->format('Y/m/d H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('personnel-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($request->status === 'pending')
                                                <a href="{{ route('personnel-requests.edit', $request) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        نمایش {{ $requests->firstItem() ?? 0 }} تا {{ $requests->lastItem() ?? 0 }} از {{ $requests->total() }} نتیجه
                    </div>
                    <nav aria-label="صفحه‌بندی">
                        <ul class="pagination mb-0">
                            {{-- Previous Page Link --}}
                            @if ($requests->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $requests->previousPageUrl() }}&status={{ request('status') }}&center_id={{ request('center_id') }}&source={{ request('source') }}&search={{ request('search') }}" rel="prev">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @php
                                $start = max($requests->currentPage() - 2, 1);
                                $end = min($start + 4, $requests->lastPage());
                                $start = max($end - 4, 1);
                            @endphp

                            @if($start > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $requests->url(1) }}&status={{ request('status') }}&center_id={{ request('center_id') }}&source={{ request('source') }}&search={{ request('search') }}">1</a>
                                </li>
                                @if($start > 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $requests->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link">{{ $i }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $requests->url($i) }}&status={{ request('status') }}&center_id={{ request('center_id') }}&source={{ request('source') }}&search={{ request('search') }}">{{ $i }}</a>
                                    </li>
                                @endif
                            @endfor

                            @if($end < $requests->lastPage())
                                @if($end < $requests->lastPage() - 1)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $requests->url($requests->lastPage()) }}&status={{ request('status') }}&center_id={{ request('center_id') }}&source={{ request('source') }}&search={{ request('search') }}">{{ $requests->lastPage() }}</a>
                                </li>
                            @endif

                            {{-- Next Page Link --}}
                            @if ($requests->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $requests->nextPageUrl() }}&status={{ request('status') }}&center_id={{ request('center_id') }}&source={{ request('source') }}&search={{ request('search') }}" rel="next">
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
            @endif
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
