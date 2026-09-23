@extends('layouts.app')

@section('title', 'Masuk - PayMe')
@section('meta_description', 'Masuk ke akun penagih PayMe untuk mengelola rekening, QRIS, dan riwayat tagihan patungan Anda.')

@section('content')
<div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-6 sm:py-10">
    <div class="w-full max-w-md">

        <!-- Card Container -->
        <div class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm relative">

            <!-- Card Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200/60 mb-3 shadow-2xs">
                    <i class="fa-light fa-arrow-right-to-bracket text-xl"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-zinc-900 tracking-tight">Selamat Datang Kembali</h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Masuk ke akun PayMe untuk kelola tagihan & QRIS
                </p>
            </div>

            <!-- Email & Password Form -->
            <form action="#" method="POST" class="space-y-4">
                @csrf

                <!-- Input: Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-zinc-700">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-zinc-400 pointer-events-none text-xs">
                            <i class="fa-light fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" required autocomplete="email" placeholder="nama@email.com" class="touch-target w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm bg-white border border-zinc-300 rounded-xl focus:outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/10 text-zinc-900 placeholder:text-zinc-400 transition-colors">
                    </div>
                </div>

                <!-- Input: Password -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-bold text-zinc-700">
                            Kata Sandi <span class="text-rose-500">*</span>
                        </label>
                        <a href="#" class="text-[11px] font-semibold text-emerald-800 hover:text-emerald-950 transition-colors">
                            Lupa kata sandi?
                        </a>
                    </div>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-zinc-400 pointer-events-none text-xs">
                            <i class="fa-light fa-lock-keyhole"></i>
                        </span>
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="touch-target w-full pl-9 pr-10 py-2.5 text-xs sm:text-sm bg-white border border-zinc-300 rounded-xl focus:outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/10 text-zinc-900 placeholder:text-zinc-400 transition-colors">
                        <button type="button" id="togglePasswordBtn" class="touch-target absolute right-2.5 w-7 h-7 flex items-center justify-center text-zinc-400 hover:text-zinc-700 transition-colors" title="Lihat/Sembunyikan Kata Sandi">
                            <i class="fa-light fa-eye text-xs" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-zinc-300 text-emerald-800 focus:ring-emerald-700 cursor-pointer accent-emerald-800">
                        <span class="text-xs text-zinc-600">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="touch-target w-full py-2.5 px-4 rounded-xl btn-primary font-semibold text-xs sm:text-sm shadow-xs inline-flex items-center justify-center gap-2 transition-all mt-2">
                    <span>Masuk ke Akun</span>
                    <i class="fa-light fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Bottom Register Link -->
            <div class="pt-6 mt-6 border-t border-zinc-100 text-center text-xs text-zinc-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-bold text-emerald-800 hover:text-emerald-950 transition-colors ml-1">
                    Daftar sekarang gratis
                </a>
            </div>

        </div>

        <!-- Micro Info Under Card -->
        <p class="text-center text-[11px] text-zinc-400 mt-4 leading-relaxed">
            Teman yang ikut patungan tidak perlu login atau buat akun.
        </p>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passInput = document.getElementById('password');
        const passIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passInput && passIcon) {
            toggleBtn.addEventListener('click', function () {
                const isPass = passInput.type === 'password';
                passInput.type = isPass ? 'text' : 'password';
                passIcon.className = isPass ? 'fa-light fa-eye-slash text-xs' : 'fa-light fa-eye text-xs';
            });
        }
    });
</script>
@endpush
