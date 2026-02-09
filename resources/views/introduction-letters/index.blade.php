@extends('layouts.app')

@section('title', 'لیست معرفی‌نامه‌ها')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">لیست معرفی‌نامه‌ها</h2>
        <a href="{{ route('introduction-letters.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> صدور معرفی‌نامه جدید
        </a>
    </div>

    {{-- Quota Info --}}
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-info-circle"></i>
            <strong>سهمیه شما:</strong>
            کل: {{ auth()->user()->quota_total }} |
            استفاده شده: {{ auth()->user()->quota_used }} |
            باقیمانده: <span class="badge bg-success">{{ auth()->user()->quota_remaining }}</span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('introduction-letters.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select">
                        <option value="">همه</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>استفاده شده</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>لغو شده</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>منقضی شده</option>
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

                <div class="col-md-4">
                    <label class="form-label">جستجو</label>
                    <input type="text" name="search" class="form-control" placeholder="کد معرفی‌نامه، نام یا کد ملی..." value="{{ request('search') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> جستجو
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Letters Table --}}
    <div class="card">
        <div class="card-body">
            @if($letters->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>کد معرفی‌نامه</th>
                                <th>پرسنل</th>
                                <th>کد ملی</th>
                                <th>مرکز</th>
                                <th>تعداد خانواده</th>
                                <th>صادرکننده</th>
                                <th>تاریخ صدور</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($letters as $letter)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $letter->letter_code }}</strong>
                                    </td>
                                    <td>{{ $letter->personnel->full_name }}</td>
                                    <td><code>{{ $letter->personnel->national_code }}</code></td>
                                    <td>
                                        <span class="badge bg-info">{{ $letter->center->name }}</span>
                                    </td>
                                    <td>
                                        <i class="bi bi-people-fill"></i> {{ $letter->family_count }}
                                    </td>
                                    <td>{{ $letter->issuedBy->name }}</td>
                                    <td>{{ jdate($letter->issued_at)->format('Y/m/d H:i') }}</td>
                                    <td>
                                        @if($letter->status === 'active')
                                            <span class="badge bg-success">فعال</span>
                                        @elseif($letter->status === 'used')
                                            <span class="badge bg-secondary">استفاده شده</span>
                                        @elseif($letter->status === 'cancelled')
                                            <span class="badge bg-danger">لغو شده</span>
                                        @else
                                            <span class="badge bg-warning">منقضی شده</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('introduction-letters.show', $letter) }}"
                                               class="btn btn-info"
                                               title="مشاهده">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('introduction-letters.print', $letter) }}"
                                               class="btn btn-primary"
                                               title="چاپ"
                                               target="_blank">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $letters->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-3">هیچ معرفی‌نامه‌ای یافت نشد</p>
                    <a href="{{ route('introduction-letters.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> صدور اولین معرفی‌نامه
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $letters->where('status', 'active')->count() }}</h3>
                    <p class="mb-0">فعال</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3>{{ $letters->where('status', 'used')->count() }}</h3>
                    <p class="mb-0">استفاده شده</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3>{{ $letters->where('status', 'cancelled')->count() }}</h3>
                    <p class="mb-0">لغو شده</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $letters->total() }}</h3>
                    <p class="mb-0">مجموع</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
