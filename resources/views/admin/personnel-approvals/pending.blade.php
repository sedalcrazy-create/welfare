@extends('layouts.app')

@section('title', 'تأیید درخواست‌های پرسنل')

@section('content')
<div class="page-header">
    <h1>
        <i class="bi bi-person-check"></i>
        تأیید درخواست‌های پرسنل
    </h1>
</div>

{{-- Statistics --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card warning">
            <i class="stat-icon bi bi-hourglass-split"></i>
            <div class="stat-value">{{ $stats['total_pending'] }}</div>
            <div class="stat-label">در انتظار بررسی</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card success">
            <i class="stat-icon bi bi-check-circle"></i>
            <div class="stat-value">{{ $stats['total_approved_today'] }}</div>
            <div class="stat-label">تأیید شده امروز</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card danger">
            <i class="stat-icon bi bi-x-circle"></i>
            <div class="stat-value">{{ $stats['total_rejected_today'] }}</div>
            <div class="stat-label">رد شده امروز</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> فیلترها
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.personnel-approvals.pending') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">جستجو</label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="نام، کد ملی، کد پیگیری">
                </div>
                <div class="col-md-2">
                    <label for="center_id" class="form-label">مرکز</label>
                    <select class="form-control" id="center_id" name="center_id">
                        <option value="">همه</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}" {{ request('center_id') == $center->id ? 'selected' : '' }}>
                                {{ $center->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="province_id" class="form-label">استان</label>
                    <select class="form-control" id="province_id" name="province_id">
                        <option value="">همه</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>
                                {{ $province->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="source" class="form-label">منبع</label>
                    <select class="form-control" id="source" name="source">
                        <option value="">همه</option>
                        <option value="web" {{ request('source') == 'web' ? 'selected' : '' }}>وب</option>
                        <option value="bale_bot" {{ request('source') == 'bale_bot' ? 'selected' : '' }}>بات بله</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> فیلتر
                    </button>
                    <a href="{{ route('admin.personnel-approvals.pending') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> پاک کردن
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Requests Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul"></i> لیست درخواست‌ها ({{ $requests->total() }} مورد)</span>
        @if($requests->count() > 0)
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('approve')">
                <i class="bi bi-check-all"></i> تأیید گروهی
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('reject')">
                <i class="bi bi-x-lg"></i> رد گروهی
            </button>
        </div>
        @endif
    </div>
    <div class="card-body">
        @if($requests->count() > 0)
        <div class="table-container">
            <form id="bulkActionForm">
                <table>
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
                            <th>کد پیگیری</th>
                            <th>نام</th>
                            <th>کد ملی</th>
                            <th>تلفن</th>
                            <th>مرکز</th>
                            <th>دوره</th>
                            <th>تعداد خانواده</th>
                            <th>استان</th>
                            <th>منبع</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                        <tr>
                            <td>
                                <input type="checkbox" name="personnel_ids[]" value="{{ $request->id }}" class="request-checkbox">
                            </td>
                            <td><code>{{ $request->tracking_code }}</code></td>
                            <td><strong>{{ $request->full_name }}</strong></td>
                            <td><code>{{ $request->national_code }}</code></td>
                            <td>{{ $request->phone }}</td>
                            <td>
                                @if($request->preferredCenter)
                                    <span class="badge badge-info">{{ $request->preferredCenter->name }}</span>
                                @endif
                            </td>
                            <td>
                                @if($request->preferredPeriod)
                                    <small>{{ jdate($request->preferredPeriod->start_date)->format('Y/m/d') }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $request->family_count }} نفر</span>
                            </td>
                            <td>
                                @if($request->province)
                                    <small>{{ $request->province->name }}</small>
                                @endif
                            </td>
                            <td>
                                @if($request->registration_source === 'bale_bot')
                                    <span class="badge badge-info">بات بله</span>
                                @else
                                    <span class="badge badge-secondary">وب</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ jdate($request->created_at)->format('Y/m/d H:i') }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.personnel-approvals.show', $request) }}"
                                       class="btn btn-sm btn-info"
                                       title="مشاهده جزئیات">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.personnel-approvals.approve', $request) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm btn-success"
                                                title="تأیید"
                                                onclick="return confirm('آیا از تأیید این درخواست اطمینان دارید؟')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                    <button type="button"
                                            class="btn btn-sm btn-danger"
                                            title="رد"
                                            onclick="showRejectModal({{ $request->id }}, '{{ $request->full_name }}')">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $requests->links() }}
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
            <p class="mt-3">درخواست در انتظار بررسی وجود ندارد</p>
        </div>
        @endif
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 16px;">
            <form method="POST" id="rejectForm">
                @csrf
                <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle text-danger"></i>
                        رد درخواست: <span id="rejectPersonnelName"></span>
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
<script>
// Select all checkboxes
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.request-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

// Show reject modal
function showRejectModal(personnelId, personnelName) {
    document.getElementById('rejectPersonnelName').textContent = personnelName;
    document.getElementById('rejectForm').action = `/admin/personnel-approvals/${personnelId}/reject`;
    document.getElementById('rejection_reason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

// Bulk actions
function bulkAction(action) {
    const form = document.getElementById('bulkActionForm');
    const checkedBoxes = form.querySelectorAll('.request-checkbox:checked');

    if (checkedBoxes.length === 0) {
        alert('لطفاً حداقل یک درخواست را انتخاب کنید');
        return;
    }

    if (action === 'approve') {
        if (!confirm(`آیا از تأیید ${checkedBoxes.length} درخواست اطمینان دارید؟`)) {
            return;
        }
        form.action = '{{ route("admin.personnel-approvals.bulk-approve") }}';
        form.method = 'POST';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        form.submit();
    } else if (action === 'reject') {
        const reason = prompt('لطفاً دلیل رد را وارد کنید (حداقل 10 کاراکتر):');
        if (!reason || reason.length < 10) {
            alert('دلیل رد باید حداقل 10 کاراکتر باشد');
            return;
        }
        form.action = '{{ route("admin.personnel-approvals.bulk-reject") }}';
        form.method = 'POST';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'rejection_reason';
        reasonInput.value = reason;
        form.appendChild(reasonInput);
        form.submit();
    }
}
</script>
@endpush
