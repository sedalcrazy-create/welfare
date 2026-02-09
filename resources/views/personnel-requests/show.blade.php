@extends('layouts.app')

@section('title', 'جزئیات درخواست')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">جزئیات درخواست</h2>
        <a href="{{ route('personnel-requests.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-right"></i> بازگشت
        </a>
    </div>

    <div class="row">
        {{-- Request Details --}}
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">اطلاعات درخواست</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">کد پیگیری:</th>
                            <td><code class="fs-5">{{ $personnelRequest->tracking_code }}</code></td>
                        </tr>
                        <tr>
                            <th>نام و نام خانوادگی:</th>
                            <td><strong>{{ $personnelRequest->full_name }}</strong></td>
                        </tr>
                        <tr>
                            <th>کد ملی:</th>
                            <td dir="ltr" class="text-start">{{ $personnelRequest->national_code }}</td>
                        </tr>
                        <tr>
                            <th>شماره تماس:</th>
                            <td dir="ltr" class="text-start">{{ $personnelRequest->phone }}</td>
                        </tr>
                        <tr>
                            <th>تعداد اعضای خانواده:</th>
                            <td><span class="badge bg-info fs-6">{{ $personnelRequest->family_count }} نفر</span></td>
                        </tr>
                        <tr>
                            <th>مرکز مورد نظر:</th>
                            <td><strong>{{ $personnelRequest->preferredCenter?->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>استان:</th>
                            <td>{{ $personnelRequest->province?->name ?? 'نامشخص' }}</td>
                        </tr>
                        <tr>
                            <th>منبع ثبت:</th>
                            <td>
                                @if($personnelRequest->registration_source === 'bale_bot')
                                    <span class="badge bg-primary">بات بله</span>
                                @elseif($personnelRequest->registration_source === 'manual')
                                    <span class="badge bg-secondary">دستی</span>
                                @else
                                    <span class="badge bg-info">وب</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>تاریخ ثبت:</th>
                            <td dir="ltr" class="text-start">{{ jdate($personnelRequest->created_at)->format('Y/m/d H:i') }}</td>
                        </tr>
                        @if($personnelRequest->notes)
                            <tr>
                                <th>یادداشت:</th>
                                <td>{{ $personnelRequest->notes }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Introduction Letters --}}
            @if($personnelRequest->introductionLetters->isNotEmpty())
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">معرفی‌نامه‌های صادر شده</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>کد معرفی‌نامه</th>
                                        <th>مرکز</th>
                                        <th>تعداد خانواده</th>
                                        <th>تاریخ صدور</th>
                                        <th>صادرکننده</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($personnelRequest->introductionLetters as $letter)
                                        <tr>
                                            <td><code>{{ $letter->letter_code }}</code></td>
                                            <td>{{ $letter->center->name }}</td>
                                            <td>{{ $letter->family_count }} نفر</td>
                                            <td dir="ltr" class="text-start">{{ jdate($letter->issued_at)->format('Y/m/d H:i') }}</td>
                                            <td>{{ $letter->issuedBy->name }}</td>
                                            <td>
                                                @if($letter->status === 'active')
                                                    <span class="badge bg-success">فعال</span>
                                                @elseif($letter->status === 'used')
                                                    <span class="badge bg-secondary">استفاده شده</span>
                                                @elseif($letter->status === 'cancelled')
                                                    <span class="badge bg-danger">لغو شده</span>
                                                @else
                                                    <span class="badge bg-warning">منقضی شده</span>
                                                @endif
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

        {{-- Actions Panel --}}
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">وضعیت درخواست</h5>
                </div>
                <div class="card-body text-center">
                    @if($personnelRequest->status === 'pending')
                        <span class="badge bg-warning text-dark fs-5 mb-3">در انتظار بررسی</span>
                    @elseif($personnelRequest->status === 'approved')
                        <span class="badge bg-success fs-5 mb-3">تأیید شده</span>
                    @else
                        <span class="badge bg-danger fs-5 mb-3">رد شده</span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">عملیات</h5>
                </div>
                <div class="card-body">
                    @if($personnelRequest->status === 'pending')
                        {{-- Approve Button --}}
                        <form action="{{ route('personnel-requests.approve', $personnelRequest) }}" method="POST" class="mb-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('آیا از تأیید این درخواست اطمینان دارید؟')">
                                <i class="bi bi-check-circle"></i> تأیید درخواست
                            </button>
                        </form>

                        {{-- Reject Button --}}
                        <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle"></i> رد درخواست
                        </button>

                        {{-- Edit Button --}}
                        <a href="{{ route('personnel-requests.edit', $personnelRequest) }}" class="btn btn-warning w-100">
                            <i class="bi bi-pencil"></i> ویرایش
                        </a>
                    @endif

                    @if($personnelRequest->status === 'approved')
                        <a href="{{ route('introduction-letters.create', ['personnel_id' => $personnelRequest->id]) }}" class="btn btn-primary w-100">
                            <i class="bi bi-file-earmark-text"></i> صدور معرفی‌نامه
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('personnel-requests.reject', $personnelRequest) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">رد درخواست</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">دلیل رد درخواست: <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-danger">رد درخواست</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
