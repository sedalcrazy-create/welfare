@extends('layouts.app')

@section('title', 'مراکز رفاهی')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-building"></i> مراکز رفاهی</h1>
    <a href="{{ route('centers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i>
        افزودن مرکز جدید
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('centers.index') }}" method="GET" class="row gap-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">جستجو</label>
                <input type="text" name="search" class="form-control"
                       placeholder="نام مرکز یا شهر..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">نوع مرکز</label>
                <select name="type" class="form-control">
                    <option value="">همه</option>
                    <option value="religious" {{ request('type') === 'religious' ? 'selected' : '' }}>زیارتی</option>
                    <option value="beach" {{ request('type') === 'beach' ? 'selected' : '' }}>ساحلی</option>
                    <option value="mountain" {{ request('type') === 'mountain' ? 'selected' : '' }}>کوهستانی</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">وضعیت</label>
                <select name="status" class="form-control">
                    <option value="">همه</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعال</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i> جستجو
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Centers List -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul"></i>
        لیست مراکز رفاهی
        <span class="badge badge-info" style="margin-right: auto;">{{ $centers->total() }} مرکز</span>
    </div>
    <div class="card-body">
        @if($centers->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام مرکز</th>
                            <th>شهر</th>
                            <th>نوع</th>
                            <th>تعداد واحد</th>
                            <th>تعداد تخت</th>
                            <th>مدت اقامت</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($centers as $center)
                            <tr>
                                <td>{{ $loop->iteration + ($centers->currentPage() - 1) * $centers->perPage() }}</td>
                                <td>
                                    <strong>{{ $center->name }}</strong>
                                </td>
                                <td>{{ $center->city }}</td>
                                <td>
                                    @switch($center->type)
                                        @case('religious')
                                            <span class="badge badge-info">زیارتی</span>
                                            @break
                                        @case('beach')
                                            <span class="badge badge-success">ساحلی</span>
                                            @break
                                        @case('mountain')
                                            <span class="badge badge-warning">کوهستانی</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ number_format($center->unit_count) }}</td>
                                <td>{{ number_format($center->bed_count) }}</td>
                                <td>{{ $center->stay_duration }} شب</td>
                                <td>
                                    @if($center->is_active)
                                        <span class="badge badge-success">فعال</span>
                                    @else
                                        <span class="badge badge-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('centers.show', $center) }}"
                                           class="btn btn-sm btn-info" title="مشاهده">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('centers.edit', $center) }}"
                                           class="btn btn-sm btn-warning" title="ویرایش">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('centers.toggle-status', $center) }}"
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-secondary"
                                                    title="{{ $center->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}">
                                                <i class="bi bi-{{ $center->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('centers.destroy', $center) }}"
                                              method="POST" style="display: inline;"
                                              onsubmit="return confirm('آیا از حذف این مرکز اطمینان دارید؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $centers->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-building" style="font-size: 4rem; color: #ccc;"></i>
                <p class="mt-3 text-muted">هیچ مرکز رفاهی یافت نشد.</p>
                <a href="{{ route('centers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> افزودن اولین مرکز
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-sm {
        padding: 6px 10px;
        font-size: 0.85rem;
    }

    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .gap-2 {
        gap: 0.5rem;
    }

    table td .d-flex {
        justify-content: center;
    }
</style>
@endpush
