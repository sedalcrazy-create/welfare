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

<!-- Quota Card -->
@if(auth()->check())
<div class="row mb-4">
    <div class="col-12">
        <div class="card quota-card">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1"><i class="bi bi-ticket-perforated"></i> سهمیه معرفی‌نامه شما</h5>
                    <p class="text-muted mb-0">
                        کل: <strong>{{ auth()->user()->quota_total }}</strong> |
                        استفاده شده: <strong>{{ auth()->user()->quota_used }}</strong> |
                        باقیمانده: <strong class="text-success">{{ auth()->user()->quota_remaining }}</strong>
                    </p>
                </div>
                <div class="quota-progress-circle">
                    <svg width="100" height="100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e9ecef" stroke-width="8"></circle>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="8"
                                stroke-dasharray="{{ auth()->user()->quota_total > 0 ? (auth()->user()->quota_remaining / auth()->user()->quota_total * 251) : 0 }} 251"
                                stroke-linecap="round" transform="rotate(-90 50 50)"></circle>
                        <text x="50" y="55" text-anchor="middle" font-size="20" font-weight="bold" fill="#10b981">
                            {{ auth()->user()->quota_total > 0 ? round((auth()->user()->quota_remaining / auth()->user()->quota_total) * 100) : 0 }}%
                        </text>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card warning">
            <i class="bi bi-clock-history stat-icon"></i>
            <div class="stat-value">{{ number_format($stats['pending_requests'] ?? 0) }}</div>
            <div class="stat-label">درخواست در انتظار</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <i class="bi bi-check-circle stat-icon"></i>
            <div class="stat-value">{{ number_format($stats['approved_requests'] ?? 0) }}</div>
            <div class="stat-label">درخواست تأیید شده</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card primary">
            <i class="bi bi-file-earmark-text stat-icon"></i>
            <div class="stat-value">{{ $stats['active_letters'] ?? 0 }}</div>
            <div class="stat-label">معرفی‌نامه فعال</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info">
            <i class="bi bi-files stat-icon"></i>
            <div class="stat-value">{{ $stats['total_letters'] ?? 0 }}</div>
            <div class="stat-label">کل معرفی‌نامه‌ها</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-charge"></i> دسترسی سریع
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('personnel-requests.create') }}" class="quick-action-btn">
                            <i class="bi bi-person-plus-fill"></i>
                            <span>ثبت درخواست جدید</span>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('personnel-requests.index', ['status' => 'pending']) }}" class="quick-action-btn">
                            <i class="bi bi-clock-history"></i>
                            <span>درخواست‌های در انتظار</span>
                            @if(($stats['pending_requests'] ?? 0) > 0)
                                <span class="badge bg-warning">{{ $stats['pending_requests'] }}</span>
                            @endif
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('introduction-letters.create') }}" class="quick-action-btn">
                            <i class="bi bi-file-earmark-plus"></i>
                            <span>صدور معرفی‌نامه</span>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('centers.index') }}" class="quick-action-btn">
                            <i class="bi bi-building"></i>
                            <span>مراکز رفاهی</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mb-4">
    <!-- Recent Personnel Requests -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-person-lines-fill"></i> آخرین درخواست‌ها
                </div>
                <a href="{{ route('personnel-requests.index') }}" class="btn btn-sm btn-outline-primary">
                    مشاهده همه
                </a>
            </div>
            <div class="card-body p-0">
                @if(isset($recentRequests) && count($recentRequests) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentRequests as $request)
                            <a href="{{ route('personnel-requests.show', $request) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $request->full_name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-hash"></i> {{ $request->tracking_code }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        @if($request->status === 'pending')
                                            <span class="badge bg-warning">در انتظار</span>
                                        @elseif($request->status === 'approved')
                                            <span class="badge bg-success">تأیید شده</span>
                                        @else
                                            <span class="badge bg-danger">رد شده</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ jdate($request->created_at)->ago() }}</small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">هیچ درخواستی یافت نشد</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Introduction Letters -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-file-earmark-text"></i> آخرین معرفی‌نامه‌ها
                </div>
                <a href="{{ route('introduction-letters.index') }}" class="btn btn-sm btn-outline-primary">
                    مشاهده همه
                </a>
            </div>
            <div class="card-body p-0">
                @if(isset($recentLetters) && count($recentLetters) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentLetters as $letter)
                            <a href="{{ route('introduction-letters.show', $letter) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $letter->personnel->full_name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-building"></i> {{ $letter->center->name }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        @if($letter->status === 'active')
                                            <span class="badge bg-success">فعال</span>
                                        @elseif($letter->status === 'used')
                                            <span class="badge bg-secondary">استفاده شده</span>
                                        @else
                                            <span class="badge bg-danger">لغو شده</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $letter->letter_code }}</small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">هیچ معرفی‌نامه‌ای یافت نشد</p>
                    </div>
                @endif
            </div>
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
                    سامانه صدور معرفی‌نامه و مدیریت رزرو مراکز رفاهی بانک ملی ایران
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
                    <i class="bi bi-check-circle-fill"></i>
                    فاز 1 - سیستم معرفی‌نامه فعال است
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .quota-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 2px solid #10b981;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);
    }

    .quota-progress-circle {
        position: relative;
    }

    .quota-progress-circle svg {
        transform: rotate(0deg);
    }

    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 25px 15px;
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        text-decoration: none;
        color: #374151;
        transition: all 0.3s ease;
        position: relative;
    }

    .quick-action-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .quick-action-btn i {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .quick-action-btn span {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .quick-action-btn .badge {
        position: absolute;
        top: 10px;
        left: 10px;
    }

    .stat-card.info {
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        color: white;
    }

    .list-group-item {
        transition: all 0.2s ease;
    }

    .list-group-item:hover {
        background-color: #f9fafb;
        transform: translateX(-5px);
    }

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
