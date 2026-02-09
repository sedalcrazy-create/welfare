@extends('layouts.app')

@section('title', 'داشبورد')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-speedometer2"></i> داشبورد</h1>
    <span class="date">
        <i class="bi bi-calendar3"></i>
        {{ jdate()->format('l j F Y') }}
    </span>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <i class="bi bi-house-door stat-icon"></i>
            <div class="stat-value">{{ number_format($stats['total_beds'] ?? 0) }}</div>
            <div class="stat-label">تخت فعال</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <i class="bi bi-people stat-icon"></i>
            <div class="stat-value">{{ number_format($stats['total_personnel'] ?? 0) }}</div>
            <div class="stat-label">پرسنل</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <i class="bi bi-dice-5 stat-icon"></i>
            <div class="stat-value">{{ $stats['active_lotteries'] ?? 0 }}</div>
            <div class="stat-label">قرعه‌کشی فعال</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <i class="bi bi-bookmark-check stat-icon"></i>
            <div class="stat-value">{{ $stats['pending_reservations'] ?? 0 }}</div>
            <div class="stat-label">رزرو در انتظار</div>
        </div>
    </div>
</div>

<!-- Centers Overview -->
@if(isset($centers) && count($centers) > 0)
<div class="row mb-4">
    @foreach($centers as $center)
    <div class="col-md-4">
        <div class="card h-100 center-card" style="animation-delay: {{ $loop->index * 0.1 }}s">
            <div class="card-header">
                <i class="bi bi-building"></i>
                {{ $center->name }}
                @if($center->is_active)
                    <span class="badge badge-success" style="margin-right: auto;">فعال</span>
                @else
                    <span class="badge badge-secondary" style="margin-right: auto;">غیرفعال</span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    <i class="bi bi-geo-alt"></i> {{ $center->city }}
                </p>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="center-stat">
                            <div class="center-stat-value shimmer-text">{{ $center->unit_count }}</div>
                            <div class="center-stat-label">واحد</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="center-stat">
                            <div class="center-stat-value shimmer-text">{{ $center->bed_count }}</div>
                            <div class="center-stat-label">تخت</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="center-stat">
                            <div class="center-stat-value shimmer-text">{{ $center->stay_duration }}</div>
                            <div class="center-stat-label">شب</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- Welcome Section -->
<div class="row">
    <div class="col-12">
        <div class="card welcome-card">
            <div class="welcome-content">
                <div class="welcome-icon">
                    <i class="bi bi-sun"></i>
                </div>
                <h2 class="welcome-title shimmer-text">به سامانه مدیریت مراکز رفاهی خوش آمدید</h2>
                <p class="welcome-text">
                    این سامانه برای مدیریت یکپارچه رزرو و قرعه‌کشی مراکز رفاهی بانک ملی ایران طراحی شده است.
                </p>
                <div class="welcome-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="feature-text">
                            <strong>۳ مرکز رفاهی</strong>
                            <span>مشهد، بابلسر، چادگان</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-door-open"></i>
                        </div>
                        <div class="feature-text">
                            <strong>۴۲۶ واحد</strong>
                            <span>اتاق، سوئیت و ویلا</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="feature-text">
                            <strong>+۷۰,۰۰۰ پرسنل</strong>
                            <span>کارکنان بانک ملی</span>
                        </div>
                    </div>
                </div>
                <div class="welcome-status">
                    <i class="bi bi-gear-wide-connected"></i>
                    سامانه در حال توسعه است...
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .center-stat {
        padding: 15px;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .center-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .center-stat-value {
        font-size: 1.8rem;
        font-weight: 800;
    }

    .center-stat-label {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 5px;
    }

    .welcome-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 249, 240, 0.8) 100%);
        text-align: center;
        padding: 50px 30px;
        position: relative;
        overflow: hidden;
    }

    .welcome-card::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 107, 107, 0.12) 0%, transparent 70%);
        animation: float-bg 15s ease-in-out infinite;
    }

    .welcome-card::after {
        content: '';
        position: absolute;
        bottom: -100px;
        left: -100px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(0, 210, 211, 0.1) 0%, transparent 70%);
        animation: float-bg 18s ease-in-out infinite reverse;
    }

    @keyframes float-bg {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(30px, 30px); }
    }

    .welcome-content {
        position: relative;
        z-index: 1;
    }

    .welcome-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 25px;
        background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        animation: bounce-icon 2s ease-in-out infinite;
        box-shadow: 0 10px 30px rgba(255, 107, 107, 0.35);
    }

    @keyframes bounce-icon {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .welcome-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .welcome-text {
        color: #6b7280;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto 40px;
        line-height: 1.8;
    }

    .welcome-features {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin-bottom: 40px;
        flex-wrap: wrap;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px 25px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .feature-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .feature-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #00d2d3 0%, #a55eea 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #fff;
    }

    .feature-text {
        text-align: right;
    }

    .feature-text strong {
        display: block;
        font-size: 1.1rem;
        color: #2d3748;
    }

    .feature-text span {
        font-size: 0.9rem;
        color: #6b7280;
    }

    .welcome-status {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 25px;
        background: linear-gradient(135deg, #00d2d3 0%, #48dbfb 100%);
        color: #fff;
        border-radius: 30px;
        font-weight: 600;
        animation: pulse-status 2s ease-in-out infinite;
    }

    @keyframes pulse-status {
        0%, 100% { box-shadow: 0 0 0 0 rgba(0, 210, 211, 0.3); }
        50% { box-shadow: 0 0 0 15px rgba(0, 210, 211, 0); }
    }

    .welcome-status i {
        animation: spin-gear 4s linear infinite;
    }

    @keyframes spin-gear {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .welcome-features {
            flex-direction: column;
            gap: 15px;
        }

        .feature-item {
            width: 100%;
            justify-content: center;
        }

        .welcome-title {
            font-size: 1.4rem;
        }
    }
</style>
@endpush
