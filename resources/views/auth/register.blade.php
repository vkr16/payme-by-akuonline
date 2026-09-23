@extends('layouts.app')

@section('title', 'Daftar Akun - PayMe')
@section('meta_description', 'Daftar akun gratis di PayMe. Kelola rekening bank, simpan QRIS, dan bagi tagihan makan tanpa ribet hitung manual.')

@section('content')
<div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-6 sm:py-10">
    <div class="w-full max-w-md">

        <!-- Card Container -->
        <div class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm relative">

            <!-- Card Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200/60 mb-3 shadow-2xs">
                    <i class="fa-light fa-user-plus text-xl"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-zinc-900 tracking-tight">Buat Akun PayMe</h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Mulai buat tagihan lebih praktis dengan QRIS
                </p>
            </div>

            <!-- Value Callout Mini Banner -->
            <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200/70 flex items-start gap-2.5 mb-6 text-xs text-emerald-950">
                <i class="fa-light fa-shield-check text-emerald-700 text-sm mt-0.5 flex-shrink-0"></i>
                <div>
                    <span class="font-bold block">100% Gratis &bull; Tanpa Verifikasi Email Rumit</span>
                    <span class="text-[11px] text-emerald-800">Akun langsung aktif dan siap digunakan seketika.</span>
                </div>
            </div>

            <!-- Form Registrasi Email & Password -->
            <form action="#" method="POST" class="space-y-4">
                @csrf

                <!-- Input: Nama Lengkap / Panggilan -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-bold text-zinc-700">
                        Nama Lengkap / Panggilan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-zinc-400 pointer-events-none text-xs">
                            <i class="fa-light fa-user"></i>
                        </span>
                        <input type="text" id="name" name="name" required autocomplete="name" placeholder="Contoh: Fikri M" class="touch-target w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm bg-white border border-zinc-300 rounded-xl focus:outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/10 text-zinc-900 placeholder:text-zinc-400 transition-colors">
                    </div>
                    <span class="text-[10px] text-zinc-400 block">Nama ini akan dilihat temanmu di halaman tagihan patungan.</span>
                </div>

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
                    <label for="password" class="block text-xs font-bold text-zinc-700">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-zinc-400 pointer-events-none text-xs">
                            <i class="fa-light fa-lock-keyhole"></i>
                        </span>
                        <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" class="touch-target w-full pl-9 pr-10 py-2.5 text-xs sm:text-sm bg-white border border-zinc-300 rounded-xl focus:outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/10 text-zinc-900 placeholder:text-zinc-400 transition-colors">
                        <button type="button" id="toggleRegPasswordBtn" class="touch-target absolute right-2.5 w-7 h-7 flex items-center justify-center text-zinc-400 hover:text-zinc-700 transition-colors" title="Lihat/Sembunyikan Kata Sandi">
                            <i class="fa-light fa-eye text-xs" id="toggleRegPasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Input: Password Confirmation -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-zinc-700">
                        Ulangi Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-zinc-400 pointer-events-none text-xs">
                            <i class="fa-light fa-shield-check"></i>
                        </span>
                        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi" class="touch-target w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm bg-white border border-zinc-300 rounded-xl focus:outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/10 text-zinc-900 placeholder:text-zinc-400 transition-colors">
                    </div>
                </div>

                <!-- Agreement & Privacy Note -->
                <p class="text-[11px] text-zinc-500 leading-relaxed pt-1">
                    Dengan mendaftar, kamu menyetujui penggunaan PayMe untuk pembagian tagihan yang adil dan transparan.
                </p>

                <!-- Submit Button -->
                <button type="submit" class="touch-target w-full py-2.5 px-4 rounded-xl btn-primary font-semibold text-xs sm:text-sm shadow-xs inline-flex items-center justify-center gap-2 transition-all mt-2">
                    <span>Buat Akun Sekarang</span>
                    <i class="fa-light fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Bottom Login Link -->
            <div class="pt-6 mt-6 border-t border-zinc-100 text-center text-xs text-zinc-600">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-emerald-800 hover:text-emerald-950 transition-colors ml-1">
                    Masuk di sini
                </a>
            </div>

        </div>

        <!-- Micro Info Under Card -->
        <p class="text-center text-[11px] text-zinc-400 mt-4 leading-relaxed">
            Data rekening dan QRIS tersimpan aman untuk memudahkan pembagian tagihan berikutnya.
        </p>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleRegPasswordBtn');
        const passInput = document.getElementById('password');
        const passIcon = document.getElementById('toggleRegPasswordIcon');

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
