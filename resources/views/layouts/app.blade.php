<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PayMe - Split Bill & Dynamic QRIS')</title>

    <!-- Standard SEO & OpenGraph Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Bagi tagihan patungan dan buat invoice instan dengan konversi QRIS statis ke dinamis ber-nominal presisi.')">
    <meta property="og:title" content="@yield('title', 'PayMe - Split Bill & Dynamic QRIS')">
    <meta property="og:description" content="@yield('meta_description', 'Bagi tagihan patungan dan buat invoice instan dengan konversi QRIS statis ke dinamis ber-nominal presisi.')">
    <meta property="og:image" content="{{ asset('images/scan-qr-code.svg') }}">
    <meta property="og:type" content="website">

    <!-- PWA Primary & Mobile Capabilities -->
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#064E3B">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PayMe">

    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Pro 7.1.0 (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.css') }}">

    <!-- Tailwind CSS v4 & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')
    @stack('styles')
</head>
<body class="bg-canvas text-zinc-900 font-sans min-h-screen flex flex-col antialiased selection:bg-emerald-700 selection:text-white">

    <!-- Selective Glass Header (Modern iOS/macOS feel with clean border) -->
    <header class="glass-header sticky top-0 z-40 transition-colors">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo & Name -->
                <div class="flex items-center gap-3">
                    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex items-center gap-2.5 group">
                        <div class="w-8 h-8 rounded-lg bg-emerald-800 text-white flex items-center justify-center shadow-2xs transition-transform group-hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 12v4a1 1 0 0 1-1 1h-4"/>
                                <path d="M17 3h2a2 2 0 0 1 2 2v2"/>
                                <path d="M17 8V7"/>
                                <path d="M21 17v2a2 2 0 0 1-2 2h-2"/>
                                <path d="M3 7V5a2 2 0 0 1 2-2h2"/>
                                <path d="M7 17h.01"/>
                                <path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                                <rect x="7" y="7" width="5" height="5" rx="1"/>
                            </svg>
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="font-bold text-zinc-900 text-base tracking-tight">PayMe</span>
                            <span class="text-[10px] text-zinc-400 font-medium mt-0.5">by AkuOnline</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Actions (Dynamic Auth / Guest) -->
                <nav class="flex items-center gap-1 sm:gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="touch-target inline-flex items-center gap-1.5 px-3 py-2 text-xs sm:text-sm font-semibold {{ request()->routeIs('dashboard') ? 'text-emerald-800 bg-emerald-50' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100/70' }} rounded-lg transition-colors" title="Dashboard">
                            <i class="fa-light fa-grid-2 text-xs"></i>
                            <span>Dashboard</span>
                        </a>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="touch-target inline-flex items-center gap-1.5 px-3 py-2 text-xs sm:text-sm font-semibold text-zinc-500 hover:text-rose-700 hover:bg-rose-50/70 rounded-lg transition-colors cursor-pointer" title="Keluar dari akun">
                                <i class="fa-light fa-arrow-right-from-bracket text-xs"></i>
                                <span class="hidden sm:inline">Keluar</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="touch-target inline-flex items-center px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs sm:text-sm font-semibold {{ request()->routeIs('login') ? 'text-emerald-800 bg-emerald-50' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100/70' }} rounded-lg transition-colors">
                            Masuk
                        </a>

                        <a href="{{ route('register') }}" class="touch-target inline-flex items-center gap-1 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-semibold rounded-lg btn-primary transition-all">
                            <span>Daftar</span>
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-2xs">
                <i class="fa-light fa-circle-check text-emerald-600 text-base mt-0.5 flex-shrink-0"></i>
                <div class="text-sm font-medium">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-2xs">
                <i class="fa-light fa-circle-exclamation text-rose-600 text-base mt-0.5 flex-shrink-0"></i>
                <div class="text-sm font-medium">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Minimalist, Grounded Footer -->
    <footer class="mt-auto border-t border-zinc-200/90 bg-white py-8 text-xs text-zinc-500">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-zinc-800">PayMe</span>
                <span>&bull;</span>
                <span>Split Bill Lebih Adil, Bayar Pakai QRIS Lebih Praktis</span>
            </div>
            <div class="flex items-center gap-3 text-zinc-400">
                <a href="#traktir-kopi" data-open-buy-coffee class="inline-flex items-center gap-1.5 text-zinc-600 hover:text-amber-700 transition-colors font-medium cursor-pointer">
                    <i class="fa-light fa-mug-hot text-amber-600 text-xs"></i>
                    <span>Traktir Kopi Mas Dev</span>
                </a>
                <span>&bull;</span>
                <span>&copy; {{ date('Y') }} AkuOnline. All rights reserved.</span>
            </div>
        </div>
    </footer>

    <!-- Notiflix Notification & Modal Suite -->
    <script src="{{ asset('vendor/notiflix/notiflix.min.js') }}"></script>
    <script>
        if (window.Notiflix) {
            Notiflix.Notify.init({
                position: 'right-top',
                cssAnimationStyle: 'from-top',
                useIcon: true,
                fontFamily: 'inherit',
                borderRadius: '12px',
                success: {
                    background: '#064E3B',
                    textColor: '#FFFFFF',
                    childClassName: 'notiflix-notify-success',
                    notiflixIconColor: '#FFFFFF',
                },
                failure: {
                    background: '#E11D48',
                    textColor: '#FFFFFF',
                },
                warning: {
                    background: '#D97706',
                    textColor: '#FFFFFF',
                }
            });

            Notiflix.Loading.init({
                svgColor: '#064E3B',
                backgroundColor: 'rgba(255,255,255,0.75)',
                messageColor: '#064E3B',
                fontFamily: 'inherit',
            });

            Notiflix.Confirm.init({
                borderRadius: '16px',
                titleColor: '#064E3B',
                okButtonBackground: '#064E3B',
                cancelButtonBackground: '#F4F4F5',
                cancelButtonColor: '#3F3F46',
                fontFamily: 'inherit',
            });
        }

        window.showPageLoading = function(message = 'Memuat ulang data terbaru...') {
            if (window.Notiflix) {
                Notiflix.Loading.pulse(message);
            }
        };
        window.hidePageLoading = function() {
            if (window.Notiflix) {
                Notiflix.Loading.remove();
            }
        };

        // Automatic smooth loading overlay for standard POST transitions
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form && form.method && form.method.toUpperCase() === 'POST' && !form.dataset.ajax) {
                window.showPageLoading('Sedang memproses...');
            }
        });
    </script>

    @include('partials.buy-coffee-modal')

    @yield('scripts')
    @stack('scripts')
</body>
</html>
