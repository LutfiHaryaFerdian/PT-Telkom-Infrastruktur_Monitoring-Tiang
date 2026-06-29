<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Monitoring Infrastruktur Tiang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #1a3a5c 0%, #0d2035 50%, #1e0a2e 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        body::before {
            content: ''; position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .login-card {
            background: rgba(255,255,255,.97); border-radius: 20px;
            box-shadow: 0 24px 60px rgba(0,0,0,.3); width: 100%; max-width: 420px;
            padding: 2.5rem 2.5rem; position: relative; z-index: 1;
        }
        .login-logo {
            width: 64px; height: 64px; background: linear-gradient(135deg, #e8402a, #c73420);
            border-radius: 16px; display: grid; place-items: center; margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(232,64,42,.35);
        }
        .login-title { font-size: 1.5rem; font-weight: 700; color: #1a3a5c; text-align: center; }
        .login-subtitle { font-size: .85rem; color: #6c757d; text-align: center; margin-bottom: 2rem; }

        .form-control {
            border-radius: 10px; padding: .7rem 1rem; border: 1.5px solid #e2e8f0;
            font-size: .9rem; transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: #1a3a5c; box-shadow: 0 0 0 3px rgba(26,58,92,.12);
        }
        .form-label { font-size: .85rem; font-weight: 600; color: #374151; }
        .input-group-text {
            border-radius: 10px 0 0 10px; border: 1.5px solid #e2e8f0;
            background: #f8fafc; color: #6c757d;
        }
        .input-group .form-control { border-radius: 0 10px 10px 0; border-left: none; }
        .input-group:focus-within .input-group-text { border-color: #1a3a5c; }

        .btn-login {
            background: linear-gradient(135deg, #1a3a5c, #1e4d7b);
            color: #fff; border: none; border-radius: 10px;
            padding: .75rem; font-weight: 600; font-size: .95rem;
            width: 100%; transition: all .2s; letter-spacing: .02em;
            box-shadow: 0 4px 12px rgba(26,58,92,.3);
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #1e4d7b, #245e96);
            transform: translateY(-1px); box-shadow: 0 6px 16px rgba(26,58,92,.35);
            color: #fff;
        }
        .btn-login:active { transform: translateY(0); }

        .divider { border-top: 1px solid #f0f2f5; margin: 1.5rem 0; }
        .footer-text { font-size: .78rem; color: #9ca3af; text-align: center; }

        /* Animated background elements */
        .bg-circle {
            position: absolute; border-radius: 50%; opacity: .07;
            background: radial-gradient(circle, #e8402a, transparent);
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
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        <div class="divider"></div>
        <p class="footer-text">© {{ date('Y') }} PT Telkom Infrastruktur Indonesia. All rights reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>.fade-in { animation: fadeIn .4s ease; } @keyframes fadeIn { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:none; } }</style>
</body>
</html>
