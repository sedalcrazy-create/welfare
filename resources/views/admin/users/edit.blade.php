@extends('layouts.app')

@section('title', 'ویرایش کاربر')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-right"></i> بازگشت
            </a>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-pencil-square"></i> ویرایش کاربر
            </h2>
            <p class="text-muted mb-0">{{ $user->name }}</p>
        </div>
        <div>
            @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.destroy', $user) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('آیا از حذف این کاربر مطمئن هستید؟ این عملیات غیرقابل بازگشت است!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> حذف کاربر
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> اطلاعات کاربر
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- اطلاعات پایه --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    نام کامل <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    ایمیل <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>توجه:</strong> فقط در صورتی که می‌خواهید رمز عبور را تغییر دهید، فیلدهای زیر را پر کنید. در غیر این صورت خالی بگذارید.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    رمز عبور جدید
                                </label>
                                <input type="password"
                                       name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       minlength="8"
                                       placeholder="حداقل 8 کاراکتر">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    تکرار رمز عبور جدید
                                </label>
                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control"
                                       minlength="8"
                                       placeholder="تکرار رمز عبور">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    نقش <span class="text-danger">*</span>
                                </label>
                                <select name="role"
                                        class="form-select @error('role') is-invalid @enderror"
                                        required
                                        {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}"
                                                {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                            {{ match($role->name) {
                                                'super_admin' => '🔴 مدیر سیستم',
                                                'admin' => '🟡 ادمین اداره کل',
                                                'provincial_admin' => '🟢 مدیر استانی',
                                                'operator' => '🔵 اپراتور',
                                                'user' => '⚪ کاربر عادی',
                                                default => $role->name
                                            } }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($user->id === auth()->id())
                                    <input type="hidden" name="role" value="{{ $user->roles->first()?->name }}">
                                    <div class="form-text">نمی‌توانید نقش خودتان را تغییر دهید</div>
                                @endif
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">وضعیت</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox"
                                           name="is_active"
                                           class="form-check-input"
                                           id="is_active"
                                           value="1"
                                           {{ $user->id === auth()->id() ? 'disabled checked' : (old('is_active', $user->is_active) ? 'checked' : '') }}>
                                    <label class="form-check-label" for="is_active">
                                        فعال
                                    </label>
                                    @if($user->id === auth()->id())
                                        <input type="hidden" name="is_active" value="1">
                                        <div class="form-text">نمی‌توانید خودتان را غیرفعال کنید</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- سهمیه مراکز --}}
                        <hr>
                        <h6 class="mb-3">
                            <i class="bi bi-bank"></i> سهمیه مراکز
                        </h6>
                        <div class="row g-3 mb-4">
                            @foreach($centers as $center)
                                <div class="col-md-4">
                                    <label class="form-label">{{ $center->name }}</label>
                                    <div class="input-group">
                                        <input type="number"
                                               name="quotas[{{ $center->id }}]"
                                               class="form-control"
                                               min="0"
                                               max="999"
                                               value="{{ old('quotas.' . $center->id, $userQuotas[$center->id] ?? 0) }}"
                                               placeholder="0">
                                        <span class="input-group-text">نفر</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- دکمه‌ها --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> انصراف
                            </a>
                            <button type="submit" class="btn btn-warning text-white">
                                <i class="bi bi-check-circle"></i> بروزرسانی
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- اطلاعات اضافی --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle"></i> اطلاعات ثبت
                    </h6>
                    <hr>
                    <p class="small mb-2">
                        <strong>تاریخ عضویت:</strong><br>
                        {{ $user->created_at->format('Y/m/d H:i') }}
                    </p>
                    <p class="small mb-2">
                        <strong>آخرین بروزرسانی:</strong><br>
                        {{ $user->updated_at->format('Y/m/d H:i') }}
                    </p>
                    <p class="small mb-0">
                        <strong>شناسه:</strong> {{ $user->id }}
                    </p>
                </div>
            </div>

            @if($user->centerQuotas->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-pie-chart"></i> استفاده از سهمیه
                        </h6>
                        <hr>
                        @foreach($user->centerQuotas as $quota)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-bold">{{ $quota->center->name }}</small>
                                    <small class="text-muted">{{ $quota->used_quota }}/{{ $quota->total_quota }}</small>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $quota->used_quota >= $quota->total_quota ? 'danger' : 'success' }}"
                                         style="width: {{ $quota->total_quota > 0 ? ($quota->used_quota / $quota->total_quota * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
