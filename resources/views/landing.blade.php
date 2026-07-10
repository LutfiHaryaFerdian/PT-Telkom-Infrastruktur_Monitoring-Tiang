<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TIF District Lampung — PT Telkom Infrastruktur Indonesia</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>

    <style>
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Navbar custom behavior */
        .navbar-landing {
            background-color: #ffffff;
            border-bottom: 1px solid var(--color-outline-variant);
            transition: box-shadow 0.3s ease;
        }
        
        .navbar-landing .nav-link {
            font-size: 14px;
            font-weight: 500;
            color: var(--color-on-surface);
            padding: 0.5rem 1rem !important;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }
        
        .navbar-landing .nav-link:hover,
        .navbar-landing .nav-link.active {
            color: var(--color-primary);
            border-bottom-color: var(--color-primary);
        }

        /* Hero section custom motif background */
        .hero-section {
            background-color: var(--color-background);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48'%3E%3Cpath d='M0 24h48M24 0v48' stroke='%23e9bcb5' stroke-width='0.5' fill='none'/%3E%3Ccircle cx='24' cy='24' r='2' fill='%23e9bcb5'/%3E%3Ccircle cx='0' cy='0' r='1.5' fill='%23e9bcb5'/%3E%3Ccircle cx='48' cy='0' r='1.5' fill='%23e9bcb5'/%3E%3Ccircle cx='0' cy='48' r='1.5' fill='%23e9bcb5'/%3E%3Ccircle cx='48' cy='48' r='1.5' fill='%23e9bcb5'/%3E%3C/svg%3E");
            background-size: 48px 48px;
            position: relative;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--color-background);
            opacity: 0.75; /* So network lines are 0.25 opacity */
            pointer-events: none;
        }
        
        .hero-section > .container {
            position: relative;
            z-index: 1;
        }

        /* Mockup layout styles */
        .mockup-container {
            background: #1e293b;
            border-radius: 16px;
            padding: 20px;
            min-height: 320px;
            box-shadow: var(--shadow-modal);
            position: relative;
        }

        .mockup-header-bar {
            display: flex;
            gap: 6px;
            margin-bottom: 12px;
        }

        .mockup-bar-red {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ef4444;
        }

        .mockup-bar-blue {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #3b82f6;
        }

        .mockup-map {
            background: #0f172a;
            border-radius: 8px;
            min-height: 230px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Grid stats items */
        .stats-divider {
            border-right: 1px solid var(--color-outline-variant);
        }

        /* Feature cards */
        .feature-card {
            background: var(--color-surface-container-lowest);
            border: 1px solid var(--color-outline-variant);
            border-radius: 12px;
            padding: 28px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .feature-icon-wrapper {
            background: var(--color-on-primary-container);
            width: 48px;
            height: 48px;
            border-radius: var(--radius-default);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            color: var(--color-primary);
        }

        .feature-icon-wrapper i {
            font-size: 22px;
        }

        /* Alur Kerja step */
        .step-col {
            position: relative;
        }

        .step-number {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--color-primary);
            color: #ffffff;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            position: relative;
            z-index: 2;
        }

        .step-line {
            position: absolute;
            top: 24px;
            left: 50%;
            width: 100%;
            border-top: 2px dashed var(--color-outline-variant);
            z-index: 1;
        }

        /* Status legend item */
        .legend-card {
            background: var(--color-surface-container-lowest);
            border: 1px solid var(--color-outline-variant);
            border-radius: var(--radius-default);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .legend-dot-ok {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #198754;
        }

        .legend-dot-warning {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ffc107;
        }

        .legend-dot-broken {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #dc3545;
        }

        /* Simulated Map points */
        .simulated-marker {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.5);
            animation: pulse-marker 2s infinite ease-in-out;
        }

        @keyframes pulse-marker {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(1); opacity: 0.8; }
        }

        /* Social icons footer */
        .social-icon {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.1);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(243,240,239,0.7);
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .social-icon:hover {
            background: rgba(255,255,255,0.2);
            color: #ffffff;
        }

        /* Responsive overrides */
        @media (max-width: 767.98px) {
            .navbar-collapse {
                background: #ffffff;
                padding: 1rem;
                border-radius: var(--radius-md);
                box-shadow: var(--shadow-hover);
                margin-top: 0.5rem;
            }
            .hero-section {
                padding-top: 48px !important;
                padding-bottom: 48px !important;
            }
            .stats-divider {
                border-right: none;
                border-bottom: 1px solid var(--color-outline-variant);
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }
            .stats-divider:last-child {
                border-bottom: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }
            .step-line {
                display: none;
            }
            .step-col {
                margin-bottom: 2rem;
            }
            .step-col:last-child {
                margin-bottom: 0;
            }
        }

        /* ══════════════════════════════════════════════════════════
           ANIMATION SYSTEM — TIF Landing Page
           ══════════════════════════════════════════════════════════ */

        /* 1. SCROLL-REVEAL base state */
        .reveal-up {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
            will-change: opacity, transform;
        }
        .reveal-up.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger delays */
        .reveal-delay-1  { transition-delay: 0.10s; }
        .reveal-delay-2  { transition-delay: 0.20s; }
        .reveal-delay-3  { transition-delay: 0.30s; }
        .reveal-delay-4  { transition-delay: 0.40s; }
        .reveal-delay-5  { transition-delay: 0.50s; }
        .reveal-delay-6  { transition-delay: 0.60s; }

        /* 7. PAGE-LOAD hero (class added at init, no IntersectionObserver) */
        .hero-load {
            opacity: 0;
            transform: translateY(20px);
            animation: heroFadeIn 0.65s ease-out forwards;
        }
        .hero-load-1 { animation-delay: 0.05s; }
        .hero-load-2 { animation-delay: 0.18s; }
        .hero-load-3 { animation-delay: 0.30s; }
        .hero-load-4 { animation-delay: 0.44s; }
        @keyframes heroFadeIn {
            to { opacity: 1; transform: translateY(0); }
        }

        /* 3a. Feature card icon micro-interaction */
        .feature-icon-wrapper {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .feature-card:hover .feature-icon-wrapper {
            transform: scale(1.07) rotate(-3deg);
            box-shadow: 0 6px 18px rgba(183,1,0,0.15);
        }

        /* 3b. Legend-card hover */
        .legend-card {
            transition: border-color 0.22s ease, box-shadow 0.22s ease, transform 0.22s ease;
        }
        .legend-card:hover {
            border-color: var(--color-primary);
            box-shadow: 0 4px 14px rgba(183,1,0,0.12);
            transform: translateX(4px);
        }

        /* 3c. CTA button scale + shadow */
        .btn-cta-hero {
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        }
        .btn-cta-hero:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 20px rgba(183,1,0,0.30) !important;
        }

        /* 4. Step-number pop-bounce animation */
        .step-number {
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }
        .step-col.step-visible .step-number {
            animation: stepPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        @keyframes stepPop {
            0%   { transform: scale(0.75); opacity: 0; }
            100% { transform: scale(1);    opacity: 1; }
        }
        .step-col.step-visible:nth-child(1) .step-number { animation-delay: 0.10s; }
        .step-col.step-visible:nth-child(2) .step-number { animation-delay: 0.28s; }
        .step-col.step-visible:nth-child(3) .step-number { animation-delay: 0.46s; }
        .step-col.step-visible:nth-child(4) .step-number { animation-delay: 0.64s; }

        /* 4. Step-line grow animation */
        .step-line {
            transform-origin: left center;
            transform: scaleX(0);
            transition: transform 0.5s ease-out;
        }
        .step-col.step-visible .step-line {
            transform: scaleX(1);
        }
        .step-col.step-visible:nth-child(1) .step-line { transition-delay: 0.15s; }
        .step-col.step-visible:nth-child(2) .step-line { transition-delay: 0.33s; }
        .step-col.step-visible:nth-child(3) .step-line { transition-delay: 0.51s; }

        /* 5. Pulse marker for anomali markers in Leaflet */
        .leaflet-anomali-pulse {
            animation: anomaliPulse 2s ease-in-out infinite;
        }
        @keyframes anomaliPulse {
            0%   { transform: scale(1);    opacity: 0.85; }
            50%  { transform: scale(1.18); opacity: 1;    }
            100% { transform: scale(1);    opacity: 0.85; }
        }

        /* ══════════════════════════════════════════════════════
           ADVANCED ANIMATIONS — BATCH 2
           ══════════════════════════════════════════════════════ */

        /* 7. SCROLL PROGRESS BAR */
        #scroll-progress {
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, var(--color-primary), #ff6b35);
            z-index: 9999;
            transition: width 0.05s linear;
            pointer-events: none;
        }

        /* 1a. HERO AURORA — animated orbs behind hero */
        .hero-aurora {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }
        .aurora-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: orbDrift 12s ease-in-out infinite alternate;
        }
        .aurora-orb-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(183,1,0,0.6), transparent 70%);
            top: -120px; left: -100px;
            animation-duration: 10s;
        }
        .aurora-orb-2 {
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(183,1,0,0.35), transparent 70%);
            bottom: -80px; right: -60px;
            animation-duration: 14s;
            animation-delay: -4s;
        }
        .aurora-orb-3 {
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(255,140,80,0.25), transparent 70%);
            top: 40%; left: 60%;
            animation-duration: 9s;
            animation-delay: -7s;
        }
        @keyframes orbDrift {
            0%   { transform: translate(0, 0) scale(1); }
            50%  { transform: translate(40px, -30px) scale(1.1); }
            100% { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* 1b. BADGE GLOW RING */
        .badge-glow {
            animation: badgeGlow 2.2s ease-in-out infinite;
        }
        @keyframes badgeGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(183,1,0,0.0), 0 0 0 4px rgba(183,1,0,0.08); }
            50%       { box-shadow: 0 0 0 6px rgba(183,1,0,0.0), 0 0 12px 6px rgba(183,1,0,0.18); }
        }

        /* 2. WORD-BY-WORD REVEAL */
        .word-reveal-wrap { display: inline-block; overflow: hidden; vertical-align: bottom; }
        .word-reveal {
            display: inline-block;
            opacity: 0;
            transform: translateY(110%);
            transition: opacity 0.45s ease-out, transform 0.45s ease-out;
        }
        .word-reveal.word-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* 3. GRADIENT TEXT ON STAT NUMBERS */
        .stat-counter {
            background: linear-gradient(135deg, var(--color-primary) 0%, #e05a2b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        /* Bounce finish */
        @keyframes counterBounce {
            0%   { transform: scale(1); }
            40%  { transform: scale(1.12); }
            70%  { transform: scale(0.97); }
            100% { transform: scale(1); }
        }
        .counter-done { animation: counterBounce 0.35s ease-out both; }

        /* 4a. FEATURE ICON FLOAT */
        .feature-icon-wrapper {
            animation: iconFloat 3s ease-in-out infinite;
        }
        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-4px); }
        }
        /* Override float on hover (tilt JS controls transform) */
        .feature-card:hover .feature-icon-wrapper {
            animation: none;
            transform: scale(1.07) rotate(-3deg);
        }

        /* 4b. 3D TILT — card base transition for smooth reset */
        .feature-card {
            transform-style: preserve-3d;
            transition: transform 0.12s ease-out, box-shadow 0.25s ease !important;
            will-change: transform;
        }

        /* 6a. MOCKUP REVEAL */
        .mockup-reveal {
            opacity: 0;
            transform: scale(0.92);
            transition: opacity 0.65s ease-out, transform 0.65s ease-out;
        }
        .mockup-reveal.mockup-visible {
            opacity: 1;
            transform: scale(1);
        }

        /* 6b. MOCKUP SPOTLIGHT SWEEP */
        .mockup-container { overflow: hidden; }
        .mockup-shine {
            position: absolute;
            top: 0; left: -100%;
            width: 60%; height: 100%;
            background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.08) 50%, transparent 60%);
            pointer-events: none;
            z-index: 10;
        }
        .mockup-shine.shine-run {
            animation: shineSwipe 0.9s ease-in-out forwards;
        }
        @keyframes shineSwipe {
            0%   { left: -80%; }
            100% { left: 130%; }
        }

        /* 6c. LEGEND SLIDE-X */
        .legend-card {
            opacity: 0;
            transform: translateX(-24px);
            transition: opacity 0.45s ease-out, transform 0.45s ease-out, border-color 0.22s ease, box-shadow 0.22s ease;
        }
        .legend-card.legend-visible {
            opacity: 1;
            transform: translateX(0);
        }
        .legend-card.legend-visible:hover {
            border-color: var(--color-primary);
            box-shadow: 0 4px 14px rgba(183,1,0,0.12);
            transform: translateX(4px);
        }

        /* 8. CURSOR GLOW */
        #hero-cursor-glow {
            position: absolute;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(183,1,0,0.10) 0%, transparent 70%);
            pointer-events: none;
            transform: translate(-50%, -50%);
            transition: opacity 0.4s ease;
            z-index: 0;
            opacity: 0;
        }

        /* 9a. RIPPLE */
        .ripple-wave {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.35);
            transform: scale(0);
            animation: rippleAnim 0.6s linear forwards;
            pointer-events: none;
        }
        @keyframes rippleAnim {
            to { transform: scale(4); opacity: 0; }
        }
        .btn-cta-hero { position: relative; overflow: hidden; }

        /* ── REDUCED MOTION FALLBACK (extended) ── */
        @media (prefers-reduced-motion: reduce) {
            .reveal-up, .hero-load, .word-reveal,
            .mockup-reveal, .legend-card {
                opacity: 1 !important;
                transform: none !important;
                animation: none !important;
                transition: none !important;
            }
            .step-line        { transform: scaleX(1) !important; transition: none !important; }
            .step-number      { animation: none !important; }
            .leaflet-anomali-pulse { animation: none !important; }
            .badge-glow       { animation: none !important; }
            .aurora-orb       { animation: none !important; }
            .feature-icon-wrapper { animation: none !important; }
            .stat-counter     { -webkit-text-fill-color: var(--color-primary); }
            #hero-cursor-glow { display: none; }
        }
    </style>
</head>
<body>
    <!-- Scroll Progress Indicator -->
    <div id="scroll-progress"></div>

    <!-- ══ NAVBAR ══════════════════════════════════════════════════════ -->
    <nav class="navbar navbar-expand-lg sticky-top navbar-landing" id="landing-navbar">
        <div class="container py-2">
            <a class="navbar-brand fw-bold" href="#beranda" style="color: var(--color-primary); font-size: 18px;">
                TIF District Lampung
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <div class="mx-auto">
                    <ul class="navbar-nav gap-2">
                        <li class="nav-item">
                            <a class="nav-link active" href="#beranda">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#fitur">Fitur</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#cara-kerja">Cara Kerja</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <a href="{{ route('login') }}" class="btn btn-primary px-4" style="border-radius: var(--radius-full) !important;">
                        Masuk ke Sistem
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ══ SECTION 1: HERO ══════════════════════════════════════════════ -->
    <section id="beranda" class="hero-section py-5 d-flex align-items-center" style="min-height: calc(100vh - 72px); padding-top: 96px !important; padding-bottom: 96px !important;">
        <!-- Aurora glow orbs -->
        <div class="hero-aurora" aria-hidden="true">
            <div class="aurora-orb aurora-orb-1"></div>
            <div class="aurora-orb aurora-orb-2"></div>
            <div class="aurora-orb aurora-orb-3"></div>
        </div>
        <!-- Cursor glow -->
        <div id="hero-cursor-glow" aria-hidden="true"></div>
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-8 mx-auto text-center">
                    <span class="badge bg-danger mb-3 hero-load hero-load-1 badge-glow" style="background-color: var(--color-primary-container) !important; color: var(--color-on-primary-container) !important; font-size: 13px !important; padding: 6px 14px !important;">
                        Real-time GIS
                    </span>
                    <h1 id="hero-h1" class="fw-bold mb-3 hero-load hero-load-2" style="font-size: 48px; font-weight: 700; line-height: 1.15; letter-spacing: -0.02em;">
                        Monitoring &amp; Validasi Tiang Infrastruktur
                    </h1>
                    <p class="mb-4 hero-load hero-load-3" style="font-size: 18px; color: var(--color-on-surface-variant); line-height: 1.6; margin-top: 20px;">
                        Meningkatkan efisiensi operasional dan transparansi data infrastruktur telekomunikasi di seluruh wilayah Lampung dengan teknologi geospasial terkini.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3 hero-load hero-load-4" style="margin-top: 32px;">
                        <a href="{{ route('login') }}" id="cta-main-btn" class="btn btn-primary px-4 py-3 d-flex align-items-center gap-2 btn-cta-hero" style="border-radius: var(--radius-default) !important;">
                            <i class="bi bi-box-arrow-in-right fs-5"></i> Masuk ke Sistem
                        </a>
                        <a href="#fitur" class="btn btn-outline-primary px-4 py-3" style="border-radius: var(--radius-default) !important;">
                            Pelajari Fitur
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ SECTION 2: STATS BAR ══════════════════════════════════════════ -->
    <section id="statistik" class="py-5" style="background-color: var(--color-surface-container-lowest); border-top: 1px solid var(--color-outline-variant); border-bottom: 1px solid var(--color-outline-variant);">
        <div class="container py-2">
            <div class="row text-center">
                <div class="col-md-3 stats-divider reveal-up reveal-delay-1">
                    <div class="fw-bold stat-counter" data-target="{{ $totalTiang }}" data-type="integer" style="font-size: 36px; color: var(--color-primary);">{{ number_format($totalTiang, 0, ',', '.') }}</div>
                    <div style="font-size: 14px; color: var(--color-on-surface-variant); margin-top: 4px;">Total Tiang Terdaftar</div>
                </div>
                <div class="col-md-3 stats-divider reveal-up reveal-delay-2">
                    <div class="fw-bold stat-counter" data-target="{{ $totalSto }}" data-type="integer" style="font-size: 36px; color: var(--color-primary);">{{ number_format($totalSto, 0, ',', '.') }}</div>
                    <div style="font-size: 14px; color: var(--color-on-surface-variant); margin-top: 4px;">Wilayah / STO</div>
                </div>
                <div class="col-md-3 stats-divider reveal-up reveal-delay-3">
                    <div class="fw-bold stat-counter" data-target="{{ $verifikasiRate }}" data-type="percent" style="font-size: 36px; color: var(--color-primary);">{{ str_replace('.', ',', $verifikasiRate) }}%</div>
                    <div style="font-size: 14px; color: var(--color-on-surface-variant); margin-top: 4px;">Tingkat Verifikasi (%)</div>
                </div>
                <div class="col-md-3 reveal-up reveal-delay-4">
                    <div class="fw-bold stat-counter" data-target="{{ $anomaliSelesai }}" data-type="integer" style="font-size: 36px; color: var(--color-primary);">{{ number_format($anomaliSelesai, 0, ',', '.') }}</div>
                    <div style="font-size: 14px; color: var(--color-on-surface-variant); margin-top: 4px;">Anomali Terselesaikan</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ SECTION 3: FITUR UTAMA ════════════════════════════════════════ -->
    <section id="fitur" class="py-5" style="background-color: var(--color-background); padding-top: 80px !important; padding-bottom: 80px !important;">
        <div class="container">
            <div class="text-center mb-5 reveal-up">
                <span class="fw-semibold" style="font-size: 12px; letter-spacing: 0.1em; color: var(--color-primary);">
                    KAPABILITAS KAMI
                </span>
                <h2 class="fw-bold mt-2" style="font-size: 32px; font-weight: 600;">
                    Fitur Utama Platform
                </h2>
            </div>
            
            <div class="row g-4" style="margin-top: 48px;">
                <!-- Fitur 1 -->
                <div class="col-md-6 col-lg-4 reveal-up reveal-delay-1">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-map"></i>
                        </div>
                        <h4 class="fw-semibold mb-2" style="font-size: 16px; color: var(--color-on-surface);">Peta GIS Interaktif</h4>
                        <p class="mb-0" style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">
                            Visualisasi aset infrastruktur secara real-time di atas peta digital dengan sistem clustering cerdas.
                        </p>
                    </div>
                </div>
                <!-- Fitur 2 -->
                <div class="col-md-6 col-lg-4 reveal-up reveal-delay-2">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="fw-semibold mb-2" style="font-size: 16px; color: var(--color-on-surface);">Validasi Aturan Bisnis</h4>
                        <p class="mb-0" style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">
                            Sistem validasi otomatis berdasarkan kebijakan internal Telkom untuk memastikan kepatuhan data.
                        </p>
                    </div>
                </div>
                <!-- Fitur 3 -->
                <div class="col-md-6 col-lg-4 reveal-up reveal-delay-3">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                        </div>
                        <h4 class="fw-semibold mb-2" style="font-size: 16px; color: var(--color-on-surface);">Import/Export Excel</h4>
                        <p class="mb-0" style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">
                            Kemudahan pengelolaan data massal melalui integrasi file spreadsheet untuk efisiensi input data.
                        </p>
                    </div>
                </div>
                <!-- Fitur 4 -->
                <div class="col-md-6 col-lg-4 reveal-up reveal-delay-1">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-camera"></i>
                        </div>
                        <h4 class="fw-semibold mb-2" style="font-size: 16px; color: var(--color-on-surface);">Dokumentasi Foto</h4>
                        <p class="mb-0" style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">
                            Penyimpanan bukti visual kondisi fisik tiang di lapangan lengkap dengan metadata GPS dan timestamp.
                        </p>
                    </div>
                </div>
                <!-- Fitur 5 -->
                <div class="col-md-6 col-lg-4 reveal-up reveal-delay-2">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                        <h4 class="fw-semibold mb-2" style="font-size: 16px; color: var(--color-on-surface);">Manajemen Legalitas</h4>
                        <p class="mb-0" style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">
                            Monitoring kontrak dan izin ISP pihak ketiga yang menggunakan infrastruktur Telkom secara terpusat.
                        </p>
                    </div>
                </div>
                <!-- Fitur 6 -->
                <div class="col-md-6 col-lg-4 reveal-up reveal-delay-3">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <h4 class="fw-semibold mb-2" style="font-size: 16px; color: var(--color-on-surface);">Dashboard Statistik</h4>
                        <p class="mb-0" style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">
                            Analitik mendalam mengenai performa aset dan progres validasi data untuk pengambilan keputusan strategis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ SECTION 4: ALUR KERJA ════════════════════════════════════════ -->
    <section id="cara-kerja" class="py-5" style="background-color: var(--color-surface-container-low); padding-top: 80px !important; padding-bottom: 80px !important;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="font-size: 32px; font-weight: 600;">
                    Alur Kerja Sistem
                </h2>
                <p class="mt-2" style="font-size: 16px; color: var(--color-on-surface-variant);">
                    Proses validasi data yang terstruktur dan transparan untuk akurasi infrastruktur.
                </p>
            </div>
            
            <div class="row g-0" style="margin-top: 56px;">
                <!-- Langkah 1 -->
                <div class="col-md-3 step-col text-center">
                    <div class="step-line"></div>
                    <div class="step-number">1</div>
                    <h5 class="fw-semibold mb-2" style="font-size: 15px; color: var(--color-on-surface);">Pengumpulan Data</h5>
                    <p class="px-3" style="font-size: 13px; color: var(--color-on-surface-variant); line-height: 1.5;">
                        Input data tiang baru atau sinkronisasi data lama.
                    </p>
                </div>
                <!-- Langkah 2 -->
                <div class="col-md-3 step-col text-center">
                    <div class="step-line"></div>
                    <div class="step-number">2</div>
                    <h5 class="fw-semibold mb-2" style="font-size: 15px; color: var(--color-on-surface);">Validasi Lapangan</h5>
                    <p class="px-3" style="font-size: 13px; color: var(--color-on-surface-variant); line-height: 1.5;">
                        Tim lapangan melakukan pengecekan fisik dan dokumentasi.
                    </p>
                </div>
                <!-- Langkah 3 -->
                <div class="col-md-3 step-col text-center">
                    <div class="step-line"></div>
                    <div class="step-number">3</div>
                    <h5 class="fw-semibold mb-2" style="font-size: 15px; color: var(--color-on-surface);">Analisis Anomali</h5>
                    <p class="px-3" style="font-size: 13px; color: var(--color-on-surface-variant); line-height: 1.5;">
                        Sistem mendeteksi ketidaksesuaian data secara otomatis.
                    </p>
                </div>
                <!-- Langkah 4 -->
                <div class="col-md-3 step-col text-center">
                    <div class="step-number">4</div>
                    <h5 class="fw-semibold mb-2" style="font-size: 15px; color: var(--color-on-surface);">Sinkronisasi GIS</h5>
                    <p class="px-3" style="font-size: 13px; color: var(--color-on-surface-variant); line-height: 1.5;">
                        Data valid dipublikasikan ke peta utama untuk monitoring.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ SECTION 5: PRATINJAU PETA ════════════════════════════════════ -->
    <section id="kontak" class="py-5" style="background-color: var(--color-background); padding-top: 80px !important; padding-bottom: 80px !important;">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <h2 class="fw-bold mb-3 reveal-up" style="font-size: 28px; font-weight: 600;">
                        Pratinjau Peta Operasional
                    </h2>
                    <p class="mb-4 reveal-up reveal-delay-1" style="font-size: 16px; color: var(--color-on-surface-variant); line-height: 1.6;">
                        Monitor status kesehatan infrastruktur di seluruh Lampung secara visual. Sistem klasifikasi warna memudahkan identifikasi area yang membutuhkan atensi segera.
                    </p>
                    
                    <div class="d-flex flex-column gap-3" style="margin-top: 28px;">
                        <div class="legend-card" style="transition-delay:0.10s">
                            <div class="legend-dot-ok"></div>
                            <span style="font-size: 14px; font-weight: 500; color: var(--color-on-surface);">Status OK – Terverifikasi</span>
                        </div>
                        <div class="legend-card" style="transition-delay:0.23s">
                            <div class="legend-dot-warning"></div>
                            <span style="font-size: 14px; font-weight: 500; color: var(--color-on-surface);">Pending Verifikasi – Butuh Review</span>
                        </div>
                        <div class="legend-card" style="transition-delay:0.36s">
                            <div class="legend-dot-broken"></div>
                            <span style="font-size: 14px; font-weight: 500; color: var(--color-on-surface);">Anomali Terdeteksi</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-7">
                    <div id="mockup-box" class="mockup-container mockup-reveal" style="background-color: #1a1a2e; min-height: 320px; box-shadow: var(--shadow-modal);">
                        <!-- Spotlight sweep element -->
                        <div class="mockup-shine" id="mockup-shine"></div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="mockup-header-bar">
                                <div class="mockup-bar-red" style="background-color: #22c55e;"></div>
                                <div class="mockup-bar-blue" style="background-color: #f59e0b;"></div>
                            </div>
                            <div class="small fw-semibold text-white-50" style="font-size: 12px;">
                                Map Overview
                            </div>
                        </div>
                        
                        <div class="mockup-map" style="background-color: #0f172a; min-height: 280px;">
                            <div id="landing-map" style="width: 100%; height: 280px; z-index: 1;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ SECTION 6: FOOTER ════════════════════════════════════════════ -->
    <footer style="background-color: var(--color-inverse-surface); color: var(--color-inverse-on-surface); padding-top: 56px; padding-bottom: 24px;">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <!-- Kolom 1 (40%) -->
                <div class="col-lg-5">
                    <h5 class="fw-bold mb-3" style="color: #ffffff !important; font-size: 16px !important;">
                        TIF District Lampung
                    </h5>
                    <p class="mb-4" style="font-size: 13px; color: rgba(243,240,239,0.7); line-height: 1.6; margin-top: 12px;">
                        PT Telkom Infrastruktur Indonesia adalah penyedia utama infrastruktur telekomunikasi digital di Indonesia. District Lampung berdedikasi untuk memberikan layanan monitoring yang unggul.
                    </p>
                    <div class="d-flex gap-2" style="margin-top: 16px;">
                        <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-globe"></i></a>
                    </div>
                </div>
                
                <!-- Kolom 2 (25%) -->
            
                
                <!-- Kolom 3 (30%) -->
                <div class="col-lg-4">
                    <h6 class="fw-semibold mb-3" style="font-size: 11px !important; letter-spacing: 0.1em; color: #ffffff !important;">
                        ALAMAT
                    </h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-2" style="font-size: 13px; color: rgba(243,240,239,0.7);">
                            <i class="bi bi-geo-alt mt-1" style="font-size: 16px; flex-shrink:0;"></i>
                            <span>Jl. Sultan Haji No. 1, Bandar Lampung, Lampung 35111, Indonesia</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr style="border-top: 1px solid rgba(243,240,239,0.15); margin: 32px 0 20px;">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div style="font-size: 12px; color: rgba(243,240,239,0.5);">
                    © 2026 Ilmu Komputer - Universitas Lampung. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        // ── STICKY NAVBAR SHADOW ON SCROLL ──────────────────────────
        const navbar = document.getElementById('landing-navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.style.boxShadow = 'var(--shadow-card)';
            } else {
                navbar.style.boxShadow = 'none';
            }
        });

        // ── ACTIVE NAVIGATION HIGHLIGHT (INTERSECTION OBSERVER) ─────
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('.navbar-landing .nav-link');

        const observerOptions = {
            root: null,
            rootMargin: '-20% 0px -60% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const activeId = entry.target.getAttribute('id');
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${activeId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }, observerOptions);

        sections.forEach(section => observer.observe(section));

        // ── INITIALIZE REAL GEOSPATIAL MAP (SAME AS DASHBOARD) ──────
        document.addEventListener("DOMContentLoaded", () => {
            // Set up landing Leaflet map
            const map = L.map('landing-map', {
                zoomControl: true,
                scrollWheelZoom: false // disable scrolling zoom for landing page usability
            }).setView([-5.35, 105.25], 10);

            // Using the same OSM standard tiles as in the dashboard
            const osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            const osmAttrib = '© OpenStreetMap contributors';

            L.tileLayer(osmUrl, { attribution: osmAttrib, maxZoom: 19 }).addTo(map);

            // Initialize MarkerCluster Group
            const markerCluster = L.markerClusterGroup({ chunkedLoading: true });
            map.addLayer(markerCluster);

            // Same markerColor logic as dashboard
            function markerColor(d) {
                if (d.has_anomali) return '#dc3545';
                if (d.status_verifikasi === 'pending') return '#ffc107';
                return '#198754';
            }

            // Same makeIcon logic as dashboard
            function makeIcon(color, isAnomali) {
                const pulseClass = isAnomali ? 'leaflet-anomali-pulse' : '';
                return L.divIcon({
                    className: '',
                    html: `<div class="${pulseClass}" style="width:12px;height:12px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)"></div>`,
                    iconSize: [12, 12],
                    iconAnchor: [6, 6]
                });
            }

            // Fetch live marker coordinates from system
            fetch('/public-map-markers')
                .then(res => res.json())
                .then(markers => {
                    if (!markers || markers.length === 0) return;

                    const group = [];
                    markers.forEach(d => {
                        if (d.latitude && d.longitude) {
                            const color = markerColor(d);
                            const lat = parseFloat(d.latitude);
                            const lng = parseFloat(d.longitude);

                            // Create marker for landing map (pulse on anomali)
                            const m = L.marker([lat, lng], { icon: makeIcon(color, d.has_anomali) });
                            m.bindPopup(`
                                <div style="font-family: var(--font-primary); font-size: 12px; padding: 4px;">
                                    <strong style="color: var(--color-primary);">${d.kode_tiang || 'N/A'}</strong><br>
                                    <span class="text-muted">Status: ${d.status_verifikasi}</span>
                                </div>
                            `);
                            markerCluster.addLayer(m);

                            group.push([lat, lng]);
                        }
                    });

                    // Fit map bounds to show all markers beautifully
                    if (group.length > 0) {
                        map.fitBounds(group, { padding: [20, 20] });
                    }
                })
                .catch(err => console.error("Error loading map markers:", err));
        });

        // ══════════════════════════════════════════════════════════════
        // ANIMATION ENGINE — scroll-reveal, counter, step-line
        // ══════════════════════════════════════════════════════════════

        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // ── 1 & 4. IntersectionObserver untuk reveal-up & step-line ──
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            });
        }, { threshold: 0.15 });

        document.querySelectorAll('.reveal-up').forEach(el => revealObserver.observe(el));

        // ── 4. Step-line: observe parent section ──
        const stepSection = document.querySelector('#cara-kerja');
        if (stepSection) {
            const stepObserver = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    document.querySelectorAll('.step-col').forEach(col => col.classList.add('step-visible'));
                    stepObserver.disconnect();
                }
            }, { threshold: 0.2 });
            stepObserver.observe(stepSection);
        }

        // ── 2. Counter animation (requestAnimationFrame, trigger once) ──
        if (!prefersReduced) {
            const counters = document.querySelectorAll('.stat-counter');
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    const el = entry.target;
                    const target = parseFloat(el.dataset.target);
                    const type = el.dataset.type; // 'integer' | 'percent'
                    const duration = 1200;
                    const start = performance.now();

                    function tick(now) {
                        const elapsed = now - start;
                        const progress = Math.min(elapsed / duration, 1);
                        // ease-out cubic
                        const eased = 1 - Math.pow(1 - progress, 3);
                        const current = target * eased;

                        if (type === 'percent') {
                            // Format: angka koma desimal satu digit + %
                            el.textContent = current.toFixed(1).replace('.', ',') + '%';
                        } else {
                            // Format: titik ribuan locale Indonesia
                            el.textContent = Math.round(current).toLocaleString('id-ID');
                        }

                        if (progress < 1) {
                            requestAnimationFrame(tick);
                        } else {
                            // Pastikan final state akurat
                            if (type === 'percent') {
                                el.textContent = target.toFixed(1).replace('.', ',') + '%';
                            } else {
                                el.textContent = Math.round(target).toLocaleString('id-ID');
                            }
                        }
                    }

                    requestAnimationFrame(tick);
                    counterObserver.unobserve(el);
                });
            }, { threshold: 0.3 });

            counters.forEach(el => counterObserver.observe(el));
        }

        // ══════════════════════════════════════════════════════════════
        // ADVANCED ANIMATION ENGINE — BATCH 2
        // ══════════════════════════════════════════════════════════════

        const isTouch     = ('ontouchstart' in window) || !window.matchMedia('(hover: hover)').matches;
        const noMotion    = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const canAnimate  = !noMotion;

        // ── 7. SCROLL PROGRESS BAR ──────────────────────────────────
        const scrollBar = document.getElementById('scroll-progress');
        if (scrollBar) {
            let scrollTicking = false;
            window.addEventListener('scroll', () => {
                if (!scrollTicking) {
                    requestAnimationFrame(() => {
                        const scrolled = window.scrollY;
                        const total    = document.documentElement.scrollHeight - window.innerHeight;
                        scrollBar.style.width = (total > 0 ? (scrolled / total) * 100 : 0) + '%';
                        scrollTicking = false;
                    });
                    scrollTicking = true;
                }
            }, { passive: true });
        }

        // ── 2. WORD-BY-WORD REVEAL on hero H1 ───────────────────────
        if (canAnimate) {
            const h1 = document.getElementById('hero-h1');
            if (h1) {
                const text = h1.textContent.trim();
                const words = text.split(/\s+/);
                h1.textContent = '';
                h1.style.lineHeight = '1.2';
                words.forEach((word, i) => {
                    const wrap = document.createElement('span');
                    wrap.className = 'word-reveal-wrap';
                    wrap.style.marginRight = '0.28em';
                    const span = document.createElement('span');
                    span.className = 'word-reveal';
                    span.style.transitionDelay = (0.18 + i * 0.085) + 's';
                    span.textContent = word;
                    wrap.appendChild(span);
                    h1.appendChild(wrap);
                });
                // Trigger word reveal after hero-load delay
                setTimeout(() => {
                    h1.querySelectorAll('.word-reveal').forEach(s => s.classList.add('word-visible'));
                }, 280);
            }
        }

        // ── 3. COUNTER BOUNCE on finish ─────────────────────────────
        // Patch the existing counter observer to add bounce class when done
        if (canAnimate) {
            document.querySelectorAll('.stat-counter').forEach(el => {
                const orig = el._tickDone; // will be overridden below via MutationObserver trick
                // Use a one-shot MutationObserver watching for textContent stabilization
                let bounceTimer;
                const mo = new MutationObserver(() => {
                    clearTimeout(bounceTimer);
                    bounceTimer = setTimeout(() => {
                        el.classList.remove('counter-done');
                        void el.offsetWidth; // reflow
                        el.classList.add('counter-done');
                        mo.disconnect();
                    }, 1250); // trigger ~after counter duration
                });
                mo.observe(el, { childList: true, subtree: true, characterData: true });
            });
        }

        // ── 4. 3D TILT on feature cards ─────────────────────────────
        if (canAnimate && !isTouch) {
            const TILT_MAX = 10; // degrees
            document.querySelectorAll('.feature-card').forEach(card => {
                let rafId;
                card.addEventListener('mousemove', e => {
                    cancelAnimationFrame(rafId);
                    rafId = requestAnimationFrame(() => {
                        const rect = card.getBoundingClientRect();
                        const cx = rect.left + rect.width  / 2;
                        const cy = rect.top  + rect.height / 2;
                        const dx = (e.clientX - cx) / (rect.width  / 2);
                        const dy = (e.clientY - cy) / (rect.height / 2);
                        const rotX = -dy * TILT_MAX;
                        const rotY =  dx * TILT_MAX;
                        const shadow = `${dx * 8}px ${dy * 8}px 24px rgba(0,0,0,0.18)`;
                        card.style.transform = `perspective(600px) rotateX(${rotX}deg) rotateY(${rotY}deg) translateZ(4px)`;
                        card.style.boxShadow = shadow;
                    });
                }, { passive: true });

                card.addEventListener('mouseleave', () => {
                    cancelAnimationFrame(rafId);
                    card.style.transform = '';
                    card.style.boxShadow = '';
                });
            });
        }

        // ── 6. MOCKUP REVEAL + SPOTLIGHT SWEEP ─────────────────────
        const mockupBox = document.getElementById('mockup-box');
        if (mockupBox && canAnimate) {
            const mockupObs = new IntersectionObserver((entries) => {
                if (!entries[0].isIntersecting) return;
                mockupBox.classList.add('mockup-visible');
                // Spotlight sweep after a slight delay
                setTimeout(() => {
                    const shine = document.getElementById('mockup-shine');
                    if (shine) shine.classList.add('shine-run');
                }, 400);
                mockupObs.disconnect();
            }, { threshold: 0.25 });
            mockupObs.observe(mockupBox);
        }

        // ── 6c. LEGEND SLIDE-X ──────────────────────────────────────
        const legendCards = document.querySelectorAll('.legend-card');
        if (legendCards.length && canAnimate) {
            const legendSection = document.querySelector('#kontak');
            const legendObs = new IntersectionObserver((entries) => {
                if (!entries[0].isIntersecting) return;
                legendCards.forEach(card => card.classList.add('legend-visible'));
                legendObs.disconnect();
            }, { threshold: 0.2 });
            if (legendSection) legendObs.observe(legendSection);
        } else {
            // No animation: show immediately
            legendCards.forEach(card => card.classList.add('legend-visible'));
        }

        // ── 8. CURSOR GLOW in Hero (lerp) ───────────────────────────
        if (canAnimate && !isTouch) {
            const glowEl   = document.getElementById('hero-cursor-glow');
            const heroSec  = document.getElementById('beranda');
            if (glowEl && heroSec) {
                let mouseX = 0, mouseY = 0;
                let glowX = 0, glowY = 0;
                let glowActive = false, glowRaf;

                heroSec.addEventListener('mouseenter', () => {
                    glowEl.style.opacity = '1';
                    glowActive = true;
                    function lerp() {
                        if (!glowActive) return;
                        glowX += (mouseX - glowX) * 0.08;
                        glowY += (mouseY - glowY) * 0.08;
                        glowEl.style.left = glowX + 'px';
                        glowEl.style.top  = glowY + 'px';
                        glowRaf = requestAnimationFrame(lerp);
                    }
                    lerp();
                });
                heroSec.addEventListener('mouseleave', () => {
                    glowEl.style.opacity = '0';
                    glowActive = false;
                    cancelAnimationFrame(glowRaf);
                });
                heroSec.addEventListener('mousemove', e => {
                    const rect = heroSec.getBoundingClientRect();
                    mouseX = e.clientX - rect.left;
                    mouseY = e.clientY - rect.top;
                }, { passive: true });
            }
        }

        // ── 9. MAGNETIC BUTTON + RIPPLE ─────────────────────────────
        const ctaBtn = document.getElementById('cta-main-btn');
        if (ctaBtn && canAnimate && !isTouch) {
            const MAG_RANGE = 80;
            const MAG_FORCE = 0.35;

            document.addEventListener('mousemove', e => {
                const rect = ctaBtn.getBoundingClientRect();
                const cx = rect.left + rect.width  / 2;
                const cy = rect.top  + rect.height / 2;
                const dx = e.clientX - cx;
                const dy = e.clientY - cy;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < MAG_RANGE) {
                    ctaBtn.style.transform = `translate(${dx * MAG_FORCE}px, ${dy * MAG_FORCE}px)`;
                } else {
                    ctaBtn.style.transform = '';
                }
            }, { passive: true });

            // Ripple on click
            ctaBtn.addEventListener('click', e => {
                const rect = ctaBtn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height) * 0.6;
                const x    = e.clientX - rect.left - size / 2;
                const y    = e.clientY - rect.top  - size / 2;
                const ripple = document.createElement('span');
                ripple.className = 'ripple-wave';
                ripple.style.cssText = `width:${size}px;height:${size}px;left:${x}px;top:${y}px`;
                ctaBtn.appendChild(ripple);
                ripple.addEventListener('animationend', () => ripple.remove());
            });
        }
    </script>
</body>
</html>
