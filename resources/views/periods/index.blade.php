@extends('layouts.app')

@section('title', 'دوره‌ها')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-calendar-range"></i> مدیریت دوره‌ها</h1>
    <a href="{{ route('periods.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i>
        افزودن دوره جدید
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('periods.index') }}" method="GET" class="row gap-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">مرکز رفاهی</label>
                <select name="center_id" class="form-control" onchange="this.form.submit()">
                    <option value="">همه مراکز</option>
                    @foreach($centers as $center)
                        <option value="{{ $center->id }}" {{ request('center_id') == $center->id ? 'selected' : '' }}>
                            {{ $center->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">وضعیت</label>
                <select name="status" class="form-control">
                    <option value="">همه</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>باز</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>بسته</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>تکمیل شده</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">جستجوی کد</label>
                <input type="text" name="search" class="form-control" placeholder="کد دوره..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i> جستجو
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Stats -->
@if(request('center_id'))
    @php
        $stats = [
            'total' => $periods->total(),
            'open' => \App\Models\Period::where('center_id', request('center_id'))->where('status', 'open')->count(),
            'upcoming' => \App\Models\Period::where('center_id', request('center_id'))->where('start_date', '>', now())->count(),
        ];
    @endphp
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card primary">
                <i class="bi bi-calendar-range stat-icon"></i>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">کل دوره‌ها</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card success">
                <i class="bi bi-door-open stat-icon"></i>
                <div class="stat-value">{{ $stats['open'] }}</div>
                <div class="stat-label">دوره باز</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card warning">
                <i class="bi bi-clock stat-icon"></i>
                <div class="stat-value">{{ $stats['upcoming'] }}</div>
                <div class="stat-label">دوره آینده</div>
            </div>
        </div>
    </div>
@endif

<!-- Periods List -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul"></i>
        لیست دوره‌ها
        <span class="badge badge-info" style="margin-right: auto;">{{ $periods->total() }} دوره</span>
    </div>
    <div class="card-body">
        @if($periods->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>مرکز</th>
                            <th>تاریخ شروع</th>
                            <th>تاریخ پایان</th>
                            <th>مدت</th>
                            <th>ظرفیت</th>
                            <th>رزرو شده</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($periods as $period)
                            <tr>
                                <td><strong>{{ $period->code }}</strong></td>
                                <td>
                                    <a href="{{ route('centers.show', $period->center) }}" class="text-link">
                                        {{ $period->center->name }}
                                    </a>
                                </td>
                                <td>{{ $period->jalali_start_date }}</td>
                                <td>{{ $period->jalali_end_date }}</td>
                                <td>{{ $period->center->stay_duration }} شب</td>
                                <td>{{ $period->capacity }}</td>
                                <td>
                                    <span class="{{ $period->reserved_count >= $period->capacity ? 'text-danger' : '' }}">
                                        {{ $period->reserved_count }} / {{ $period->capacity }}
                                    </span>
                                </td>
                                <td>
                                    @switch($period->status)
                                        @case('draft')
                                            <span class="badge badge-secondary">پیش‌نویس</span>
                                            @break
                                        @case('open')
                                            <span class="badge badge-success">باز</span>
                                            @break
                                        @case('closed')
                                            <span class="badge badge-warning">بسته</span>
                                            @break
                                        @case('completed')
                                            <span class="badge badge-info">تکمیل شده</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('periods.show', $period) }}" class="btn btn-sm btn-info" title="مشاهده">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('periods.edit', $period) }}" class="btn btn-sm btn-warning" title="ویرایش">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($period->status === 'draft')
                                            <form action="{{ route('periods.change-status', $period) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="open">
                                                <button type="submit" class="btn btn-sm btn-success" title="باز کردن">
                                                    <i class="bi bi-play-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($period->status === 'open')
                                            <form action="{{ route('periods.change-status', $period) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="closed">
                                                <button type="submit" class="btn btn-sm btn-secondary" title="بستن">
                                                    <i class="bi bi-stop-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if(!$period->lottery && $period->reserved_count == 0)
                                            <form action="{{ route('periods.destroy', $period) }}" method="POST" style="display:inline;"
                                                  onsubmit="return confirm('آیا از حذف این دوره اطمینان دارید؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 d-flex justify-content-center">
                {{ $periods->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-calendar-x" style="font-size: 4rem; color: #ccc;"></i>
                <p class="mt-3 text-muted">هیچ دوره‌ای یافت نشد.</p>
                <a href="{{ route('periods.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> افزودن اولین دوره
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-link { color: var(--primary); text-decoration: none; }
    .text-link:hover { text-decoration: underline; }
    .text-danger { color: var(--danger); font-weight: 600; }
    .action-buttons { display: flex; gap: 4px; justify-content: center; }
    .btn-sm { padding: 5px 8px; font-size: 0.8rem; }
</style>
@endpush
