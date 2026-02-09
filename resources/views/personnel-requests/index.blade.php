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
                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
