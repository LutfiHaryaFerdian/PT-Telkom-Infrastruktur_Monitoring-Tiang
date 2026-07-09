<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Monitoring Infrastruktur Tiang</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Design System -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background-color: var(--color-background);
            color: var(--color-on-background);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        body::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(rgba(183, 1, 0, 0.05) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
        }

        .login-card {
            background: var(--color-surface-container-lowest);
            border: 1px solid var(--color-outline-variant);
            border-radius: var(--radius-large);
            box-shadow: var(--shadow-modal);
            width: 100%; max-width: 420px;
            padding: 2.5rem 2.5rem; position: relative; z-index: 1;
        }
        .login-logo {
            width: 64px; height: 64px; 
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            border-radius: var(--radius-default); 
            display: grid; place-items: center; margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(183, 1, 0, 0.25);
        }
        .login-title { font-size: 1.5rem; font-weight: 700; color: var(--color-on-surface); text-align: center; }
        .login-subtitle { font-size: .85rem; color: var(--color-on-surface-variant); text-align: center; margin-bottom: 2rem; }

        .form-control {
            border-radius: var(--radius-default); padding: .7rem 1rem; border: 1.5px solid var(--color-outline);
            background-color: var(--color-surface-container-low);
            color: var(--color-on-surface);
            font-size: .9rem; transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(183, 1, 0, 0.15);
            background-color: var(--color-surface-container-lowest);
        }
        .form-label { font-size: .85rem; font-weight: 600; color: var(--color-on-surface); }
        .input-group-text {
            border-radius: var(--radius-default) 0 0 var(--radius-default); border: 1.5px solid var(--color-outline);
            background-color: var(--color-surface-container-lowest); color: var(--color-on-surface-variant);
        }
        .input-group .form-control { border-radius: 0 var(--radius-default) var(--radius-default) 0; border-left: none; }
        .input-group:focus-within .input-group-text { border-color: var(--color-primary); }

        .btn-login {
            background: var(--color-primary);
            color: var(--color-on-primary); border: none; border-radius: var(--radius-default);
            padding: .75rem; font-weight: 600; font-size: .95rem;
            width: 100%; transition: all .2s; letter-spacing: .02em;
            box-shadow: 0 4px 12px rgba(183, 1, 0, 0.2);
        }
        .btn-login:hover {
            background: var(--color-primary-hover);
            transform: translateY(-1px); box-shadow: 0 6px 16px rgba(183, 1, 0, 0.3);
            color: var(--color-on-primary);
        }
        .btn-login:active { transform: translateY(0); }

        .divider { border-top: 1px solid var(--color-outline-variant); margin: 1.5rem 0; }
        .footer-text { font-size: .78rem; color: var(--color-on-surface-variant); text-align: center; }

        /* Animated background elements */
        .bg-circle {
            position: absolute; border-radius: 50%; opacity: .05;
            background: radial-gradient(circle, var(--color-primary), transparent);
            animation: float 8s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
    </style>
</head>
<body>
    <!-- Decorative circles -->
    <div class="bg-circle" style="width:400px;height:400px;top:-100px;right:-100px;animation-delay:-2s"></div>
    <div class="bg-circle" style="width:300px;height:300px;bottom:-80px;left:-80px;animation-delay:-5s"></div>

    <div class="login-card fade-in">
        <div class="login-logo">
            <i class="bi bi-broadcast-pin text-white fs-3"></i>
        </div>
        <h1 class="login-title">Monitoring Tiang</h1>
        <p class="login-subtitle">PT Telkom Infrastruktur Indonesia<br>District Lampung</p>

        @if($errors->any())
        <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.875rem">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input
                        type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="email@telkominf.com"
                        autofocus required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input
                        type="password" id="password" name="password"
                        class="form-control" placeholder="••••••••" required>
                </div>
            </div>
            <div class="mb-4 d-flex align-items-center">
                <input type="checkbox" id="remember" name="remember" class="form-check-input me-2">
                <label for="remember" class="form-check-label" style="font-size:.85rem;color:#6c757d;">Ingat saya</label>
            </div>
            <button type="submit" class="btn-login mb-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
            <a href="{{ route('landing') }}" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center" style="border-radius: var(--radius-default); font-size: .9rem; font-weight: 500; height: 42px;">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
            </a>
        </form>

        <div class="divider"></div>
        <p class="footer-text">© {{ date('Y') }} PT Telkom Infrastruktur Indonesia. All rights reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>.fade-in { animation: fadeIn .4s ease; } @keyframes fadeIn { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }</style>
</body>
</html>
