@extends('layouts.app')

@section('title', 'دوره ' . $period->code)

@section('content')
<div class="page-header">
    <h1><i class="bi bi-calendar-range"></i> {{ $period->code }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('periods.edit', $period) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i>
            ویرایش
        </a>
        <a href="{{ route('periods.index', ['center_id' => $period->center_id]) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-right"></i>
            بازگشت
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <i class="bi bi-calendar-range stat-icon"></i>
            <div class="stat-value">{{ $period->center->stay_duration }}</div>
            <div class="stat-label">شب اقامت</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <i class="bi bi-door-open stat-icon"></i>
            <div class="stat-value">{{ $period->capacity }}</div>
            <div class="stat-label">ظرفیت کل</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <i class="bi bi-bookmark-check stat-icon"></i>
            <div class="stat-value">{{ $period->reserved_count }}</div>
            <div class="stat-label">رزرو شده</div>
        </div>
    </div>
    <div class="col-md-3">
        @php
            $available = $period->capacity - $period->reserved_count;
            $cardClass = $available > 0 ? 'success' : 'danger';
        @endphp
        <div class="stat-card {{ $cardClass }}">
            <i class="bi bi-check2-circle stat-icon"></i>
            <div class="stat-value">{{ $available }}</div>
            <div class="stat-label">ظرفیت باقیمانده</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Period Info -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle"></i>
                اطلاعات دوره
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>کد دوره</label>
                        <span>{{ $period->code }}</span>
                    </div>
                    <div class="info-item">
                        <label>مرکز رفاهی</label>
                        <span>
                            <a href="{{ route('centers.show', $period->center) }}" class="text-link">
                                {{ $period->center->name }}
                            </a>
                        </span>
                    </div>
                    <div class="info-item">
                        <label>تاریخ شروع</label>
                        <span>{{ $period->jalali_start_date }}</span>
                    </div>
                    <div class="info-item">
                        <label>تاریخ پایان</label>
                        <span>{{ $period->jalali_end_date }}</span>
                    </div>
                    <div class="info-item">
                        <label>فصل</label>
                        <span>{{ $period->season ? $period->season->name : '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>نوع فصل</label>
                        <span>{{ $period->season ? $period->season->getTypeLabel() : '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>وضعیت</label>
                        <span>
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
                        </span>
                    </div>
                    <div class="info-item">
                        <label>درصد پر شدن</label>
                        <span>
                            @php $percent = $period->capacity > 0 ? round(($period->reserved_count / $period->capacity) * 100) : 0; @endphp
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: {{ $percent }}%"></div>
                                <span class="progress-text">{{ $percent }}%</span>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lottery Info -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-shuffle"></i>
                قرعه‌کشی
            </div>
            <div class="card-body">
                @if($period->lottery)
                    <div class="info-grid">
                        <div class="info-item">
                            <label>وضعیت قرعه‌کشی</label>
                            <span>{{ $period->lottery->status }}</span>
                        </div>
                        <div class="info-item">
                            <label>تعداد شرکت‌کننده</label>
                            <span>{{ $period->lottery->entries_count ?? 0 }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-shuffle" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="mt-2 text-muted">هنوز قرعه‌کشی برای این دوره ایجاد نشده است.</p>
                        @if($period->status === 'open' || $period->status === 'closed')
                            <a href="#" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i>
                                ایجاد قرعه‌کشی
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Reservations -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bookmark-check"></i>
                رزروها
                <span class="badge badge-info" style="margin-right: auto;">{{ $period->reservations->count() }}</span>
            </div>
            <div class="card-body">
                @if($period->reservations->count() > 0)
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>کد رزرو</th>
                                    <th>پرسنل</th>
                                    <th>واحد</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($period->reservations->take(10) as $reservation)
                                    <tr>
                                        <td>{{ $reservation->code ?? '-' }}</td>
                                        <td>{{ $reservation->personnel->full_name ?? '-' }}</td>
                                        <td>{{ $reservation->unit->number ?? '-' }}</td>
                                        <td>{{ $reservation->status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="mt-2 text-muted">هنوز رزروی ثبت نشده است.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Status Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-gear"></i>
                تغییر وضعیت
            </div>
            <div class="card-body">
                <div class="status-actions">
                    @if($period->status !== 'draft')
                        <form action="{{ route('periods.change-status', $period) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" class="status-btn {{ $period->status === 'draft' ? 'active' : '' }}">
                                <i class="bi bi-file-earmark"></i>
                                پیش‌نویس
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('periods.change-status', $period) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="open">
                        <button type="submit" class="status-btn success {{ $period->status === 'open' ? 'active' : '' }}">
                            <i class="bi bi-door-open"></i>
                            باز
                        </button>
                    </form>
                    <form action="{{ route('periods.change-status', $period) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="status-btn warning {{ $period->status === 'closed' ? 'active' : '' }}">
                            <i class="bi bi-door-closed"></i>
                            بسته
                        </button>
                    </form>
                    <form action="{{ route('periods.change-status', $period) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="status-btn info {{ $period->status === 'completed' ? 'active' : '' }}">
                            <i class="bi bi-check-all"></i>
                            تکمیل شده
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Center Info -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-building"></i>
                مرکز رفاهی
            </div>
            <div class="card-body">
                <div class="center-info">
                    <h4>{{ $period->center->name }}</h4>
                    <p class="text-muted">{{ $period->center->city }}</p>
                    <div class="center-stats">
                        <div><i class="bi bi-door-open"></i> {{ $period->center->unit_count }} واحد</div>
                        <div><i class="bi bi-bed"></i> {{ $period->center->bed_count }} تخت</div>
                        <div><i class="bi bi-moon"></i> {{ $period->center->stay_duration }} شب</div>
                    </div>
                    <a href="{{ route('centers.show', $period->center) }}" class="btn btn-secondary btn-sm mt-3">
                        مشاهده مرکز
                    </a>
                </div>
            </div>
        </div>

        <!-- Meta -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i>
                اطلاعات سیستمی
            </div>
            <div class="card-body">
                <div class="meta-list">
                    <div class="meta-row">
                        <label>شناسه</label>
                        <span>#{{ $period->id }}</span>
                    </div>
                    <div class="meta-row">
                        <label>تاریخ ایجاد</label>
                        <span>{{ jdate($period->created_at)->format('Y/m/d H:i') }}</span>
                    </div>
                    <div class="meta-row">
                        <label>آخرین بروزرسانی</label>
                        <span>{{ jdate($period->updated_at)->format('Y/m/d H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .info-item { display: flex; flex-direction: column; gap: 5px; }
    .info-item label { font-size: 0.85rem; color: var(--text-muted); }
    .info-item span { font-size: 1rem; color: var(--text-dark); font-weight: 600; }
    .text-link { color: var(--primary); text-decoration: none; }
    .text-link:hover { text-decoration: underline; }

    .progress-bar-container {
        width: 100%;
        height: 24px;
        background: var(--bg-light);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--success), var(--primary));
        border-radius: 12px;
        transition: width 0.3s;
    }
    .progress-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-actions { display: flex; flex-direction: column; gap: 8px; }
    .status-btn {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border-color);
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-family: inherit;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .status-btn:hover { background: var(--bg-light); }
    .status-btn.active { background: #ecfdf5; border-color: var(--success); color: var(--success); }
    .status-btn.success.active { background: #ecfdf5; border-color: var(--success); color: #065f46; }
    .status-btn.warning.active { background: #fffbeb; border-color: var(--warning); color: #92400e; }
    .status-btn.info.active { background: #eff6ff; border-color: var(--info); color: #1e40af; }

    .center-info h4 { margin: 0 0 5px; color: var(--text-dark); }
    .center-stats { display: flex; flex-direction: column; gap: 8px; margin-top: 15px; }
    .center-stats div { display: flex; align-items: center; gap: 8px; color: var(--text-muted); }
    .center-stats i { color: var(--primary); }

    .meta-list { display: flex; flex-direction: column; gap: 12px; }
    .meta-row { display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); }
    .meta-row:last-child { border-bottom: none; padding-bottom: 0; }
    .meta-row label { font-size: 0.85rem; color: var(--text-muted); }
    .meta-row span { font-size: 0.9rem; color: var(--text-dark); font-weight: 500; }

    .btn-warning { background: var(--warning); color: white; }

    @media (max-width: 768px) { .info-grid { grid-template-columns: 1fr; } }
</style>
@endpush
