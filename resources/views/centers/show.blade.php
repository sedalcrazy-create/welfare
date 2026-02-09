@extends('layouts.app')

@section('title', $center->name)

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-building"></i> {{ $center->name }}</h1>
    <div class="d-flex gap-3">
        <a href="{{ route('centers.edit', $center) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i>
            ویرایش
        </a>
        <a href="{{ route('centers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-right"></i>
            بازگشت به لیست
        </a>
    </div>
</div>

<!-- Center Info Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <i class="bi bi-door-open stat-icon"></i>
            <div class="stat-value">{{ number_format($center->unit_count) }}</div>
            <div class="stat-label">تعداد واحد</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <i class="bi bi-bed stat-icon"></i>
            <div class="stat-value">{{ number_format($center->bed_count) }}</div>
            <div class="stat-label">تعداد تخت</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <i class="bi bi-moon-stars stat-icon"></i>
            <div class="stat-value">{{ $center->stay_duration }}</div>
            <div class="stat-label">شب اقامت</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card {{ $center->is_active ? 'success' : 'danger' }}">
            <i class="bi bi-{{ $center->is_active ? 'check-circle' : 'x-circle' }} stat-icon"></i>
            <div class="stat-value">{{ $center->is_active ? 'فعال' : 'غیرفعال' }}</div>
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
                اطلاعات مرکز
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>نام مرکز</label>
                        <span>{{ $center->name }}</span>
                    </div>
                    <div class="info-item">
                        <label>شهر</label>
                        <span>{{ $center->city }}</span>
                    </div>
                    <div class="info-item">
                        <label>نوع مرکز</label>
                        <span>
                            @switch($center->type)
                                @case('religious')
                                    <span class="badge badge-info">زیارتی</span>
                                    @break
                                @case('beach')
                                    <span class="badge badge-success">ساحلی</span>
                                    @break
                                @case('mountain')
                                    <span class="badge badge-warning">کوهستانی</span>
                                    @break
                            @endswitch
                        </span>
                    </div>
                    <div class="info-item">
                        <label>تلفن</label>
                        <span>{{ $center->phone ?: '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>ساعت ورود</label>
                        <span>{{ substr($center->check_in_time, 0, 5) }}</span>
                    </div>
                    <div class="info-item">
                        <label>ساعت خروج</label>
                        <span>{{ substr($center->check_out_time, 0, 5) }}</span>
                    </div>
                </div>

                @if($center->address)
                    <div class="info-section mt-4">
                        <label><i class="bi bi-geo-alt"></i> آدرس</label>
                        <p>{{ $center->address }}</p>
                    </div>
                @endif

                @if($center->description)
                    <div class="info-section mt-4">
                        <label><i class="bi bi-text-paragraph"></i> توضیحات</label>
                        <p>{{ $center->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Units Summary -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-door-open"></i>
                واحدهای این مرکز
                <span class="badge badge-info" style="margin-right: auto;">{{ $center->units->count() }} واحد</span>
            </div>
            <div class="card-body">
                @if($center->units->count() > 0)
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>شماره واحد</th>
                                    <th>نوع</th>
                                    <th>تعداد تخت</th>
                                    <th>طبقه</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($center->units->take(10) as $unit)
                                    <tr>
                                        <td>{{ $unit->number }}</td>
                                        <td>
                                            @switch($unit->type)
                                                @case('room') اتاق @break
                                                @case('suite') سوئیت @break
                                                @case('villa') ویلا @break
                                                @case('apartment') آپارتمان @break
                                            @endswitch
                                        </td>
                                        <td>{{ $unit->bed_count }} تخت</td>
                                        <td>{{ $unit->floor ?: '-' }}</td>
                                        <td>
                                            @if($unit->status === 'available')
                                                <span class="badge badge-success">فعال</span>
                                            @elseif($unit->status === 'maintenance')
                                                <span class="badge badge-warning">تعمیرات</span>
                                            @else
                                                <span class="badge badge-danger">مسدود</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($center->units->count() > 10)
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-secondary">
                                مشاهده همه {{ $center->units->count() }} واحد
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-door-closed" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="mt-2 text-muted">هنوز واحدی برای این مرکز ثبت نشده است.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Amenities -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-stars"></i>
                امکانات
            </div>
            <div class="card-body">
                @if($center->amenities && count($center->amenities) > 0)
                    <div class="amenities-list">
                        @php
                            $amenityLabels = [
                                'parking' => ['icon' => 'bi-car-front', 'label' => 'پارکینگ'],
                                'wifi' => ['icon' => 'bi-wifi', 'label' => 'اینترنت WiFi'],
                                'restaurant' => ['icon' => 'bi-cup-hot', 'label' => 'رستوران'],
                                'prayer_room' => ['icon' => 'bi-moon', 'label' => 'نمازخانه'],
                                'gym' => ['icon' => 'bi-bicycle', 'label' => 'سالن ورزش'],
                                'pool' => ['icon' => 'bi-water', 'label' => 'استخر'],
                                'playground' => ['icon' => 'bi-tree', 'label' => 'زمین بازی'],
                                'laundry' => ['icon' => 'bi-bucket', 'label' => 'خشکشویی'],
                                'medical' => ['icon' => 'bi-heart-pulse', 'label' => 'بهداری'],
                                'shop' => ['icon' => 'bi-shop', 'label' => 'فروشگاه'],
                            ];
                        @endphp
                        @foreach($center->amenities as $amenity)
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
                دسترسی سریع
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="{{ route('centers.edit', $center) }}" class="quick-action-btn">
                        <i class="bi bi-pencil"></i>
                        ویرایش مرکز
                    </a>
                    <a href="#" class="quick-action-btn">
                        <i class="bi bi-plus-circle"></i>
                        افزودن واحد
                    </a>
                    <a href="#" class="quick-action-btn">
                        <i class="bi bi-calendar-plus"></i>
                        ایجاد دوره
                    </a>
                    <a href="#" class="quick-action-btn">
                        <i class="bi bi-graph-up"></i>
                        گزارش اشغال
                    </a>
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
                <div class="meta-info">
                    <div class="meta-item">
                        <label>شناسه</label>
                        <span>#{{ $center->id }}</span>
                    </div>
                    <div class="meta-item">
                        <label>اسلاگ</label>
                        <span>{{ $center->slug }}</span>
                    </div>
                    <div class="meta-item">
                        <label>تاریخ ایجاد</label>
                        <span>{{ jdate($center->created_at)->format('Y/m/d H:i') }}</span>
                    </div>
                    <div class="meta-item">
                        <label>آخرین بروزرسانی</label>
                        <span>{{ jdate($center->updated_at)->format('Y/m/d H:i') }}</span>
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
        color: #888;
        font-weight: 500;
    }

    .info-item span {
        font-size: 1rem;
        color: #333;
        font-weight: 600;
    }

    .info-section {
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }

    .info-section label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 8px;
    }

    .info-section p {
        margin: 0;
        color: #333;
        line-height: 1.8;
    }

    .amenities-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .amenity-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 15px;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 10px;
    }

    .amenity-item i {
        font-size: 1.2rem;
        color: #ff8928;
    }

    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 10px;
        color: #333;
        text-decoration: none;
        transition: all 0.3s;
    }

    .quick-action-btn:hover {
        background: linear-gradient(135deg, #fff4cc 0%, #ffde22 100%);
        transform: translateX(-5px);
    }

    .quick-action-btn i {
        font-size: 1.1rem;
        color: #ff8928;
    }

    .meta-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .meta-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .meta-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .meta-item label {
        font-size: 0.85rem;
        color: #888;
    }

    .meta-item span {
        font-size: 0.9rem;
        color: #333;
        font-weight: 500;
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
