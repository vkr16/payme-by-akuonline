@extends('layouts.app')

@section('title', 'Dashboard Penagih - PayMe')
@section('meta_description', 'Kelola rekening, QRIS, dan riwayat tagihan patungan PayMe Anda.')

@section('content')
<div class="space-y-6 pb-16">

    <!-- Welcome Greeting Header -->
    <div class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200/60 flex items-center justify-center font-bold text-xl shadow-2xs">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-zinc-900 tracking-tight">Halo, {{ $user->name }}!</h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                        <i class="fa-light fa-badge-check text-xs"></i>
                        <span>Host Aktif</span>
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    {{ $user->email }} &bull; Sesi tersinkronisasi aman
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full md:w-auto">
            <a href="{{ route('bills.create') }}" class="touch-target flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl btn-primary font-semibold text-xs sm:text-sm shadow-xs transition-all">
                <i class="fa-light fa-plus text-xs"></i>
                <span>Buat Tagihan Baru</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Tagihan Dibuat -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs">
            <div class="flex items-center justify-between text-zinc-400 mb-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Tagihan</span>
                <i class="fa-light fa-receipt text-lg text-emerald-800"></i>
            </div>
            <div class="text-2xl font-black text-zinc-900 tabular-nums">{{ $totalBills }}</div>
            <span class="text-[11px] text-zinc-400 mt-1 block">{{ $totalBills > 0 ? $totalBills . ' tagihan tersimpan' : 'Belum ada bill yang dibuat' }}</span>
        </div>

        <!-- Card 2: Status QRIS -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs">
            <div class="flex items-center justify-between text-zinc-400 mb-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">QRIS Terhubung</span>
                <i class="fa-light fa-qrcode text-lg text-zinc-700"></i>
            </div>
            <div class="text-sm font-bold {{ $hasQris ? 'text-emerald-800' : 'text-zinc-800' }} mt-1">
                {{ $hasQris ? 'Aktif di Database' : 'Belum Tersimpan' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1 block">Otomatis dipakai saat buat bill</span>
        </div>

        <!-- Card 3: Rekening Bank -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs">
            <div class="flex items-center justify-between text-zinc-400 mb-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Metode Bank / E-Wallet</span>
                <i class="fa-light fa-building-columns text-lg text-zinc-700"></i>
            </div>
            <div class="text-sm font-bold {{ $hasBank ? 'text-emerald-800' : 'text-zinc-800' }} mt-1">
                {{ $hasBank ? 'Tersimpan' : 'Belum Tersimpan' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1 block">Pilihan transfer kawan patungan</span>
        </div>
    </div>

    <!-- Riwayat Tagihan Aktif -->
    @if($bills->count() > 0)
        <div class="card-solid rounded-2xl p-6 bg-white border border-zinc-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-zinc-900 flex items-center gap-2">
                    <i class="fa-light fa-clock-rotate-left text-emerald-700"></i>
                    <span>Tagihan Patungan Kamu</span>
                </h2>
                <span class="text-xs font-semibold text-zinc-500">{{ $bills->count() }} tagihan</span>
            </div>

            <div class="divide-y divide-zinc-100">
                @foreach($bills as $bill)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 first:pt-0 last:pb-0">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('bills.show', ['slug' => $bill->slug]) }}" class="font-bold text-zinc-900 hover:text-emerald-800 text-sm transition-colors">
                                    {{ $bill->title }}
                                </a>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                                    Aktif
                                </span>
                            </div>
                            <div class="text-xs text-zinc-500 flex items-center gap-3">
                                <span><i class="fa-light fa-calendar text-[11px] mr-1"></i>{{ $bill->created_at->translatedFormat('d M Y') }}</span>
                                <span>&bull;</span>
                                <span>{{ $bill->items->count() }} menu pesanan</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3">
                            <div class="text-right">
                                <div class="text-sm font-black text-emerald-900 tabular-nums">
                                    Rp {{ number_format($bill->grand_total, 0, ',', '.') }}
                                </div>
                            </div>
                            <a href="{{ route('bills.show', ['slug' => $bill->slug]) }}" class="touch-target px-3 py-1.5 rounded-lg bg-zinc-100 hover:bg-emerald-50 text-zinc-700 hover:text-emerald-800 font-semibold text-xs border border-zinc-200 transition-colors inline-flex items-center gap-1.5">
                                <span>Detail</span>
                                <i class="fa-light fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Onboarding Guide Card for Host -->
        <div class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm space-y-4">
            <h2 class="text-base sm:text-lg font-bold text-zinc-900 flex items-center gap-2">
                <i class="fa-light fa-sparkles text-emerald-700"></i>
                <span>Langkah Pertama Sebagai Host</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200/70 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-zinc-800">
                        <span class="w-5 h-5 rounded-full bg-emerald-800 text-white flex items-center justify-center text-[10px]">1</span>
                        <span>Scan Struk atau Input Menu</span>
                    </div>
                    <p class="text-xs text-zinc-600 leading-relaxed">
                        Foto struk makan atau ketik pesanan secara cepat. Sistem otomatis menghitung pembagian proporsional yang adil untuk seluruh kawan.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200/70 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-zinc-800">
                        <span class="w-5 h-5 rounded-full bg-emerald-800 text-white flex items-center justify-center text-[10px]">2</span>
                        <span>Simpan QRIS & Rekening Sekali Saja</span>
                    </div>
                    <p class="text-xs text-zinc-600 leading-relaxed">
                        Saat membuat tagihan, centang opsi <em>Simpan ke Akun</em> agar nomor rekening & QRIS Anda otomatis tersimpan di database untuk tagihan selanjutnya.
                    </p>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
