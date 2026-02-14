@extends('layouts.app')

@section('title', 'مدیریت پرسنل')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-people-fill text-primary"></i>
                مدیریت پرسنل بانک ملی ایران
            </h2>
            <p class="text-muted mb-0">مشاهده و مدیریت اطلاعات کارکنان و همراهان آن‌ها</p>
        </div>
        @can('create', App\Models\Personnel::class)
        <a href="{{ route('personnel.create') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-circle"></i> افزودن پرسنل جدید
        </a>
        @endcan
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background: rgba(255, 107, 107, 0.1);">
                                <i class="bi bi-people fs-3 text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">کل پرسنل</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($personnel->total()) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background: rgba(38, 222, 129, 0.1);">
                                <i class="bi bi-check-circle fs-3 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">شاغل</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($personnel->where('employment_status', 'active')->count()) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background: rgba(254, 202, 87, 0.1);">
                                <i class="bi bi-shield-fill-check fs-3 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">ایثارگران</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($personnel->where('is_isargar', true)->count()) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background: rgba(0, 210, 211, 0.1);">
                                <i class="bi bi-calendar-check fs-3 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">دارای رزرو</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($personnel->where('reservations_count', '>', 0)->count()) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-funnel"></i> فیلترها و جستجو
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('personnel.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">جستجو</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="نام، کد پرسنلی، کد ملی..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                @if(!auth()->user()->hasRole('provincial_admin'))
                <div class="col-md-2">
                    <label class="form-label fw-bold">استان</label>
                    <select name="province_id" class="form-select">
                        <option value="">همه استان‌ها</option>
                        @foreach($provinces as $province)
                        <option value="{{ $province->id }}"
                                {{ request('province_id') == $province->id ? 'selected' : '' }}>
                            {{ $province->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label fw-bold">وضعیت استخدام</label>
                    <select name="status" class="form-select">
                        <option value="">همه</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            شاغل
                        </option>
                        <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>
                            بازنشسته
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">ایثارگری</label>
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" name="is_isargar" value="1"
                               class="form-check-input" id="filterIsargar"
                               {{ request()->boolean('is_isargar') ? 'checked' : '' }}>
                        <label class="form-check-label" for="filterIsargar">
                            فقط ایثارگران
                        </label>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">دارای همراه</label>
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" name="has_family" value="1"
                               class="form-check-input" id="filterFamily"
                               {{ request()->boolean('has_family') ? 'checked' : '' }}>
                        <label class="form-check-label" for="filterFamily">
                            دارای همراهان
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> اعمال فیلتر
                    </button>
                    <a href="{{ route('personnel.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> پاک کردن فیلترها
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i>
                لیست پرسنل ({{ number_format($personnel->total()) }} نفر)
            </h5>
            <div class="text-muted small">
                نمایش {{ $personnel->firstItem() ?? 0 }} تا {{ $personnel->lastItem() ?? 0 }}
            </div>
        </div>
        <div class="card-body p-0">
            @if($personnel->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-muted">هیچ پرسنلی یافت نشد</h5>
                    <p class="text-muted">فیلترهای خود را تغییر دهید یا پرسنل جدیدی اضافه کنید.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">کد پرسنلی</th>
                                <th>نام کامل</th>
                                <th>کد ملی</th>
                                <th>استان</th>
                                <th class="text-center">همراهان</th>
                                <th class="text-center">سابقه</th>
                                <th class="text-center">وضعیت</th>
                                <th class="text-center">رزروها</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($personnel as $person)
                            <tr>
                                <td class="px-4">
                                    <code class="badge bg-light text-dark border">
                                        {{ $person->employee_code }}
                                    </code>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                             style="width: 36px; height: 36px; font-size: 14px;">
                                            {{ mb_substr($person->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $person->full_name }}</div>
                                            @if($person->is_isargar)
                                            <span class="badge bg-success-subtle text-success small">
                                                <i class="bi bi-shield-fill-check"></i> ایثارگر
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted font-monospace">
                                        {{ $person->national_code }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        {{ $person->province->name ?? 'نامشخص' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($person->hasFamilyMembers())
                                        <span class="badge bg-primary" title="تعداد همراهان">
                                            <i class="bi bi-people-fill"></i>
                                            {{ $person->getFamilyMembersCount() }}
                                        </span>
                                        @if($person->getBankAffiliatedMembersCount() > 0)
                                        <span class="badge bg-success-subtle text-success small ms-1"
                                              title="بانکی">
                                            <i class="bi bi-bank"></i>
                                            {{ $person->getBankAffiliatedMembersCount() }}
                                        </span>
                                        @endif
                                        @if($person->getNonBankAffiliatedMembersCount() > 0)
                                        <span class="badge bg-warning-subtle text-warning small"
                                              title="غیر بانکی">
                                            <i class="bi bi-person"></i>
                                            {{ $person->getNonBankAffiliatedMembersCount() }}
                                        </span>
                                        @endif
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ $person->service_years ?? 0 }} سال
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($person->employment_status == 'active')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> شاغل
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-clock-history"></i> بازنشسته
                                    </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">
                                        {{ $person->reservations_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('personnel.show', $person) }}"
                                           class="btn btn-outline-info"
                                           title="مشاهده جزئیات">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $person)
                                        <a href="{{ route('personnel.edit', $person) }}"
                                           class="btn btn-outline-warning"
                                           title="ویرایش">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $person)
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                onclick="confirmDelete({{ $person->id }})"
                                                title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            نمایش {{ $personnel->firstItem() ?? 0 }} تا {{ $personnel->lastItem() ?? 0 }}
                            از {{ number_format($personnel->total()) }} نتیجه
                        </div>
                        <div>
                            {{ $personnel->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@can('delete', App\Models\Personnel::class)
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(id) {
    if (confirm('آیا از حذف این پرسنل اطمینان دارید؟')) {
        const form = document.getElementById('delete-form');
        form.action = '/personnel/' + id;
        form.submit();
    }
}
</script>
@endcan
@endsection
