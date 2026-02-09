@extends('layouts.app')

@section('title', 'جزئیات معرفی‌نامه')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">جزئیات معرفی‌نامه</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('introduction-letters.print', $introductionLetter) }}"
               class="btn btn-primary"
               target="_blank">
                <i class="bi bi-printer"></i> چاپ / دانلود
            </a>
            <a href="{{ route('introduction-letters.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-right"></i> بازگشت به لیست
            </a>
        </div>
    </div>

    {{-- Letter Code & Status --}}
    <div class="card mb-4 border-primary">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="mb-0">
                        <i class="bi bi-file-earmark-text text-primary"></i>
                        <strong>کد معرفی‌نامه:</strong>
                        <span class="text-primary">{{ $introductionLetter->letter_code }}</span>
                    </h3>
                </div>
                <div class="col-md-6 text-end">
                    @if($introductionLetter->status === 'active')
                        <span class="badge bg-success" style="font-size: 1.2rem; padding: 10px 20px;">
                            <i class="bi bi-check-circle"></i> فعال
                        </span>
                    @elseif($introductionLetter->status === 'used')
                        <span class="badge bg-secondary" style="font-size: 1.2rem; padding: 10px 20px;">
                            <i class="bi bi-check-all"></i> استفاده شده
                        </span>
                    @elseif($introductionLetter->status === 'cancelled')
                        <span class="badge bg-danger" style="font-size: 1.2rem; padding: 10px 20px;">
                            <i class="bi bi-x-circle"></i> لغو شده
                        </span>
                    @else
                        <span class="badge bg-warning" style="font-size: 1.2rem; padding: 10px 20px;">
                            <i class="bi bi-clock-history"></i> منقضی شده
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Personnel Information --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-person-badge"></i> اطلاعات پرسنل
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">نام و نام خانوادگی:</th>
                            <td><strong>{{ $introductionLetter->personnel->full_name }}</strong></td>
                        </tr>
                        <tr>
                            <th>کد ملی:</th>
                            <td><code>{{ $introductionLetter->personnel->national_code }}</code></td>
                        </tr>
                        <tr>
                            <th>شماره تماس:</th>
                            <td>{{ $introductionLetter->personnel->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>تعداد خانواده:</th>
                            <td>
                                <i class="bi bi-people-fill"></i>
                                <strong>{{ $introductionLetter->family_count }} نفر</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Center Information --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-building"></i> اطلاعات مرکز رفاهی
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">نام مرکز:</th>
                            <td><strong>{{ $introductionLetter->center->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>شهر:</th>
                            <td>{{ $introductionLetter->center->city }}</td>
                        </tr>
                        <tr>
                            <th>نوع:</th>
                            <td>
                                @if($introductionLetter->center->type === 'religious')
                                    <span class="badge bg-success">مذهبی</span>
                                @elseif($introductionLetter->center->type === 'beach')
                                    <span class="badge bg-info">ساحلی</span>
                                @else
                                    <span class="badge bg-secondary">کوهستانی</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>مدت اقامت:</th>
                            <td>{{ $introductionLetter->center->stay_duration ?? '-' }} شب</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Issue Information --}}
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="bi bi-info-circle"></i> اطلاعات صدور
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>صادرکننده:</strong>
                    <p class="mb-0">{{ $introductionLetter->issuedBy->name }}</p>
                </div>
                <div class="col-md-4">
                    <strong>تاریخ صدور:</strong>
                    <p class="mb-0">{{ jdate($introductionLetter->issued_at)->format('l j F Y - H:i') }}</p>
                </div>
                <div class="col-md-4">
                    <strong>زمان استفاده:</strong>
                    <p class="mb-0">
                        @if($introductionLetter->used_at)
                            {{ jdate($introductionLetter->used_at)->format('l j F Y - H:i') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>

            @if($introductionLetter->notes)
                <hr>
                <div>
                    <strong>یادداشت:</strong>
                    <p class="mb-0 mt-2">{{ $introductionLetter->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Cancellation Info (if cancelled) --}}
    @if($introductionLetter->status === 'cancelled')
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle"></i> اطلاعات لغو
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>تاریخ لغو:</strong>
                        <p>{{ jdate($introductionLetter->cancelled_at)->format('l j F Y - H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>دلیل لغو:</strong>
                        <p>{{ $introductionLetter->cancellation_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Actions --}}
    @if($introductionLetter->isActive())
        <div class="card">
            <div class="card-header bg-warning">
                <i class="bi bi-tools"></i> عملیات
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('introduction-letters.mark-as-used', $introductionLetter) }}"
                              onsubmit="return confirm('آیا از علامت‌گذاری این معرفی‌نامه به عنوان استفاده شده اطمینان دارید؟')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-all"></i> علامت‌گذاری به عنوان استفاده شده
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle"></i> لغو معرفی‌نامه
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Cancel Modal --}}
@if($introductionLetter->isActive())
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('introduction-letters.cancel', $introductionLetter) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-exclamation-triangle"></i> لغو معرفی‌نامه
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i>
                            با لغو این معرفی‌نامه، سهمیه به حساب شما بازگردانده خواهد شد.
                        </div>

                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label">
                                دلیل لغو <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control"
                                      id="cancellation_reason"
                                      name="cancellation_reason"
                                      rows="3"
                                      required
                                      placeholder="لطفاً دلیل لغو را وارد کنید..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check-circle"></i> تأیید لغو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
