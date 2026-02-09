<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود - سامانه رفاهی بانک ملی ایران</title>
    <link href="{{ asset('assets/css/vazirmatn.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap-icons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            /* Vibrant Sunset Theme - Colorful & Artistic */
            --primary: #ff6b6b;           /* Coral Red */
            --primary-dark: #ee5253;      /* Deep Coral */
            --secondary: #00d2d3;         /* Turquoise */
            --accent: #feca57;            /* Golden Yellow */
            --purple: #a55eea;            /* Vibrant Purple */
            --deep-charcoal: #2d3436;     /* Deep Charcoal */
            --light-coral: #ffeaa7;       /* Light Coral */
            --primary-gradient: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
            --secondary-gradient: linear-gradient(135deg, #00d2d3 0%, #a55eea 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazirmatn', 'Tahoma', 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 20% 30%, rgba(255, 107, 107, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 80% 70%, rgba(0, 210, 211, 0.12) 0%, transparent 40%),
                        radial-gradient(circle at 60% 20%, rgba(254, 202, 87, 0.15) 0%, transparent 35%),
                        radial-gradient(circle at 30% 80%, rgba(165, 94, 234, 0.1) 0%, transparent 35%),
                        linear-gradient(135deg, #fff9f0 0%, #f0fffe 50%, #fff5f5 100%);
            direction: rtl;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            animation: float-shape 20s ease-in-out infinite;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            top: -150px;
            right: -100px;
            animation-duration: 25s;
        }

        .shape-2 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--purple) 100%);
            bottom: -100px;
            left: -100px;
            animation-duration: 30s;
            animation-delay: -5s;
        }

        .shape-3 {
            width: 250px;
            height: 250px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--primary) 100%);
            top: 50%;
            left: 50%;
            animation-duration: 35s;
            animation-delay: -10s;
        }

        .shape-4 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, var(--purple) 0%, var(--secondary) 100%);
            top: 60%;
            right: 10%;
            animation-duration: 28s;
            animation-delay: -15s;
        }

        @keyframes float-shape {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(30px, -30px) rotate(90deg); }
            50% { transform: translate(-20px, 20px) rotate(180deg); }
            75% { transform: translate(40px, 10px) rotate(270deg); }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        /* Login Card */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 0 25px 80px rgba(255, 107, 107, 0.15),
                        0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 50px 40px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            animation: slideUp 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 50%, var(--secondary) 100%);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Logo Section */
        .login-logo {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-glow {
            position: absolute;
            width: 180%;
            height: 180%;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 107, 107, 0.4) 0%, rgba(254, 202, 87, 0.3) 40%, transparent 70%);
            animation: pulse-glow 2.5s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.2); opacity: 1; }
        }

        .logo-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid;
            animation: rotate-ring 8s linear infinite;
        }

        .ring-1 {
            width: 130px;
            height: 130px;
            border-color: rgba(255, 107, 107, 0.5);
            border-style: dashed;
            animation-duration: 6s;
        }

        .ring-2 {
            width: 150px;
            height: 150px;
            border-color: rgba(0, 210, 211, 0.4);
            border-style: dotted;
            animation-duration: 10s;
            animation-direction: reverse;
        }

        @keyframes rotate-ring {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .logo-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fff5f5 0%, #ff6b6b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 50px rgba(255, 107, 107, 0.35),
                        inset 0 3px 10px rgba(255, 255, 255, 0.5);
            border: 3px solid rgba(255, 255, 255, 0.95);
            position: relative;
            z-index: 10;
            animation: logo-float 3s ease-in-out infinite;
        }

        @keyframes logo-float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-8px) scale(1.03); }
        }

        .logo-circle img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .login-title {
            font-size: 1.6rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #6b7280;
            font-size: 0.95rem;
        }

        /* Alert Styles */
        .alert {
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: shake 0.5s ease-in-out;
            border-right: 4px solid;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #ef4444;
            color: #991b1b;
        }

        .alert i {
            font-size: 1.2rem;
            margin-top: 2px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #374151;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.15);
        }

        .form-control:focus + i {
            color: var(--primary);
        }

        /* Checkbox */
        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .form-check-label {
            color: #6b7280;
            cursor: pointer;
            user-select: none;
        }

        /* Submit Button */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255, 107, 107, 0.45);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb;
        }

        .login-footer p {
            color: #9ca3af;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-footer i {
            color: var(--primary);
        }

        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading .btn-text {
            visibility: hidden;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 25px;
                border-radius: 24px;
            }

            .logo-container {
                width: 100px;
                height: 100px;
            }

            .logo-circle {
                width: 80px;
                height: 80px;
            }

            .logo-circle img {
                width: 65px;
                height: 65px;
            }

            .ring-1 {
                width: 110px;
                height: 110px;
            }

            .ring-2 {
                width: 130px;
                height: 130px;
            }

            .login-title {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background Shapes -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="login-logo">
                <div class="logo-container">
                    <div class="logo-glow"></div>
                    <div class="logo-ring ring-1"></div>
                    <div class="logo-ring ring-2"></div>
                    <div class="logo-circle">
                        <img src="{{ asset('logo.png') }}" alt="بانک ملی ایران">
                    </div>
                </div>
                <h1 class="login-title">سامانه مراکز رفاهی</h1>
                <p class="login-subtitle">بانک ملی ایران</p>
            </div>

            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">ایمیل</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="example@bankmelli.ir">
                        <i class="bi bi-envelope"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">رمز عبور</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" class="form-control" required placeholder="رمز عبور خود را وارد کنید">
                        <i class="bi bi-lock"></i>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">مرا به خاطر بسپار</label>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-text">ورود به سامانه</span>
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>
                    <i class="bi bi-building"></i>
                    اداره کل رفاه و درمان
                </p>
            </div>
        </div>
    </div>

    <script>
        // Add loading state on form submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loginBtn').classList.add('loading');
        });
    </script>
</body>
</html>
