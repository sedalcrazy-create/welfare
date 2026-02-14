@extends('layouts.app')

@section('title', 'ساخت کاربر جدید')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-right"></i> بازگشت
            </a>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-person-plus-fill"></i> ساخت کاربر جدید
            </h2>
            <p class="text-muted mb-0">افزودن کاربر جدید به سیستم</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> اطلاعات کاربر
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        {{-- اطلاعات پایه --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    نام کامل <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       required
                                       placeholder="مثال: علی احمدی">
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
                                       value="{{ old('email') }}"
                                       required
                                       placeholder="مثال: user@bankmelli.ir">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    رمز عبور <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       required
                                       minlength="8"
                                       placeholder="حداقل 8 کاراکتر">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">حداقل 8 کاراکتر</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    تکرار رمز عبور <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control"
                                       required
                                       minlength="8"
                                       placeholder="تکرار رمز عبور">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    نقش <span class="text-danger">*</span>
                                </label>
                                <select name="role"
                                        class="form-select @error('role') is-invalid @enderror"
                                        required>
                                    <option value="">انتخاب کنید</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}"
                                                {{ old('role') === $role->name ? 'selected' : '' }}>
                                            {{ match($role->name) {
                                                'super_admin' => '🔴 مدیر سیستم (دسترسی کامل)',
                                                'admin' => '🟡 ادمین اداره کل',
                                                'provincial_admin' => '🟢 مدیر استانی',
                                                'operator' => '🔵 اپراتور (فقط مشاهده)',
                                                'user' => '⚪ کاربر عادی',
                                                default => $role->name
                                            } }}
                                        </option>
                                    @endforeach
                                </select>
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
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        فعال
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- سهمیه مراکز --}}
                        <hr>
                        <h6 class="mb-3">
                            <i class="bi bi-bank"></i> سهمیه مراکز (اختیاری)
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
                                               value="{{ old('quotas.' . $center->id, 0) }}"
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
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> ذخیره کاربر
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- راهنما --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle"></i> راهنما
                    </h6>
                    <hr>
                    <h6 class="small fw-bold">نقش‌های کاربری:</h6>
                    <ul class="small">
                        <li><strong>مدیر سیستم:</strong> دسترسی کامل به همه بخش‌ها</li>
                        <li><strong>ادمین:</strong> مدیریت مراکز، قرعه‌کشی‌ها و گزارشات</li>
                        <li><strong>مدیر استانی:</strong> تأیید/رد درخواست‌های استان خودش</li>
                        <li><strong>اپراتور:</strong> فقط مشاهده و ورود اطلاعات</li>
                        <li><strong>کاربر:</strong> ثبت‌نام و مشاهده نتایج</li>
                    </ul>

                    <hr>
                    <h6 class="small fw-bold">سهمیه مراکز:</h6>
                    <p class="small mb-0">
                        سهمیه تعداد دفعاتی است که کاربر می‌تواند در هر مرکز رزرو کند.
                        می‌توانید بعداً هم سهمیه تخصیص دهید.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
