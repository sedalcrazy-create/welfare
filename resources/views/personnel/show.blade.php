@extends('layouts.app')

@section('title', 'جزئیات پرسنل')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('personnel.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-right"></i> بازگشت به لیست
            </a>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-person-badge"></i>
                {{ $personnel->full_name }}
            </h2>
            <p class="text-muted mb-0">کد پرسنلی: {{ $personnel->employee_code }}</p>
        </div>
        <div>
            @can('update', $personnel)
            <a href="{{ route('personnel.edit', $personnel) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> ویرایش
            </a>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        {{-- Personal Information --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle"></i> اطلاعات شخصی
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">نام کامل</label>
                            <p class="fw-bold mb-0">{{ $personnel->full_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">کد ملی</label>
                            <p class="fw-bold mb-0 font-monospace">{{ $personnel->national_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">کد پرسنلی</label>
                            <p class="fw-bold mb-0">
                                <code class="badge bg-light text-dark border">{{ $personnel->employee_code }}</code>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">جنسیت</label>
                            <p class="fw-bold mb-0">
                                @if($personnel->gender == 'male')
                                    <i class="bi bi-gender-male text-primary"></i> مرد
                                @elseif($personnel->gender == 'female')
                                    <i class="bi bi-gender-female text-danger"></i> زن
                                @else
                                    نامشخص
                                @endif
                            </p>
                        </div>
                        @if($personnel->birth_date)
                        <div class="col-md-6">
                            <label class="text-muted small">تاریخ تولد</label>
                            <p class="fw-bold mb-0">{{ $personnel->birth_date }}</p>
                        </div>
                        @endif
                        @if($personnel->father_name)
                        <div class="col-md-6">
                            <label class="text-muted small">نام پدر</label>
                            <p class="fw-bold mb-0">{{ $personnel->father_name }}</p>
                        </div>
                        @endif
                        @if($personnel->phone)
                        <div class="col-md-6">
                            <label class="text-muted small">شماره تماس</label>
                            <p class="fw-bold mb-0">
                                <i class="bi bi-telephone"></i>
                                {{ $personnel->phone }}
                            </p>
                        </div>
                        @endif
                        @if($personnel->email)
                        <div class="col-md-6">
                            <label class="text-muted small">ایمیل</label>
                            <p class="fw-bold mb-0">
                                <i class="bi bi-envelope"></i>
                                {{ $personnel->email }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Employment Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-briefcase"></i> اطلاعات استخدامی
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small">استان</label>
                            <p class="fw-bold mb-0">
                                <span class="badge bg-info-subtle text-info">
                                    {{ $personnel->province->name ?? 'نامشخص' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">وضعیت استخدام</label>
                            <p class="fw-bold mb-0">
                                @if($personnel->employment_status == 'active')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> شاغل
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-clock-history"></i> بازنشسته
                                </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">سابقه خدمت</label>
                            <p class="fw-bold mb-0">
                                <span class="badge bg-primary">
                                    {{ $personnel->service_years ?? 0 }} سال
                                </span>
                            </p>
                        </div>
                        @if($personnel->hire_date)
                        <div class="col-md-4">
                            <label class="text-muted small">تاریخ استخدام</label>
                            <p class="fw-bold mb-0">{{ $personnel->hire_date }}</p>
                        </div>
                        @endif
                        @if($personnel->department)
                        <div class="col-md-4">
                            <label class="text-muted small">دپارتمان</label>
                            <p class="fw-bold mb-0">{{ $personnel->department }}</p>
                        </div>
                        @endif
                        @if($personnel->service_location)
                        <div class="col-md-4">
                            <label class="text-muted small">محل خدمت</label>
                            <p class="fw-bold mb-0">{{ $personnel->service_location }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Family Members --}}
            @if($personnel->hasFamilyMembers())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people-fill"></i>
                        همراهان ({{ $personnel->getFamilyMembersCount() }} نفر)
                    </h5>
                    <div>
                        <span class="badge bg-success-subtle text-success">
                            <i class="bi bi-bank"></i> بانکی: {{ $personnel->getBankAffiliatedMembersCount() }}
                        </span>
                        <span class="badge bg-warning-subtle text-warning">
                            <i class="bi bi-person"></i> غیر بانکی: {{ $personnel->getNonBankAffiliatedMembersCount() }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">#</th>
                                    <th>نام کامل</th>
                                    <th>نسبت</th>
                                    <th>کد ملی</th>
                                    <th>تاریخ تولد</th>
                                    <th>جنسیت</th>
                                    <th class="text-center">نوع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($personnel->family_members as $index => $member)
                                @php
                                    $isBankAffiliated = \App\Models\Personnel::isFamilyMemberBankAffiliated($member['relation'] ?? '');
                                @endphp
                                <tr>
                                    <td class="px-4">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle {{ $isBankAffiliated ? 'bg-success' : 'bg-warning' }} text-white d-flex align-items-center justify-content-center me-2"
                                                 style="width: 32px; height: 32px; font-size: 12px;">
                                                {{ mb_substr($member['full_name'] ?? '', 0, 1) }}
                                            </div>
                                            <strong>{{ $member['full_name'] ?? 'نامشخص' }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ $member['relation'] ?? 'نامشخص' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="font-monospace text-muted">
                                            {{ $member['national_code'] ?? '-' }}
                                        </small>
                                    </td>
                                    <td>{{ $member['birth_date'] ?? '-' }}</td>
                                    <td>
                                        @if(($member['gender'] ?? '') == 'male')
                                            <i class="bi bi-gender-male text-primary"></i> مرد
                                        @elseif(($member['gender'] ?? '') == 'female')
                                            <i class="bi bi-gender-female text-danger"></i> زن
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($isBankAffiliated)
                                        <span class="badge bg-success">
                                            <i class="bi bi-bank"></i> بانکی
                                        </span>
                                        @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-person"></i> غیر بانکی
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-people fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-muted">بدون همراه</h5>
                    <p class="text-muted mb-0">این پرسنل همراهی ثبت نکرده است.</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Status Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    @if($personnel->is_isargar)
                    <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-shield-fill-check fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-success mb-2">ایثارگر</h5>
                    @if($personnel->isargar_type)
                    <p class="text-muted small mb-2">
                        نوع: {{ $personnel->isargar_type }}
                    </p>
                    @endif
                    @if($personnel->isargar_percentage)
                    <div class="badge bg-success-subtle text-success">
                        درصد جانبازی: {{ $personnel->isargar_percentage }}%
                    </div>
                    @endif
                    @else
                    <div class="rounded-circle bg-secondary-subtle text-secondary d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-person fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-secondary mb-2">عادی</h5>
                    <p class="text-muted small">ایثارگر نیست</p>
                    @endif
                </div>
            </div>

            {{-- Statistics --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up"></i> آمار
                    </h6>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-people text-primary"></i>
                            تعداد همراهان
                        </span>
                        <strong class="badge bg-primary">{{ $personnel->getFamilyMembersCount() }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-calendar-check text-info"></i>
                            تعداد رزروها
                        </span>
                        <strong class="badge bg-info">{{ $personnel->reservations_count }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-ticket-detailed text-warning"></i>
                            شرکت در قرعه‌کشی
                        </span>
                        <strong class="badge bg-warning">{{ $personnel->lottery_entries_count ?? 0 }}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-person-plus text-success"></i>
                            جمع کل افراد
                        </span>
                        <strong class="badge bg-success">{{ $personnel->getTotalPersonsCount() }}</strong>
                    </div>
                </div>
            </div>

            {{-- Registration Info --}}
            @if($personnel->registration_source)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-info-circle"></i> اطلاعات ثبت
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">منبع ثبت‌نام</label>
                        <p class="fw-bold mb-0">
                            @if($personnel->registration_source == 'bale_bot')
                                <span class="badge bg-info">
                                    <i class="bi bi-robot"></i> بات بله
                                </span>
                            @elseif($personnel->registration_source == 'web')
                                <span class="badge bg-primary">
                                    <i class="bi bi-globe"></i> وب
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-pencil"></i> دستی
                                </span>
                            @endif
                        </p>
                    </div>
                    @if($personnel->tracking_code)
                    <div class="mb-3">
                        <label class="text-muted small">کد پیگیری</label>
                        <p class="fw-bold mb-0">
                            <code class="badge bg-light text-dark border">{{ $personnel->tracking_code }}</code>
                        </p>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="text-muted small">تاریخ ایجاد</label>
                        <p class="fw-bold mb-0">{{ $personnel->created_at?->format('Y/m/d H:i') }}</p>
                    </div>
                    @if($personnel->updated_at != $personnel->created_at)
                    <div>
                        <label class="text-muted small">آخرین بروزرسانی</label>
                        <p class="fw-bold mb-0">{{ $personnel->updated_at?->format('Y/m/d H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
