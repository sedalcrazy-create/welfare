@extends('layouts.app')

@section('title', 'مدیریت کاربران')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-people-fill"></i> مدیریت کاربران
            </h2>
            <p class="text-muted mb-0">مشاهده و مدیریت کاربران سیستم</p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> کاربر جدید
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> لیست کاربران
                </h5>
                <span class="badge bg-primary">{{ $users->total() }} کاربر</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>نام</th>
                                <th>ایمیل</th>
                                <th>نقش</th>
                                <th>وضعیت</th>
                                <th>تاریخ عضویت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-info badge-sm">شما</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-monospace text-muted">{{ $user->email }}</span>
                                    </td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-{{ $role->name === 'super_admin' ? 'danger' : ($role->name === 'admin' ? 'warning' : ($role->name === 'provincial_admin' ? 'success' : 'secondary')) }}">
                                                {{ match($role->name) {
                                                    'super_admin' => 'مدیر سیستم',
                                                    'admin' => 'ادمین',
                                                    'provincial_admin' => 'مدیر استانی',
                                                    'operator' => 'اپراتور',
                                                    'user' => 'کاربر',
                                                    default => $role->name
                                                } }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> فعال
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> غیرفعال
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $user->created_at->format('Y/m/d') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.users.show', $user) }}"
                                               class="btn btn-outline-info"
                                               title="نمایش">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                               class="btn btn-outline-warning"
                                               title="ویرایش">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.toggle-status', $user) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('آیا از تغییر وضعیت این کاربر مطمئن هستید؟')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="btn btn-outline-{{ $user->is_active ? 'secondary' : 'success' }}"
                                                            title="{{ $user->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}">
                                                        <i class="bi bi-{{ $user->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.users.destroy', $user) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('آیا از حذف این کاربر مطمئن هستید؟ این عملیات غیرقابل بازگشت است!')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-outline-danger"
                                                            title="حذف">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-outline-secondary" disabled title="نمی‌توانید خودتان را حذف کنید">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="card-footer bg-white border-top">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-people" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">هیچ کاربری یافت نشد</p>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> ساخت کاربر جدید
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
