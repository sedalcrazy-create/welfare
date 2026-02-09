@extends('layouts.app')

@section('title', 'واحد ' . $unit->number)

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-door-open"></i> واحد {{ $unit->number }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('units.edit', $unit) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i>
            ویرایش
        </a>
        <a href="{{ route('units.index', ['center_id' => $unit->center_id]) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-right"></i>
            بازگشت
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <i class="bi bi-hash stat-icon"></i>
            <div class="stat-value">{{ $unit->number }}</div>
            <div class="stat-label">شماره واحد</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <i class="bi bi-bed stat-icon"></i>
            <div class="stat-value">{{ $unit->bed_count }}</div>
            <div class="stat-label">تعداد تخت</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <i class="bi bi-layers stat-icon"></i>
            <div class="stat-value">{{ $unit->floor ?? '-' }}</div>
            <div class="stat-label">طبقه</div>
        </div>
    </div>
    <div class="col-md-3">
        @php
            $statusClass = match($unit->status) {
                'active' => 'success',
                'maintenance' => 'warning',
                'blocked' => 'danger',
                default => 'secondary'
            };
            $statusLabel = match($unit->status) {
                'active' => 'فعال',
                'maintenance' => 'تعمیرات',
                'blocked' => 'مسدود',
                default => '-'
            };
        @endphp
        <div class="stat-card {{ $statusClass }}">
            <i class="bi bi-circle-fill stat-icon"></i>
            <div class="stat-value">{{ $statusLabel }}</div>
            <div class="stat-label">وضعیت</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Info -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle"></i>
                اطلاعات واحد
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>شماره واحد</label>
                        <span>{{ $unit->number }}</span>
                    </div>
                    <div class="info-item">
                        <label>نام واحد</label>
                        <span>{{ $unit->name ?: '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>مرکز رفاهی</label>
                        <span>
                            <a href="{{ route('centers.show', $unit->center) }}" class="text-link">
                                {{ $unit->center->name }}
                            </a>
                        </span>
                    </div>
                    <div class="info-item">
                        <label>شهر</label>
                        <span>{{ $unit->center->city }}</span>
                    </div>
                    <div class="info-item">
                        <label>نوع واحد</label>
                        <span>
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
                        </span>
                    </div>
                    <div class="info-item">
                        <label>تعداد تخت</label>
                        <span>{{ $unit->bed_count }} تخت</span>
                    </div>
                    <div class="info-item">
                        <label>طبقه</label>
                        <span>{{ $unit->floor ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>بلوک</label>
                        <span>{{ $unit->block ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>وضعیت</label>
                        <span>
                            @switch($unit->status)
                                @case('active')
                                    <span class="badge badge-success">فعال</span>
                                    @break
                                @case('maintenance')
                                    <span class="badge badge-warning">در حال تعمیر</span>
                                    @break
                                @case('blocked')
                                    <span class="badge badge-danger">مسدود</span>
                                    @break
                            @endswitch
                        </span>
                    </div>
                    <div class="info-item">
                        <label>نوع استفاده</label>
                        <span>
                            @if($unit->is_management)
                                <span class="badge badge-secondary">مدیریتی</span>
                            @else
                                <span class="badge badge-info">قابل رزرو</span>
                            @endif
                        </span>
                    </div>
                </div>

                @if($unit->notes)
                    <div class="info-section mt-4">
                        <label><i class="bi bi-chat-text"></i> توضیحات</label>
                        <p>{{ $unit->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Reservations -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check"></i>
                رزروهای اخیر
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="mt-2 text-muted">هنوز رزروی برای این واحد ثبت نشده است.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Amenities -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-stars"></i>
                امکانات واحد
            </div>
            <div class="card-body">
                @if($unit->amenities && count($unit->amenities) > 0)
                    <div class="amenities-list">
                        @php
                            $amenityLabels = [
                                'ac' => ['icon' => 'bi-snow', 'label' => 'کولر/اسپلیت'],
                                'tv' => ['icon' => 'bi-tv', 'label' => 'تلویزیون'],
                                'fridge' => ['icon' => 'bi-box', 'label' => 'یخچال'],
                                'bathroom' => ['icon' => 'bi-droplet', 'label' => 'سرویس بهداشتی'],
                                'kitchen' => ['icon' => 'bi-cup-hot', 'label' => 'آشپزخانه'],
                                'balcony' => ['icon' => 'bi-door-open', 'label' => 'بالکن'],
                                'safe' => ['icon' => 'bi-safe', 'label' => 'گاوصندوق'],
                                'wifi' => ['icon' => 'bi-wifi', 'label' => 'WiFi'],
                                'phone' => ['icon' => 'bi-telephone', 'label' => 'تلفن'],
                                'wardrobe' => ['icon' => 'bi-archive', 'label' => 'کمد لباس'],
                                'desk' => ['icon' => 'bi-laptop', 'label' => 'میز کار'],
                                'sofa' => ['icon' => 'bi-house', 'label' => 'مبل'],
                            ];
                        @endphp
                        @foreach($unit->amenities as $amenity)
                            @if(isset($amenityLabels[$amenity]))
                                <div class="amenity-item">
                                    <i class="bi {{ $amenityLabels[$amenity]['icon'] }}"></i>
                                    <span>{{ $amenityLabels[$amenity]['label'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center">امکاناتی ثبت نشده است.</p>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-lightning"></i>
                تغییر وضعیت
            </div>
            <div class="card-body">
                <div class="status-actions">
                    <form action="{{ route('units.toggle-status', $unit) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="status-btn {{ $unit->status === 'active' ? 'active' : '' }}">
                            <i class="bi bi-check-circle"></i>
                            فعال
                        </button>
                    </form>
                    <form action="{{ route('units.toggle-status', $unit) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="maintenance">
                        <button type="submit" class="status-btn warning {{ $unit->status === 'maintenance' ? 'active' : '' }}">
                            <i class="bi bi-tools"></i>
                            تعمیرات
                        </button>
                    </form>
                    <form action="{{ route('units.toggle-status', $unit) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="blocked">
                        <button type="submit" class="status-btn danger {{ $unit->status === 'blocked' ? 'active' : '' }}">
                            <i class="bi bi-x-circle"></i>
                            مسدود
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i>
                اطلاعات سیستمی
            </div>
            <div class="card-body">
                <div class="meta-list">
                    <div class="meta-row">
                        <label>شناسه</label>
                        <span>#{{ $unit->id }}</span>
                    </div>
                    <div class="meta-row">
                        <label>تاریخ ایجاد</label>
                        <span>{{ jdate($unit->created_at)->format('Y/m/d H:i') }}</span>
                    </div>
                    <div class="meta-row">
                        <label>آخرین بروزرسانی</label>
                        <span>{{ jdate($unit->updated_at)->format('Y/m/d H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-item label {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .info-item span {
        font-size: 1rem;
        color: var(--text-dark);
        font-weight: 600;
    }

    .text-link {
        color: var(--primary);
        text-decoration: none;
    }

    .text-link:hover {
        text-decoration: underline;
    }

    .info-section {
        padding-top: 15px;
        border-top: 1px solid var(--border-color);
    }

    .info-section label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .info-section p {
        margin: 0;
        color: var(--text-dark);
        line-height: 1.8;
    }

    .amenities-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .amenity-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: var(--bg-light);
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .amenity-item i {
        color: var(--primary);
        font-size: 1.1rem;
    }

    .status-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

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

    .status-btn:hover {
        background: var(--bg-light);
    }

    .status-btn.active {
        background: #ecfdf5;
        border-color: var(--success);
        color: var(--success);
    }

    .status-btn.warning.active {
        background: #fffbeb;
        border-color: var(--warning);
        color: var(--warning);
    }

    .status-btn.danger.active {
        background: #fef2f2;
        border-color: var(--danger);
        color: var(--danger);
    }

    .meta-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .meta-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .meta-row label {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .meta-row span {
        font-size: 0.9rem;
        color: var(--text-dark);
        font-weight: 500;
    }

    .btn-warning {
        background: var(--warning);
        color: white;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
