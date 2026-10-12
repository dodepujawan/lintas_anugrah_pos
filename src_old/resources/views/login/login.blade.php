<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Lintas Anugrah Ekspedisi</title>

    <!-- Tailwind CSS 3 (Play CDN — ganti dengan build process Tailwind kamu di production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['"Space Grotesk"', 'sans-serif'],
                        body: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            950: '#0B1223',
                            900: '#0F1830',
                            800: '#14213D',
                            700: '#1C2D50',
                        },
                        amber: {
                            300: '#FFC971',
                            400: '#FFB449',
                            500: '#FF9F1C',
                        },
                        paper: '#F7F4EE',
                        ink: '#1C2333',
                    },
                }
            }
        }
    </script>

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
        }

        .font-display {
            font-family: 'Space Grotesk', sans-serif;
        }

        /* --- route line draw-in --- */
        .route-path {
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: draw-route 2.4s cubic-bezier(0.65, 0, 0.35, 1) 0.3s forwards;
        }
        @keyframes draw-route {
            to { stroke-dashoffset: 0; }
        }

        /* --- truck travelling along the route, looping --- */
        .route-mover {
            offset-path: path('M 40 260 C 120 260, 140 120, 240 120 C 340 120, 340 300, 460 300 C 540 300, 560 180, 640 180');
            offset-rotate: auto;
            animation: travel 7s linear 2.6s infinite;
            opacity: 0;
        }
        @keyframes travel {
            0%   { offset-distance: 0%;   opacity: 0; }
            4%   { opacity: 1; }
            96%  { opacity: 1; }
            100% { offset-distance: 100%; opacity: 0; }
        }

        /* --- pulsing origin / destination pins --- */
        .pin-pulse {
            animation: pulse-ring 2.2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-ring {
            0%   { transform: scale(0.9); opacity: 0.7; }
            70%  { transform: scale(1.8); opacity: 0; }
            100% { transform: scale(1.8); opacity: 0; }
        }

        /* --- staggered entrance for the form panel --- */
        .rise-in {
            opacity: 0;
            transform: translateY(14px);
            animation: rise 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .rise-in:nth-child(1) { animation-delay: 0.05s; }
        .rise-in:nth-child(2) { animation-delay: 0.15s; }
        .rise-in:nth-child(3) { animation-delay: 0.25s; }
        .rise-in:nth-child(4) { animation-delay: 0.35s; }
        .rise-in:nth-child(5) { animation-delay: 0.45s; }
        @keyframes rise {
            to { opacity: 1; transform: translateY(0); }
        }

        .dot-grid {
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset;
        }

        html, body {
            overflow-x: hidden;
        }

        /* iOS Safari auto-zoom saat fokus jika font-size < 16px */
        input[type="email"],
        input[type="password"] {
            font-size: 16px;
        }
        @media (min-width: 640px) {
            input[type="email"],
            input[type="password"] {
                font-size: 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .route-path, .route-mover, .pin-pulse, .rise-in {
                animation: none !important;
                opacity: 1 !important;
                stroke-dashoffset: 0 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body class="font-body bg-paper text-ink overflow-x-hidden">

<div class="min-h-screen flex flex-col lg:flex-row lg:h-screen">

    <!-- ================= LEFT: ROUTE / BRAND PANEL ================= -->
    <div class="relative w-full lg:w-1/2 shrink-0 min-h-[220px] sm:min-h-[300px] lg:min-h-0 lg:h-full bg-navy-950 overflow-hidden dot-grid">

        <!-- subtle top-to-bottom glow -->
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-navy-800/60 via-navy-950 to-navy-950"></div>

        <!-- brand -->
        <div class="relative z-10 px-5 pt-6 sm:px-8 sm:pt-10 lg:px-14 lg:pt-14">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-md bg-amber-500 flex items-center justify-center rotate-3 shrink-0">
                    <svg viewBox="0 0 24 24" class="w-4 h-4 sm:w-5 sm:h-5 text-navy-950" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 16V6a1 1 0 0 1 1-1h9v11" />
                        <path d="M13 9h4l4 4v3a1 1 0 0 1-1 1h-1" />
                        <circle cx="7.5" cy="17.5" r="1.8" />
                        <circle cx="17.5" cy="17.5" r="1.8" />
                    </svg>
                </div>
                <span class="font-display text-base sm:text-lg font-semibold tracking-tight text-white">Lintas Anugrah</span>
            </div>
            {{-- <p class="mt-3 sm:mt-4 font-display text-xl sm:text-2xl lg:text-3xl font-semibold text-white leading-snug max-w-sm">
                ,<br class="hidden sm:block"> terlacak sampai tujuan.
            </p> --}}
            <p class="mt-2 sm:mt-3 text-sm text-slate-400 max-w-xs hidden sm:block">
                Masuk ke dashboard operasional untuk kelola rute, armada, dan status pengiriman hari ini.
            </p>
        </div>

        <!-- animated route map -->
        <div class="relative z-10 mt-2 sm:mt-4 lg:mt-8 px-3 sm:px-6 lg:px-10">
            <svg viewBox="0 0 680 360" class="w-full h-auto max-h-[150px] sm:max-h-[220px] lg:max-h-[320px]" fill="none">
                <!-- faded base road -->
                <path d="M 40 260 C 120 260, 140 120, 240 120 C 340 120, 340 300, 460 300 C 540 300, 560 180, 640 180"
                      stroke="#243358" stroke-width="3" stroke-linecap="round" />

                <!-- animated amber route -->
                <path class="route-path" d="M 40 260 C 120 260, 140 120, 240 120 C 340 120, 340 300, 460 300 C 540 300, 560 180, 640 180"
                      stroke="#FF9F1C" stroke-width="3" stroke-linecap="round" />

                <!-- origin pin -->
                <g transform="translate(40 260)">
                    <circle class="pin-pulse" r="8" fill="#FF9F1C" />
                    <circle r="5" fill="#FFC971" />
                </g>

                <!-- destination pin -->
                <g transform="translate(640 180)">
                    <circle class="pin-pulse" r="8" fill="#FF9F1C" />
                    <circle r="5" fill="#FFC971" />
                </g>

                <!-- travelling van -->
                <g class="route-mover">
                    <g transform="translate(-13 -10)">
                        <rect width="26" height="18" rx="3" fill="#F7F4EE" />
                        <rect x="16" width="10" height="18" rx="2" fill="#FFC971" />
                        <circle cx="7" cy="19" r="2.6" fill="#0B1223" />
                        <circle cx="20" cy="19" r="2.6" fill="#0B1223" />
                    </g>
                </g>
            </svg>
        </div>

        <div class="relative z-10 hidden lg:flex items-center gap-6 px-14 pb-10 text-slate-400 text-xs">
            <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Berbagai kota tujuan</span>
            <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Jasa Sewa Mobil Box</span>
        </div>
    </div>

    <!-- ================= RIGHT: LOGIN FORM ================= -->
    <div class="w-full lg:w-1/2 flex-1 lg:h-full flex items-center justify-center px-5 py-10 sm:px-10 sm:py-12 lg:px-16 overflow-y-auto">
        <div class="w-full max-w-sm">

            <!-- manifest-style eyebrow -->
            <div class="rise-in flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-[11px] sm:text-xs font-medium tracking-widest text-slate-400 uppercase mb-6 pb-3 border-b border-dashed border-slate-300">
                <span>Portal Operasional</span>
                <span class="text-slate-300"></span>
            </div>

            <div class="rise-in mb-8">
                <h1 class="font-display text-2xl font-semibold text-ink">Masuk ke akun kamu</h1>
                <p class="text-sm text-slate-500 mt-1">Gunakan email dan kata sandi terdaftar.</p>
            </div>

            <!-- Error Message Block -->
            @if(session('error'))
                <div class="rise-in mb-5 flex items-start gap-2 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('actionlogin') }}" method="post" class="space-y-5">
                @csrf

                <div class="rise-in">
                    <label for="email" class="block text-sm font-medium text-ink mb-1.5">Email</label>
                    <input type="email" name="email" id="email" required placeholder="nama@perusahaan.com"
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-ink placeholder-slate-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                </div>

                <div class="rise-in">
                    <label for="password" class="block text-sm font-medium text-ink mb-1.5">Password</label>
                    <input type="password" name="password" id="password" required placeholder="••••••••"
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-ink placeholder-slate-400 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                </div>

                <button type="submit" id="submit_form"
                        class="rise-in w-full inline-flex items-center justify-center gap-2 rounded-lg bg-navy-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-500 hover:text-navy-950 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">
                    Log In
                </button>
            </form>

            <p class="rise-in mt-8 text-center text-xs text-slate-400">
                Lintas Anugrah Ekspedisi &copy; {{ date('Y') }}
            </p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#email").on("keydown", function(event) {
        if (event.which == 13 || event.which === 9) {
            event.preventDefault();
            $('#password').focus();
        }
    });
    $('#password').on("keydown", function(event) {
        if (event.which == 13 || event.which === 9) {
            event.preventDefault();
            $('#submit_form').focus();
        }
    });
    // Mencegah submit dengan Enter kecuali pada tombol submit
    $('#submit_form').on('keydown', function (event) {
        if (event.which == 13 || event.which === 9) { // Enter atau Tab
            event.preventDefault();
            $(this).click();
        }
    });
    // Nonaktifkan tombol submit setelah klik
    $('#submit_form').click(function (e) {
        $(this).prop('disabled', true).text('Memproses...');
        $(this).closest('form').submit();
    });
});
</script>
</body>
</html>
