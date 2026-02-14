{{-- Personnel Guests Management Tab (همراهان پرسنل) --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-person-badge"></i> همراهان پرسنل
        </h5>
        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addPersonnelGuestModal">
            <i class="bi bi-plus-circle"></i> افزودن همراه پرسنل
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>توضیح:</strong>
            اگر همراهان شما (همسر، فرزند، پدر و...) خودشان هم پرسنل بانک ملی هستند، در این بخش اضافه کنید.
            <br>
            <small class="text-muted">مثال: زن و شوهر هر دو پرسنل / پدر و دختر هر دو پرسنل</small>
        </div>

        <div id="personnelGuestsList" class="mb-3">
            <div class="text-center text-muted py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Personnel Guest Modal --}}
<div class="modal fade" id="addPersonnelGuestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge-fill"></i> افزودن همراه پرسنل
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPersonnelGuestForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>توجه:</strong> فقط می‌توانید پرسنل‌های فعال بانک ملی را به عنوان همراه اضافه کنید.
                    </div>

                    <div class="row g-3">
                        {{-- Search Personnel --}}
                        <div class="col-12">
                            <label class="form-label">جستجوی پرسنل <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="personnelSearchInput"
                                   class="form-control"
                                   placeholder="کد پرسنلی، کد ملی یا نام پرسنل را وارد کنید..."
                                   autocomplete="off">
                            <div class="invalid-feedback"></div>

                            {{-- Search Results Dropdown --}}
                            <div id="personnelSearchResults" class="list-group mt-2" style="display: none; max-height: 300px; overflow-y: auto;"></div>

                            {{-- Selected Personnel Display --}}
                            <div id="selectedPersonnelDisplay" class="mt-3" style="display: none;">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1" id="selectedPersonnelName"></h6>
                                                <small class="text-muted">
                                                    کد پرسنلی: <span id="selectedPersonnelCode"></span> |
                                                    کد ملی: <span id="selectedPersonnelNational"></span>
                                                </small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSelectedPersonnel()">
                                                <i class="bi bi-x"></i> حذف
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden input for selected personnel ID --}}
                            <input type="hidden" name="guest_personnel_id" id="guestPersonnelId">
                        </div>

                        {{-- Relation --}}
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
                                <optgroup label="متفرقه">
                                    <option value="دوست">دوست</option>
                                    <option value="فامیل">فامیل</option>
                                    <option value="سایر">سایر</option>
                                </optgroup>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label">یادداشت</label>
                            <textarea name="notes" class="form-control" rows="2" maxlength="500"></textarea>
                            <div class="form-text">حداکثر 500 کاراکتر</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> افزودن
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const personnelId = {{ $personnel->id }};
    let searchTimeout = null;
    let selectedPersonnelData = null;

    // Load personnel guests list
    loadPersonnelGuestsList();

    // Personnel search with debounce
    document.getElementById('personnelSearchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();

        if (query.length < 2) {
            document.getElementById('personnelSearchResults').style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            searchPersonnel(query);
        }, 300);
    });

    // Add personnel guest form submission
    document.getElementById('addPersonnelGuestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addPersonnelGuest();
    });

    // Search personnel
    function searchPersonnel(query) {
        fetch(`/personnel/${personnelId}/personnel-guests/search?query=${encodeURIComponent(query)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('personnelSearchResults');

            if (data.success && data.data.length > 0) {
                resultsDiv.innerHTML = data.data.map(p => `
                    <button type="button" class="list-group-item list-group-item-action" onclick="selectPersonnel(${JSON.stringify(p).replace(/"/g, '&quot;')})">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${p.full_name}</h6>
                                <small class="text-muted">کد پرسنلی: ${p.employee_code} | کد ملی: ${p.national_code}</small>
                            </div>
                            <i class="bi bi-arrow-left"></i>
                        </div>
                    </button>
                `).join('');
                resultsDiv.style.display = 'block';
            } else {
                resultsDiv.innerHTML = `
                    <div class="list-group-item text-center text-muted">
                        <i class="bi bi-search"></i> پرسنلی یافت نشد
                    </div>
                `;
                resultsDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطا در جستجو');
        });
    }

    // Select personnel from search results
    window.selectPersonnel = function(personnel) {
        selectedPersonnelData = personnel;

        document.getElementById('guestPersonnelId').value = personnel.id;
        document.getElementById('selectedPersonnelName').textContent = personnel.full_name;
        document.getElementById('selectedPersonnelCode').textContent = personnel.employee_code;
        document.getElementById('selectedPersonnelNational').textContent = personnel.national_code;

        document.getElementById('personnelSearchInput').value = '';
        document.getElementById('personnelSearchResults').style.display = 'none';
        document.getElementById('selectedPersonnelDisplay').style.display = 'block';
    };

    // Clear selected personnel
    window.clearSelectedPersonnel = function() {
        selectedPersonnelData = null;
        document.getElementById('guestPersonnelId').value = '';
        document.getElementById('selectedPersonnelDisplay').style.display = 'none';
    };

    // Load personnel guests list
    function loadPersonnelGuestsList() {
        fetch(`/personnel/${personnelId}/personnel-guests`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderPersonnelGuestsList(data.data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('personnelGuestsList').innerHTML = `
                <div class="alert alert-danger">خطا در بارگذاری لیست</div>
            `;
        });
    }

    // Render personnel guests list
    function renderPersonnelGuestsList(guests) {
        const listDiv = document.getElementById('personnelGuestsList');

        if (guests.length === 0) {
            listDiv.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-person-badge" style="font-size: 3rem;"></i>
                    <p class="mt-2">هنوز همراه پرسنلی اضافه نشده است</p>
                </div>
            `;
            return;
        }

        listDiv.innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>کد پرسنلی</th>
                            <th>نام کامل</th>
                            <th>کد ملی</th>
                            <th>نسبت</th>
                            <th>استان</th>
                            <th>تاریخ افزودن</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${guests.map(guest => `
                            <tr>
                                <td>${guest.employee_code}</td>
                                <td><strong>${guest.full_name}</strong></td>
                                <td><span class="font-monospace">${guest.national_code}</span></td>
                                <td><span class="badge bg-primary">${guest.relation}</span></td>
                                <td><small class="text-muted">${guest.province || '-'}</small></td>
                                <td><small class="text-muted">${guest.added_at || '-'}</small></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deletePersonnelGuest(${guest.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    // Add personnel guest
    function addPersonnelGuest() {
        const formData = new FormData(document.getElementById('addPersonnelGuestForm'));

        fetch(`/personnel/${personnelId}/personnel-guests`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'همراه پرسنل با موفقیت اضافه شد');
                bootstrap.Modal.getInstance(document.getElementById('addPersonnelGuestModal')).hide();
                document.getElementById('addPersonnelGuestForm').reset();
                clearSelectedPersonnel();
                loadPersonnelGuestsList();
            } else {
                alert(data.message || 'خطا در افزودن همراه پرسنل');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطا در افزودن همراه پرسنل');
        });
    }

    // Delete personnel guest
    window.deletePersonnelGuest = function(guestId) {
        if (!confirm('آیا از حذف این همراه پرسنل مطمئن هستید؟')) {
            return;
        }

        fetch(`/personnel/${personnelId}/personnel-guests/${guestId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'همراه پرسنل با موفقیت حذف شد');
                loadPersonnelGuestsList();
            } else {
                alert(data.message || 'خطا در حذف');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطا در حذف همراه پرسنل');
        });
    };
});
</script>
