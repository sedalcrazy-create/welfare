@extends('layouts.app')

@section('title', 'کنترل ثبت نام')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="bi bi-shield-lock"></i>
            کنترل ثبت نام
        </h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRuleModal">
            <i class="bi bi-plus-circle"></i> افزودن قانون جدید
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="bi bi-info-circle"></i> راهنما
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Global:</strong> کل سیستم را مسدود یا فعال می‌کند (بالاترین اولویت)</li>
                <li><strong>Date Range:</strong> ثبت نام را در بازه زمانی مشخص کنترل می‌کند</li>
                <li><strong>Center:</strong> ثبت نام برای مرکز خاص را کنترل می‌کند</li>
                <li><strong>Period:</strong> ثبت نام برای دوره خاص را کنترل می‌کند</li>
                <li><strong>اولویت:</strong> Global → Date Range → Center → Period</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($controls->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>وضعیت</th>
                                <th>نوع</th>
                                <th>توضیحات</th>
                                <th>مجوز ثبت نام</th>
                                <th>پیام</th>
                                <th>ایجاد شده</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($controls as $control)
                                <tr class="{{ !$control->is_active ? 'table-secondary' : '' }}">
                                    <td>
                                        <form method="POST"
                                              action="{{ route('admin.registration-control.toggle', $control) }}"
                                              style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-{{ $control->is_active ? 'success' : 'secondary' }}">
                                                <i class="bi bi-{{ $control->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                                {{ $control->is_active ? 'فعال' : 'غیرفعال' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        @php
                                            $typeLabels = [
                                                'global' => ['label' => 'کل سیستم', 'color' => 'danger'],
                                                'date_range' => ['label' => 'بازه زمانی', 'color' => 'warning'],
                                                'center' => ['label' => 'مرکز', 'color' => 'info'],
                                                'period' => ['label' => 'دوره', 'color' => 'primary'],
                                            ];
                                            $typeInfo = $typeLabels[$control->rule_type] ?? ['label' => $control->rule_type, 'color' => 'secondary'];
                                        @endphp
                                        <span class="badge bg-{{ $typeInfo['color'] }}">{{ $typeInfo['label'] }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $control->description }}</strong>
                                    </td>
                                    <td>
                                        @if($control->allow_registration)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> مجاز
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> مسدود
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($control->message)
                                            <small class="text-muted">{{ Str::limit($control->message, 50) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ jdate($control->created_at)->ago() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button"
                                                    class="btn btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editRuleModal{{ $control->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST"
                                                  action="{{ route('admin.registration-control.destroy', $control) }}"
                                                  style="display: inline;"
                                                  onsubmit="return confirm('آیا از حذف این قانون اطمینان دارید؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editRuleModal{{ $control->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.registration-control.update', $control) }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">ویرایش قانون</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @if($control->rule_type === 'date_range')
                                                        <div class="mb-3">
                                                            <label class="form-label">از تاریخ</label>
                                                            <input type="text"
                                                                   class="form-control datepicker"
                                                                   name="start_date"
                                                                   value="{{ $control->start_date }}"
                                                                   required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">تا تاریخ</label>
                                                            <input type="text"
                                                                   class="form-control datepicker"
                                                                   name="end_date"
                                                                   value="{{ $control->end_date }}"
                                                                   required>
                                                        </div>
                                                    @endif

                                                    <div class="mb-3">
                                                        <label class="form-label">مجوز ثبت نام</label>
                                                        <select class="form-select" name="allow_registration" required>
                                                            <option value="1" {{ $control->allow_registration ? 'selected' : '' }}>
                                                                مجاز (فعال)
                                                            </option>
                                                            <option value="0" {{ !$control->allow_registration ? 'selected' : '' }}>
                                                                مسدود (غیرفعال)
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">پیام نمایشی</label>
                                                        <textarea class="form-control"
                                                                  name="message"
                                                                  rows="3"
                                                                  maxlength="500"
                                                                  placeholder="پیامی که به کاربر نمایش داده می‌شود...">{{ $control->message }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                                    <button type="submit" class="btn btn-primary">ذخیره</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($controls->hasPages())
                    <div class="mt-3">
                        {{ $controls->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-3">هیچ قانونی تعریف نشده است</p>
                    <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createRuleModal">
                        <i class="bi bi-plus-circle"></i> افزودن اولین قانون
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Create Rule Modal --}}
<div class="modal fade" id="createRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.registration-control.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> افزودن قانون جدید
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نوع قانون <span class="text-danger">*</span></label>
                        <select class="form-select" name="rule_type" id="rule_type" required onchange="toggleRuleFields()">
                            <option value="">-- انتخاب کنید --</option>
                            <option value="global">Global - کل سیستم</option>
                            <option value="date_range">Date Range - بازه زمانی</option>
                            <option value="center">Center - مرکز خاص</option>
                            <option value="period">Period - دوره خاص</option>
                        </select>
                    </div>

                    <div id="date_range_fields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">از تاریخ</label>
                            <input type="text" class="form-control datepicker" name="start_date" placeholder="1404/12/01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تا تاریخ</label>
                            <input type="text" class="form-control datepicker" name="end_date" placeholder="1404/12/15">
                        </div>
                    </div>

                    <div id="center_field" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">مرکز</label>
                            <select class="form-select" name="center_id">
                                <option value="">-- انتخاب کنید --</option>
                                @foreach($centers as $center)
                                    <option value="{{ $center->id }}">{{ $center->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="period_field" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">دوره</label>
                            <select class="form-select" name="period_id">
                                <option value="">-- انتخاب کنید --</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}">
                                        {{ $period->center->name }} - {{ jdate($period->start_date)->format('Y/m/d') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">مجوز ثبت نام <span class="text-danger">*</span></label>
                        <select class="form-select" name="allow_registration" required>
                            <option value="1">مجاز (فعال)</option>
                            <option value="0">مسدود (غیرفعال)</option>
                        </select>
                        <div class="form-text">آیا ثبت نام در این قانون مجاز است یا خیر</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">پیام نمایشی</label>
                        <textarea class="form-control"
                                  name="message"
                                  rows="3"
                                  maxlength="500"
                                  placeholder="پیامی که به کاربر نمایش داده می‌شود..."></textarea>
                        <div class="form-text">مثال: "ثبت نام تا 15 اسفند بسته است"</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> ایجاد قانون
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleRuleFields() {
        const ruleType = document.getElementById('rule_type').value;

        // Hide all
        document.getElementById('date_range_fields').style.display = 'none';
        document.getElementById('center_field').style.display = 'none';
        document.getElementById('period_field').style.display = 'none';

        // Show relevant
        if (ruleType === 'date_range') {
            document.getElementById('date_range_fields').style.display = 'block';
        } else if (ruleType === 'center') {
            document.getElementById('center_field').style.display = 'block';
        } else if (ruleType === 'period') {
            document.getElementById('period_field').style.display = 'block';
        }
    }
</script>
@endpush
