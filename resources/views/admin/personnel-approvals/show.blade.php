@extends('layouts.app')

@section('title', 'جزئیات درخواست - ' . $personnel->full_name)

@section('content')
<div class="page-header">
    <h1>
        <i class="bi bi-person-badge"></i>
        جزئیات درخواست پرسنل
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.personnel-approvals.pending') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-right"></i> بازگشت
        </a>
    </div>
</div>

{{-- Status Alert --}}
<div class="alert {{ $personnel->status === 'pending' ? 'alert-warning' : ($personnel->status === 'approved' ? 'alert-success' : 'alert-danger') }}">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-info-circle"></i>
            <strong>وضعیت:</strong>
            @if($personnel->status === 'pending')
                <span class="badge badge-warning">در انتظار بررسی</span>
            @elseif($personnel->status === 'approved')
                <span class="badge badge-success">تأیید شده</span>
            @else
                <span class="badge badge-danger">رد شده</span>
            @endif
            |
            <strong>کد پیگیری:</strong> <code>{{ $personnel->tracking_code }}</code>
        </div>
        @if($personnel->status === 'pending')
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.personnel-approvals.approve', $personnel) }}" style="display: inline;">
                @csrf
                <button type="submit"
                        class="btn btn-success"
                        onclick="return confirm('آیا از تأیید این درخواست اطمینان دارید؟')">
                    <i class="bi bi-check-circle"></i> تأیید درخواست
                </button>
            </form>
            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#rejectModal">
                <i class="bi bi-x-circle"></i> رد درخواست
            </button>
        </div>
        @endif
    </div>
</div>

<div class="row">
    {{-- Personnel Information --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-fill"></i> اطلاعات پرسنل
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">کد پرسنلی</label>
                        <p class="fw-bold">{{ $personnel->employee_code }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">نام و نام خانوادگی</label>
                        <p class="fw-bold">{{ $personnel->full_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">کد ملی</label>
                        <p class="fw-bold"><code>{{ $personnel->national_code }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">شماره تماس</label>
                        <p class="fw-bold">{{ $personnel->phone }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">تعداد خانواده</label>
                        <p class="fw-bold">
                            <span class="badge badge-info" style="font-size: 1rem;">{{ $personnel->family_count }} نفر</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">استان / اداره امور</label>
                        <p class="fw-bold">
                            @if($personnel->province)
                                {{ $personnel->province->name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Family Members --}}
        @if($personnel->family_members && count($personnel->family_members) > 0)
        <div class="card">
            <div class="card-header">
                <i class="bi bi-people-fill"></i> همراهان ({{ count($personnel->family_members) }} نفر)
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام و نام خانوادگی</th>
                                <th>نسبت</th>
                                <th>کد ملی</th>
                                <th>جنسیت</th>
                                <th>تاریخ تولد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($personnel->family_members as $index => $member)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $member['full_name'] }}</strong></td>
                                <td><span class="badge badge-info">{{ $member['relation'] }}</span></td>
                                <td><code>{{ $member['national_code'] }}</code></td>
                                <td>{{ $member['gender'] === 'male' ? 'مرد' : 'زن' }}</td>
                                <td>{{ $member['birth_date'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($personnel->notes)
        <div class="card">
            <div class="card-header">
                <i class="bi bi-chat-left-text"></i> یادداشت / توضیحات
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $personnel->notes }}</p>
            </div>
        </div>
        @endif

        {{-- Rejection Reason --}}
        @if($personnel->status === 'rejected' && $personnel->rejection_reason)
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle"></i> دلیل رد
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $personnel->rejection_reason }}</p>
            </div>
        </div>
        @endif

        {{-- Introduction Letters --}}
        @if($personnel->introductionLetters && $personnel->introductionLetters->count() > 0)
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-text"></i> معرفی‌نامه‌های صادر شده ({{ $personnel->introductionLetters->count() }})
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>کد معرفی‌نامه</th>
                                <th>مرکز</th>
                                <th>وضعیت</th>
                                <th>تاریخ صدور</th>
                                <th>صادر کننده</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($personnel->introductionLetters as $letter)
                            <tr>
                                <td><code>{{ $letter->letter_code }}</code></td>
                                <td>{{ $letter->center->name ?? '-' }}</td>
                                <td>
                                    @if($letter->status === 'active')
                                        <span class="badge badge-success">فعال</span>
                                    @elseif($letter->status === 'used')
                                        <span class="badge badge-secondary">استفاده شده</span>
                                    @else
                                        <span class="badge badge-danger">لغو شده</span>
                                    @endif
                                </td>
                                <td>{{ jdate($letter->issued_at)->format('Y/m/d H:i') }}</td>
                                <td>{{ $letter->issuedBy->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('introduction-letters.show', $letter) }}"
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> مشاهده
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-md-4">
        {{-- Preferences --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-heart-fill"></i> انتخاب‌های مورد نظر
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">مرکز رفاهی</label>
                    @if($personnel->preferredCenter)
                        <p class="fw-bold">
                            <i class="bi bi-building text-primary"></i>
                            {{ $personnel->preferredCenter->name }}<br>
                            <small class="text-muted">{{ $personnel->preferredCenter->city }}</small>
                        </p>
                    @else
                        <p class="text-muted">-</p>
                    @endif
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">دوره اقامت</label>
                    @if($personnel->preferredPeriod)
                        <p class="fw-bold">
                            <i class="bi bi-calendar-range text-success"></i>
                            {{ jdate($personnel->preferredPeriod->start_date)->format('Y/m/d') }}<br>
                            تا {{ jdate($personnel->preferredPeriod->end_date)->format('Y/m/d') }}
                        </p>
                    @else
                        <p class="text-muted">-</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Registration Info --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> اطلاعات ثبت
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">منبع ثبت</label>
                    <p>
                        @if($personnel->registration_source === 'bale_bot')
                            <span class="badge badge-info">بات بله</span>
                        @elseif($personnel->registration_source === 'web')
                            <span class="badge badge-secondary">وب</span>
                        @else
                            <span class="badge badge-secondary">دستی</span>
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">تاریخ ثبت</label>
                    <p class="fw-bold">{{ jdate($personnel->created_at)->format('Y/m/d H:i') }}</p>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted">آخرین ویرایش</label>
                    <p class="fw-bold">{{ jdate($personnel->updated_at)->format('Y/m/d H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 16px;">
            <form method="POST" action="{{ route('admin.personnel-approvals.reject', $personnel) }}">
                @csrf
                <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle text-danger"></i>
                        رد درخواست: {{ $personnel->full_name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        لطفاً دلیل رد درخواست را به صورت واضح بیان کنید.
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">
                            دلیل رد <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control"
                                  id="rejection_reason"
                                  name="rejection_reason"
                                  rows="4"
                                  required
                                  minlength="10"
                                  maxlength="500"
                                  placeholder="مثال: مدارک ارسالی ناقص است"></textarea>
                        <div class="form-text">حداقل 10 و حداکثر 500 کاراکتر</div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> رد درخواست
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
