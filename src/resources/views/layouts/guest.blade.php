<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FinLedger') }} — {{ config('app.tagline') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #0f172a;
            min-height: 100vh;
            overflow: hidden;
        }

        .auth-bg {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            z-index: 0;
        }

        .auth-bg::before {
            content: '';
            position: absolute;
            top: -40%;
            left: -20%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .auth-bg::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: -10%;
            width: 60%;
            height: 60%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.12) 0%, transparent 70%);
            border-radius: 50%;
        }

        .auth-card {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
        }

        .auth-logo {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        }

        .auth-title {
            color: #f8fafc;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .auth-subtitle {
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .form-label {
            color: #cbd5e1;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
        }

        .auth-input {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 10px !important;
            color: #f1f5f9 !important;
            padding: 0.7rem 1rem !important;
            font-size: 0.9rem !important;
            transition: all 0.2s !important;
        }

        .auth-input:focus {
            background: rgba(255, 255, 255, 0.09) !important;
            border-color: rgba(99, 102, 241, 0.6) !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
            outline: none !important;
            color: #f1f5f9 !important;
        }

        .auth-input::placeholder {
            color: #475569 !important;
        }

        .auth-input.is-invalid {
            border-color: rgba(239, 68, 68, 0.6) !important;
        }

        .btn-auth {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.75rem 1.5rem;
            width: 100%;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
        }

        .btn-auth:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
            color: white;
        }

        .btn-auth:active {
            transform: translateY(0);
        }

        .auth-divider {
            border-color: rgba(255, 255, 255, 0.08);
        }

        .auth-link {
            color: #818cf8;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .auth-link:hover {
            color: #a5b4fc;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.06) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }

        .form-check-input:checked {
            background-color: #6366f1 !important;
            border-color: #6366f1 !important;
        }

        .form-check-label {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .invalid-feedback {
            color: #f87171 !important;
            font-size: 0.8rem;
        }

        .alert-success-auth {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 10px;
            color: #6ee7b7;
            font-size: 0.85rem;
            padding: 0.75rem 1rem;
        }

        .floating-dots {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            overflow: hidden;
        }

        .dot {
            position: absolute;
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }

            50% {
                transform: translateY(-30px) rotate(180deg);
                opacity: 0.6;
            }
        }
    </style>
</head>

<body>
    <div class="auth-bg"></div>

    <!-- Floating decorative dots -->
    <div class="floating-dots">
        <div class="dot" style="width:6px;height:6px;background:#6366f1;top:15%;left:10%;animation-delay:0s;"></div>
        <div class="dot" style="width:4px;height:4px;background:#8b5cf6;top:70%;left:5%;animation-delay:2s;"></div>
        <div class="dot" style="width:8px;height:8px;background:#6366f1;top:30%;right:8%;animation-delay:1s;"></div>
        <div class="dot" style="width:5px;height:5px;background:#a78bfa;top:80%;right:15%;animation-delay:3s;"></div>
        <div class="dot" style="width:3px;height:3px;background:#818cf8;top:50%;left:50%;animation-delay:4s;"></div>
    </div>

    <div class="d-flex align-items-center justify-content-center min-vh-100 p-3" style="position:relative;z-index:10;">
        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
