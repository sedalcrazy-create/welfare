{{-- Guests Management Tab --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-people"></i> مهمانان
        </h5>
        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addGuestModal">
            <i class="bi bi-plus-circle"></i> افزودن مهمان
        </button>
    </div>
    <div class="card-body">
        <div id="guestsList" class="mb-3">
            <div class="text-center text-muted py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Guest Modal --}}
<div class="modal fade" id="addGuestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i> افزودن مهمان
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addGuestForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>توجه:</strong> اگر این مهمان قبلاً در سیستم ثبت شده باشد، با وارد کردن کد ملی به لیست شما اضافه می‌شود.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">کد ملی <span class="text-danger">*</span></label>
                            <input type="text" name="national_code" class="form-control" maxlength="10" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">نام کامل <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">نسبت <span class="text-danger">*</span></label>
                            <select name="relation" class="form-select" required>
                                <option value="">انتخاب کنید</option>
                                <optgroup label="خانواده بانکی (تعرفه کمتر)">
                                    <option value="همسر">همسر</option>
                                    <option value="فرزند">فرزند</option>
                                    <option value="پدر">پدر</option>
                                    <option value="مادر">مادر</option>
                                    <option value="پدر همسر">پدر همسر</option>
                                    <option value="مادر همسر">مادر همسر</option>
                                </optgroup>
                                <optgroup label="متفرقه (تعرفه بیشتر)">
                                    <option value="دوست">دوست</option>
                                    <option value="فامیل">فامیل</option>
                                    <option value="سایر">سایر</option>
                                </optgroup>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">جنسیت</label>
                            <select name="gender" class="form-select">
                                <option value="">انتخاب کنید</option>
                                <option value="male">مرد</option>
                                <option value="female">زن</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">تاریخ تولد</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">شماره تماس</label>
                            <input type="text" name="phone" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">یادداشت</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-check-circle"></i> ذخیره
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const personnelId = {{ $personnel->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Load guests on page load
    loadGuests();

    // Add guest form submit
    document.getElementById('addGuestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch(`/personnel/${personnelId}/guests`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('addGuestModal')).hide();

                // Reset form
                document.getElementById('addGuestForm').reset();

                // Reload guests
                loadGuests();

                // Show success toast
                showToast('success', data.message);
            } else {
                showToast('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'خطا در افزودن مهمان');
        });
    });

    function loadGuests() {
        const guestsList = document.getElementById('guestsList');

        fetch(`/personnel/${personnelId}/guests`, {
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.guests.length === 0) {
                guestsList.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-people fs-1"></i>
                        <p class="mt-2">هیچ مهمانی ثبت نشده است.</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-hover align-middle">';
            html += '<thead><tr>';
            html += '<th>نام کامل</th>';
            html += '<th>کد ملی</th>';
            html += '<th>نسبت</th>';
            html += '<th>نوع</th>';
            html += '<th>جنسیت</th>';
            html += '<th>تاریخ تولد</th>';
            html += '<th>عملیات</th>';
            html += '</tr></thead><tbody>';

            data.guests.forEach(guest => {
                html += '<tr>';
                html += `<td><strong>${guest.full_name}</strong></td>`;
                html += `<td><code>${guest.national_code}</code></td>`;
                html += `<td>${guest.relation}</td>`;
                html += `<td><span class="badge bg-${guest.badge_class}">${guest.badge_text}</span></td>`;
                html += `<td>${guest.gender === 'male' ? 'مرد' : guest.gender === 'female' ? 'زن' : '-'}</td>`;
                html += `<td>${guest.birth_date || '-'}</td>`;
                html += `<td>
                    <button class="btn btn-sm btn-danger" onclick="removeGuest(${guest.id})">
                        <i class="bi bi-trash"></i> حذف
                    </button>
                </td>`;
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            guestsList.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            guestsList.innerHTML = `
                <div class="alert alert-danger">
                    خطا در بارگذاری لیست مهمانان
                </div>
            `;
        });
    }

    // Remove guest function
    window.removeGuest = function(guestId) {
        if (!confirm('آیا از حذف این مهمان اطمینان دارید؟')) {
            return;
        }

        fetch(`/personnel/${personnelId}/guests/${guestId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadGuests();
                showToast('success', data.message);
            } else {
                showToast('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'خطا در حذف مهمان');
        });
    };

    function showToast(type, message) {
        // Simple toast notification (you can replace with your preferred notification library)
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const toast = document.createElement('div');
        toast.className = `alert ${alertClass} position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});
</script>
@endpush
