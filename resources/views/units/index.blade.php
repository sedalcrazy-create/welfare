@extends('layouts.app')

@section('title', 'واحدها')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-door-open"></i> مدیریت واحدها</h1>
    <a href="{{ route('units.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i>
        افزودن واحد جدید
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('units.index') }}" method="GET" class="row gap-3 align-items-end">
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
                <label class="form-label">جستجو</label>
                <input type="text" name="search" class="form-control"
                       placeholder="شماره یا نام..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">نوع واحد</label>
                <select name="type" class="form-control">
                    <option value="">همه</option>
                    <option value="room" {{ request('type') === 'room' ? 'selected' : '' }}>اتاق</option>
                    <option value="suite" {{ request('type') === 'suite' ? 'selected' : '' }}>سوئیت</option>
                    <option value="villa" {{ request('type') === 'villa' ? 'selected' : '' }}>ویلا</option>
                    <option value="apartment" {{ request('type') === 'apartment' ? 'selected' : '' }}>آپارتمان</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">وضعیت</label>
                <select name="status" class="form-control">
                    <option value="">همه</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>تعمیرات</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>مسدود</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">تعداد تخت</label>
                <select name="bed_count" class="form-control">
                    <option value="">همه</option>
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ request('bed_count') == $i ? 'selected' : '' }}>{{ $i }} تخت</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Stats Row -->
@if(request('center_id'))
    @php
        $selectedCenter = $centers->find(request('center_id'));
        $stats = [
            'total' => $units->total(),
            'active' => \App\Models\Unit::where('center_id', request('center_id'))->where('status', 'active')->count(),
            'beds' => \App\Models\Unit::where('center_id', request('center_id'))->sum('bed_count'),
        ];
    @endphp
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card primary">
                <i class="bi bi-door-open stat-icon"></i>
                <div class="stat-value">{{ number_format($stats['total']) }}</div>
                <div class="stat-label">کل واحدها</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card success">
                <i class="bi bi-check-circle stat-icon"></i>
                <div class="stat-value">{{ number_format($stats['active']) }}</div>
                <div class="stat-label">واحد فعال</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card warning">
                <i class="bi bi-bed stat-icon"></i>
                <div class="stat-value">{{ number_format($stats['beds']) }}</div>
                <div class="stat-label">کل تخت‌ها</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info">
                <i class="bi bi-building stat-icon"></i>
                <div class="stat-value">{{ $selectedCenter?->name ?? '-' }}</div>
                <div class="stat-label">مرکز انتخابی</div>
            </div>
        </div>
    </div>
@endif

<!-- Units List -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul"></i>
        لیست واحدها
        <span class="badge badge-info" style="margin-right: auto;">{{ $units->total() }} واحد</span>
    </div>
    <div class="card-body">
        @if($units->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>شماره</th>
                            <th>نام</th>
                            <th>مرکز</th>
                            <th>نوع</th>
                            <th>تخت</th>
                            <th>طبقه</th>
                            <th>بلوک</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($units as $unit)
                            <tr>
                                <td>{{ $loop->iteration + ($units->currentPage() - 1) * $units->perPage() }}</td>
                                <td><strong>{{ $unit->number }}</strong></td>
                                <td>{{ $unit->name ?: '-' }}</td>
                                <td>
                                    <a href="{{ route('centers.show', $unit->center) }}" class="text-link">
                                        {{ $unit->center->name }}
                                    </a>
                                </td>
                                <td>
                                    @switch($unit->type)
                                        @case('room')
                                            <span class="badge badge-secondary">اتاق</span>
                                            @break
                                        @case('suite')
                                            <span class="badge badge-info">سوئیت</span>
                                            @break
                                        @case('villa')
                                            <span class="badge badge-success">ویلا</span>
                                            @break
                                        @case('apartment')
                                            <span class="badge badge-warning">آپارتمان</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $unit->bed_count }}</td>
                                <td>{{ $unit->floor ?? '-' }}</td>
                                <td>{{ $unit->block ?? '-' }}</td>
                                <td>
                                    @switch($unit->status)
                                        @case('active')
                                            <span class="badge badge-success">فعال</span>
                                            @break
                                        @case('maintenance')
                                            <span class="badge badge-warning">تعمیرات</span>
                                            @break
                                        @case('blocked')
                                            <span class="badge badge-danger">مسدود</span>
                                            @break
                                    @endswitch
                                    @if($unit->is_management)
                                        <span class="badge badge-secondary">مدیریتی</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('units.show', $unit) }}"
                                           class="btn btn-sm btn-info" title="مشاهده">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('units.edit', $unit) }}"
                                           class="btn btn-sm btn-warning" title="ویرایش">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    onclick="toggleDropdown(this)">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <form action="{{ route('units.toggle-status', $unit) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-check-circle text-success"></i> فعال
                                                    </button>
                                                </form>
                                                <form action="{{ route('units.toggle-status', $unit) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="maintenance">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-tools text-warning"></i> تعمیرات
                                                    </button>
                                                </form>
                                                <form action="{{ route('units.toggle-status', $unit) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="blocked">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-x-circle text-danger"></i> مسدود
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <form action="{{ route('units.destroy', $unit) }}"
                                              method="POST" style="display: inline;"
                                              onsubmit="return confirm('آیا از حذف این واحد اطمینان دارید؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $units->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-door-closed" style="font-size: 4rem; color: #ccc;"></i>
                <p class="mt-3 text-muted">هیچ واحدی یافت نشد.</p>
                <a href="{{ route('units.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> افزودن اولین واحد
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-link {
        color: var(--primary);
        text-decoration: none;
    }

    .text-link:hover {
        text-decoration: underline;
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: center;
        flex-wrap: nowrap;
    }

    .btn-sm {
        padding: 5px 8px;
        font-size: 0.8rem;
    }

    .stat-card.info {
        border-right: 4px solid var(--info);
    }

    .stat-card.info .stat-icon,
    .stat-card.info .stat-value {
        color: var(--info);
    }

    .dropdown {
        position: relative;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        left: 0;
        top: 100%;
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 140px;
        z-index: 1000;
    }

    .dropdown-menu.show {
        display: block;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border: none;
        background: none;
        width: 100%;
        text-align: right;
        cursor: pointer;
        font-size: 0.85rem;
        transition: background 0.2s;
    }

    .dropdown-item:hover {
        background: var(--bg-light);
    }

    .dropdown-toggle::after {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleDropdown(btn) {
        const menu = btn.nextElementSibling;
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m !== menu) m.classList.remove('show');
        });
        menu.classList.toggle('show');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
        }
    });
</script>
@endpush
