@extends('layouts.app')

@section('title', 'PayMe - Split Bill Lebih Adil, Bayar Pakai QRIS Lebih Praktis')
@section('meta_description', 'Bagi tagihan patungan dari foto struk secara proporsional dan bayar instan pakai QRIS dinamis dengan nominal pas terkunci.')

@section('content')
<div class="space-y-16 sm:space-y-24 py-4 sm:py-6">

    <!-- ========================================== -->
    <!-- 1. HERO SECTION                            -->
    <!-- ========================================== -->
    <section class="text-center max-w-3xl mx-auto pt-4 sm:pt-8">
        <!-- Top Pill Badge -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold mb-6 shadow-2xs">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
            <span>Split Bill &amp; QRIS Dinamis Indonesia</span>
        </div>

        <!-- Main Headline (AB-AB Rhyme requested by user) -->
        <h1 class="text-3xl sm:text-5xl font-extrabold text-zinc-900 tracking-tight leading-[1.15] mb-5">
            Split Bill Lebih Adil,<br class="hidden sm:inline" />
            <span class="text-emerald-800">Bayar Pakai QRIS Lebih Praktis.</span>
        </h1>

        <!-- Subheadline (Revised: without "tanpa bukti palsu", focused on precision & hassle-free calculation) -->
        <p class="text-sm sm:text-base text-zinc-600 leading-relaxed max-w-2xl mx-auto mb-8">
            Foto struk untuk bagi pesanan per orang secara proporsional. Teman pilih menu sendiri, dapatkan QRIS dengan nominal pas terkunci — tanpa repot hitung manual, tanpa risiko salah transfer.
        </p>        <!-- Action CTAs -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 mb-8">
            <a href="#daftar" class="touch-target w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold btn-primary shadow-xs">
                <span>Mulai Gratis Sekarang</span>
                <i class="fa-light fa-arrow-right text-xs"></i>
            </a>

            <a href="#cara-kerja" class="touch-target w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg text-sm font-semibold bg-white border border-zinc-200/90 text-zinc-700 hover:text-zinc-900 hover:border-zinc-300 transition-colors shadow-2xs">
                <i class="fa-light fa-circle-play text-zinc-400 text-xs"></i>
                <span>Lihat Cara Kerja</span>
            </a>
        </div>

        <!-- Social Proof Micro-metrics -->
        <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-zinc-500 font-medium">
            <div class="flex items-center gap-1.5">
                <i class="fa-light fa-check text-emerald-700"></i>
                <span>100% Bebas Biaya Layanan</span>
            </div>
            <div class="flex items-center gap-1.5">
                <i class="fa-light fa-check text-emerald-700"></i>
                <span>Standar QRIS Nasional</span>
            </div>
            <div class="flex items-center gap-1.5">
                <i class="fa-light fa-check text-emerald-700"></i>
                <span>Teman Tak Perlu Buat Akun</span>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- HERO TACTILE PREVIEW: REAL USER FLOW SCENARIO                   -->
        <!-- Flow: Teman memilih menu sendiri -> Generate QRIS -> Klaim -> Host Approve -->
        <!-- ============================================================== -->
        <div class="card-solid rounded-2xl p-4 sm:p-7 mt-10 sm:mt-12 text-left relative overflow-hidden shadow-sm">
            <!-- Simulated Browser/App Header Bar -->
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-zinc-100">
                <div class="flex items-center gap-2.5">
                    <div class="flex gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-zinc-200"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-zinc-200"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-zinc-200"></span>
                    </div>
                    <span class="text-xs font-semibold text-zinc-500 ml-2">Simulasi Mekanisme Memilih & Membayar Pesanan</span>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                    <i class="fa-light fa-hand-pointer text-[10px]"></i> Coba Pilih Menu
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                <!-- Left Column: Menu Selection by Participant with Quantity Stepper -->
                <div class="md:col-span-7 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900">Pilih Menu Pesananmu</h2>
                            <p class="text-xs text-zinc-500">Tentukan jumlah item yang kamu ambil dari sisa tagihan di struk</p>
                        </div>
                        {{-- <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                            Struk Bersama
                        </span> --}}
                    </div>

                    <!-- Items from Receipt with Quantity Steppers -->
                    <div class="space-y-2.5" id="menu-items-list">
                        <!-- Item 1: Nasi Goreng Spesial (Total struk: 3, 1 sudah terbayar, sisa 2) -->
                        <div class="item-row p-3 rounded-xl bg-white border-2 border-emerald-600/80 shadow-2xs flex items-center justify-between transition-colors" data-price="35000" data-max="2" data-qty="1">
                            <div class="flex-1 pr-3 min-w-0">
                                <div class="text-xs font-bold text-zinc-800 leading-snug">Nasi Goreng Spesial</div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[11px] text-zinc-500 tabular-nums">@ Rp 35.000</span>
                                    <span class="text-zinc-300 text-[10px]">&bull;</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/50">1/3 terbayar</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 flex-shrink-0">
                                <div class="flex items-center bg-zinc-50 border border-zinc-200 rounded-lg p-0.5">
                                    <button type="button" class="btn-qty-minus w-6 h-6 rounded flex items-center justify-center text-zinc-600 hover:bg-zinc-200 disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer">
                                        <i class="fa-light fa-minus text-[9px]"></i>
                                    </button>
                                    <span class="qty-display w-7 text-center text-xs font-bold text-zinc-900 tabular-nums">1</span>
                                    <button type="button" class="btn-qty-plus w-6 h-6 rounded flex items-center justify-center text-zinc-600 hover:bg-zinc-200 disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer">
                                        <i class="fa-light fa-plus text-[9px]"></i>
                                    </button>
                                </div>
                                <span class="item-subtotal text-xs font-bold text-zinc-900 tabular-nums w-16 text-right">Rp 35.000</span>
                            </div>
                        </div>

                        <!-- Item 2: Jus Alpukat (Total struk: 2, 0 terbayar, sisa 2) -->
                        <div class="item-row p-3 rounded-xl bg-white border-2 border-emerald-600/80 shadow-2xs flex items-center justify-between transition-colors" data-price="18000" data-max="2" data-qty="1">
                            <div class="flex-1 pr-3 min-w-0">
                                <div class="text-xs font-bold text-zinc-800 leading-snug">Jus Alpukat</div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[11px] text-zinc-500 tabular-nums">@ Rp 18.000</span>
                                    <span class="text-zinc-300 text-[10px]">&bull;</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-500 font-medium">0/2 terbayar</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 flex-shrink-0">
                                <div class="flex items-center bg-zinc-50 border border-zinc-200 rounded-lg p-0.5">
                                    <button type="button" class="btn-qty-minus w-6 h-6 rounded flex items-center justify-center text-zinc-600 hover:bg-zinc-200 disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer">
                                        <i class="fa-light fa-minus text-[9px]"></i>
                                    </button>
                                    <span class="qty-display w-7 text-center text-xs font-bold text-zinc-900 tabular-nums">1</span>
                                    <button type="button" class="btn-qty-plus w-6 h-6 rounded flex items-center justify-center text-zinc-600 hover:bg-zinc-200 disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer">
                                        <i class="fa-light fa-plus text-[9px]"></i>
                                    </button>
                                </div>
                                <span class="item-subtotal text-xs font-bold text-zinc-900 tabular-nums w-16 text-right">Rp 18.000</span>
                            </div>
                        </div>

                        <!-- Item 3: Ayam Bakar Madu (Total struk: 3, 2 terbayar, sisa 1) -->
                        <div class="item-row p-3 rounded-xl bg-zinc-50 border border-zinc-200/80 flex items-center justify-between transition-colors" data-price="32000" data-max="1" data-qty="0">
                            <div class="flex-1 pr-3 min-w-0">
                                <div class="text-xs font-bold text-zinc-800 leading-snug">Ayam Bakar Madu</div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[11px] text-zinc-500 tabular-nums">@ Rp 32.000</span>
                                    <span class="text-zinc-300 text-[10px]">&bull;</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/50">2/3 terbayar</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 flex-shrink-0">
                                <div class="flex items-center bg-white border border-zinc-200 rounded-lg p-0.5">
                                    <button type="button" class="btn-qty-minus w-6 h-6 rounded flex items-center justify-center text-zinc-600 hover:bg-zinc-200 disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer" disabled>
                                        <i class="fa-light fa-minus text-[9px]"></i>
                                    </button>
                                    <span class="qty-display w-7 text-center text-xs font-bold text-zinc-900 tabular-nums">0</span>
                                    <button type="button" class="btn-qty-plus w-6 h-6 rounded flex items-center justify-center text-zinc-600 hover:bg-zinc-200 disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer">
                                        <i class="fa-light fa-plus text-[9px]"></i>
                                    </button>
                                </div>
                                <span class="item-subtotal text-xs font-bold text-zinc-400 tabular-nums w-16 text-right">Rp 0</span>
                            </div>
                        </div>

                        <!-- Item 4: Es Teh Manis (Total struk: 4, 1 terbayar, sisa 3) -->
                        <div class="item-row p-3 rounded-xl bg-zinc-50 border border-zinc-200/80 flex items-center justify-between transition-colors" data-price="6000" data-max="3" data-qty="0">
                            <div class="flex-1 pr-3 min-w-0">
                                <div class="text-xs font-bold text-zinc-800 leading-snug">Es Teh Manis</div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[11px] text-zinc-500 tabular-nums">@ Rp 6.000</span>
                                    <span class="text-zinc-300 text-[10px]">&bull;</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/50">1/4 terbayar</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 flex-shrink-0">
                                <div class="flex items-center bg-white border border-zinc-200 rounded-lg p-0.5">
                                    <button type="button" class="btn-qty-minus w-6 h-6 rounded flex items-center justify-center text-zinc-600 hover:bg-zinc-200 disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer" disabled>
                                        <i class="fa-light fa-minus text-[9px]"></i>
                                    </button>
                                    <span class="qty-display w-7 text-center text-xs font-bold text-zinc-900 tabular-nums">0</span>
                                    <button type="button" class="btn-qty-plus w-6 h-6 rounded flex items-center justify-center text-zinc-600 hover:bg-zinc-200 disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer">
                                        <i class="fa-light fa-plus text-[9px]"></i>
                                    </button>
                                </div>
                                <span class="item-subtotal text-xs font-bold text-zinc-400 tabular-nums w-16 text-right">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown: Proporsional Murni Berdasarkan Persentase Belanja Produk -->
                    <div class="pt-3 border-t border-zinc-200/70 space-y-1.5 text-xs">
                        <div class="flex justify-between text-zinc-600">
                            <span>Subtotal Menu Dipilih:</span>
                            <span id="demo-subtotal" class="font-semibold text-zinc-900 tabular-nums">Rp 53.000</span>
                        </div>
                        <div class="flex justify-between text-zinc-500">
                            <span class="flex items-center gap-1">
                                <i class="fa-light fa-chart-pie text-[10px] text-emerald-700"></i> Porsi Belanja dari Total Produk:
                            </span>
                            <span id="demo-percentage" class="font-bold text-emerald-800 tabular-nums">20,3%</span>
                        </div>
                        <div class="flex justify-between text-emerald-700">
                            <span class="flex items-center gap-1">
                                <i class="fa-light fa-tag text-[10px]"></i> Diskon Promo Resto (<span class="demo-pct-label">20,3%</span>):
                            </span>
                            <span id="demo-discount" class="font-semibold tabular-nums">-Rp 5.300</span>
                        </div>
                        <div class="flex justify-between text-zinc-600">
                            <span class="flex items-center gap-1">
                                <i class="fa-light fa-motorcycle text-[10px] text-zinc-400"></i> Ongkos Kirim (<span class="demo-pct-label">20,3%</span>):
                            </span>
                            <span id="demo-shipping" class="font-semibold text-zinc-900 tabular-nums">+Rp 3.046</span>
                        </div>
                        <div class="flex justify-between text-zinc-600">
                            <span class="flex items-center gap-1">
                                <i class="fa-light fa-receipt text-[10px] text-zinc-400"></i> Biaya Tambahan Lainnya (<span class="demo-pct-label">20,3%</span>):
                            </span>
                            <span id="demo-extra" class="font-semibold text-zinc-900 tabular-nums">+Rp 6.313</span>
                        </div>
                        <div class="flex justify-between text-zinc-900 font-bold pt-1.5 border-t border-dashed border-zinc-200 text-sm">
                            <span>Total yang Harus Kamu Bayar:</span>
                            <span id="demo-grand-total" class="text-emerald-800 font-extrabold tabular-nums">Rp 57.059</span>
                        </div>
                        <div class="text-[10px] text-zinc-400 italic pt-0.5 text-right">
                            *Dihitung proporsional dari total belanja produk di struk (Rp 261.000)
                        </div>
                    </div>
                </div>

                <!-- Right Column: Generated Dynamic QRIS + Claim/Approve Flow -->
                <div class="md:col-span-5 bg-zinc-50 border border-zinc-200/90 rounded-xl p-4 sm:p-5 flex flex-col items-center text-center relative">
                    <!-- Dynamic Stamp Container -->
                    <div id="hero-stamp" class="absolute inset-0 flex items-center justify-center pointer-events-none z-20 hidden">
                        <div class="stamp-lunas stamp-lunas-animate px-5 py-2 font-black text-xl tracking-widest uppercase border-4 rounded-lg">
                            LUNAS
                        </div>
                    </div>

                    <div class="w-full flex items-center justify-between pb-3 border-b border-zinc-200 text-left mb-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400">QRIS Dinamis Kamu</span>
                            <div id="qr-total-display" class="text-base font-extrabold text-zinc-900 tabular-nums">Rp 57.059</div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                            <i class="fa-light fa-lock text-[9px]"></i> Nominal Pas
                        </span>
                    </div>

                    <!-- Clean QR Code Visual with Real SVG -->
                    <div class="bg-white p-3 rounded-lg border border-zinc-200/80 shadow-2xs mb-2 flex items-center justify-center">
                        <img src="{{ asset('images/qris-saya.svg') }}" alt="QRIS Dinamis PayMe" class="w-32 h-32 object-contain rounded">
                    </div>

                    <!-- Status Indicator Badge -->
                    <div id="flow-status-pill" class="mb-3 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-zinc-200 text-zinc-700">
                        Status: Belum Dibayar
                    </div>

                    <!-- Flow Step Simulation Buttons -->
                    <div class="w-full space-y-2">
                        <!-- Step A: Teman Klaim Sudah Bayar -->
                        <button type="button" id="btn-step-claim" class="w-full py-2 px-3 text-xs font-semibold rounded-lg bg-emerald-800 text-white hover:bg-emerald-700 transition-colors shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-light fa-paper-plane text-[11px]"></i>
                            <span>1. Teman: Klaim Sudah Bayar</span>
                        </button>

                        <!-- Step B: Host Konfirmasi / Approve -->
                        <button type="button" id="btn-step-approve" class="w-full py-2 px-3 text-xs font-semibold rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 transition-colors shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer opacity-50 pointer-events-none" disabled>
                            <i class="fa-light fa-circle-check text-[11px] text-emerald-400"></i>
                            <span>2. Host: Konfirmasi Dana Masuk</span>
                        </button>

                        <!-- Reset Button -->
                        <button type="button" id="btn-step-reset" class="w-full py-1.5 px-3 text-[11px] font-medium text-zinc-500 hover:text-zinc-800 transition-colors cursor-pointer hidden">
                            <i class="fa-light fa-rotate-left mr-1"></i> Reset Simulasi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 2. VALUE PILLARS (3 Masalah yang Diatasi)  -->
    <!-- ========================================== -->
    <section class="py-4">
        <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 mb-2 block">
                MENGAPA PAYME
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight mb-3">
                Bukan Sekadar Bagi Rata, Tapi Bagi Sesuai Porsi Hak Masing-Masing
            </h2>
            <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">
                Jajan bareng teman jadi bebas drama salah hitung, bebas canggung menagih, dan adil untuk semua orang.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: AI / OCR Receipt Scanner -->
            <div class="card-solid rounded-xl p-6 sm:p-7 flex flex-col justify-between hover:border-zinc-300 transition-colors">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center text-base mb-4 border border-emerald-200/60 shadow-2xs">
                        <i class="fa-light fa-receipt"></i>
                    </div>
                    <h3 class="text-base font-bold text-zinc-900 mb-2">Scan Struk Otomatis</h3>
                    <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed mb-4">
                        Cukup foto struk restoran berformat apapun. Sistem mendeteksi daftar menu, harga satuan, diskon promo, pajak, hingga service charge tanpa perlu ketik manual.
                    </p>
                </div>
                <div class="pt-3 border-t border-zinc-100 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-800">
                    <i class="fa-light fa-wand-magic-sparkles text-[10px]"></i>
                    <span>Ekstraksi Cepat &amp; Akurat</span>
                </div>
            </div>

            <!-- Card 2: Dynamic QRIS -->
            <div class="card-solid rounded-xl p-6 sm:p-7 flex flex-col justify-between hover:border-zinc-300 transition-colors">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center text-base mb-4 border border-emerald-200/60 shadow-2xs">
                        <i class="fa-light fa-qrcode"></i>
                    </div>
                    <h3 class="text-base font-bold text-zinc-900 mb-2">QRIS Dinamis Presisi</h3>
                    <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed mb-4">
                        Ubah QRIS statis rekeningmu menjadi QRIS dinamis dengan nominal pas terkunci. Teman tinggal scan tanpa perlu input nominal secara manual.
                    </p>
                </div>
                <div class="pt-3 border-t border-zinc-100 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-800">
                    <i class="fa-light fa-shield-halved text-[10px]"></i>
                    <span>Cegah Salah Transfer &amp; Typo</span>
                </div>
            </div>

            <!-- Card 3: Pure Proportional Split -->
            <div class="card-solid rounded-xl p-6 sm:p-7 flex flex-col justify-between hover:border-zinc-300 transition-colors">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center text-base mb-4 border border-emerald-200/60 shadow-2xs">
                        <i class="fa-light fa-scale-balanced"></i>
                    </div>
                    <h3 class="text-base font-bold text-zinc-900 mb-2">Proporsional Murni Berdasarkan Nilai Produk</h3>
                    <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed mb-4">
                        Semua potongan diskon, ongkos kirim, pajak (PB1), hingga biaya kemasan dibagi proporsional sebesar persentase harga belanjaan produkmu terhadap total pesanan di struk. Bebas dari ketidakadilan ongkir bagi rata!
                    </p>
                </div>
                <div class="pt-3 border-t border-zinc-100 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-800">
                    <i class="fa-light fa-calculator text-[10px]"></i>
                    <span>Adil &amp; Presisi Sesuai Persentase Belanja</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 3. HOW IT WORKS (3 Langkah Sederhana)       -->
    <!-- ========================================== -->
    <section id="cara-kerja" class="py-4">
        <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 mb-2 block">
                CARA KERJA
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight mb-3">
                Selesai dalam 3 Langkah Sederhana
            </h2>
            <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">
                Dari foto struk hingga dana terverifikasi masuk ke rekeningmu dengan aman.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Step 1 -->
            <div class="card-solid rounded-xl p-6 sm:p-7 relative">
                <span class="text-2xl font-black text-emerald-800/20 mb-3 block">01</span>
                <h3 class="text-base font-bold text-zinc-900 mb-2">Unggah Struk &amp; QRIS</h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">
                    Cukup foto struk dan pilih QRIS rekening bank atau e-wallet kamu yang sudah tersimpan di akun penagihmu.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="card-solid rounded-xl p-6 sm:p-7 relative">
                <span class="text-2xl font-black text-emerald-800/20 mb-3 block">02</span>
                <h3 class="text-base font-bold text-zinc-900 mb-2">Teman Pilih Menu Sendiri</h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">
                    Bagikan tautan tagihan. Teman cukup buka link di browser HP dan mencentang menu apa saja yang mereka pesan.
                </p>
            </div>

            <!-- Step 3 (Updated: Scan, Claim, Host Confirm) -->
            <div class="card-solid rounded-xl p-6 sm:p-7 relative">
                <span class="text-2xl font-black text-emerald-800/20 mb-3 block">03</span>
                <h3 class="text-base font-bold text-zinc-900 mb-2">Scan, Klaim &amp; Konfirmasi Host</h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">
                    QRIS muncul dengan nominal pas. Setelah bayar, teman klik klaim, dan kamu bisa konfirmasi penerimaan dana dalam 1 klik.
                </p>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 4. VALUE FOR COLLECTORS (Host Value Section)-->
    <!-- Catchy headline without jargon "host",      -->
    <!-- highlights ownership & verification value  -->
    <!-- ========================================== -->
    <section id="daftar" class="card-solid rounded-2xl p-6 sm:p-10 border border-zinc-200/90 shadow-xs">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-8 sm:mb-10">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 mb-2 block">
                    UNTUK YANG SERING NALANGIN
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight mb-3">
                    Sering Nalangin Teman? Simpan Semua Tagihanmu Otomatis.
                </h2>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed max-w-xl mx-auto">
                    Tak perlu repot ketik nomor rekening atau cari file QRIS berulang-ulang setiap kali kumpul bareng. Cukup daftar sekali, semua tersimpan rapi dan verifikasi dana jadi mudah.
                </p>
            </div>

            <!-- 3 Ownership Advantages -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                <div class="p-4 sm:p-5 rounded-xl bg-zinc-50 border border-zinc-200/70">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm mb-3">
                        <i class="fa-light fa-wallet"></i>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold text-zinc-900 mb-1">Rekening &amp; QRIS Tersimpan</h3>
                    <p class="text-xs text-zinc-500 leading-relaxed">
                        Data pembayaranmu siap digunakan kapan saja untuk tagihan baru tanpa perlu upload ulang.
                    </p>
                </div>

                <div class="p-4 sm:p-5 rounded-xl bg-zinc-50 border border-zinc-200/70">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm mb-3">
                        <i class="fa-light fa-clipboard-check"></i>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold text-zinc-900 mb-1">Konfirmasi Dana Terkendali</h3>
                    <p class="text-xs text-zinc-500 leading-relaxed">
                        Pantau siapa yang sudah klaim bayar. Cek mutasimu dan setujui konfirmasi dengan 1 klik agar tidak ada klaim palsu.
                    </p>
                </div>

                <div class="p-4 sm:p-5 rounded-xl bg-zinc-50 border border-zinc-200/70">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm mb-3">
                        <i class="fa-light fa-mobile-screen-button"></i>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold text-zinc-900 mb-1">Teman Tak Perlu Buat Akun</h3>
                    <p class="text-xs text-zinc-500 leading-relaxed">
                        Hanya kamu yang perlu login. Teman yang kamu tagih cukup buka tautan di browser HP untuk pilih pesanan dan bayar.
                    </p>
                </div>
            </div>

            <!-- CTA Callout Bar -->
            <div class="p-5 rounded-xl bg-emerald-50/70 border border-emerald-200/70 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-center sm:text-left">
                    <div class="text-sm font-bold text-emerald-950">Mulai Buat Akun Penagih Sekarang</div>
                    <div class="text-xs text-emerald-800">100% Gratis &bull; Tanpa batasan tagihan &bull; Siap dalam 1 menit</div>
                </div>
                <a href="{{ route('register') }}" class="touch-target w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg text-xs sm:text-sm font-semibold btn-primary shadow-xs">
                    <span>Mulai Gratis Sekarang</span>
                    <i class="fa-light fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 5. FAQ (Pertanyaan yang Sering Diajukan)    -->
    <!-- ========================================== -->
    <section class="py-4">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 mb-2 block">
                TANYA JAWAB
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight mb-3">
                Pertanyaan yang Sering Ditanyakan
            </h2>
            <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">
                Semua yang perlu kamu ketahui tentang alur kerja dan keamanan PayMe.
            </p>
        </div>

        <div class="max-w-3xl mx-auto space-y-4">
            <!-- FAQ 1 -->
            <div class="card-solid rounded-xl p-5 hover:border-zinc-300 transition-colors">
                <h3 class="text-sm font-bold text-zinc-900 mb-2 flex items-center gap-2">
                    <i class="fa-light fa-circle-question text-emerald-700 text-xs"></i>
                    <span>Apakah teman yang ikut patungan harus membuat akun juga?</span>
                </h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed pl-5">
                    Tidak sama sekali. Teman yang ditagih cukup membuka link patungan melalui browser HP mereka, memilih menu pesanan mereka, dan bayar lewat QRIS. Hanya kamu (yang menalangi dan membuat tagihan) yang membutuhkan akun agar profil rekening dan riwayat tagihan tersimpan rapi.
                </p>
            </div>

            <!-- FAQ 2 -->
            <div class="card-solid rounded-xl p-5 hover:border-zinc-300 transition-colors">
                <h3 class="text-sm font-bold text-zinc-900 mb-2 flex items-center gap-2">
                    <i class="fa-light fa-circle-question text-emerald-700 text-xs"></i>
                    <span>Bagaimana cara kerja konfirmasi pembayaran agar tidak ada klaim palsu?</span>
                </h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed pl-5">
                    Setelah teman transfer lewat QRIS, mereka menekan tombol "Klaim Sudah Bayar". Sebagai host, kamu menerima notifikasi klaim tersebut, memeriksa mutasi rekeningmu, lalu menekan konfirmasi untuk menyetujui penerimaan dana. Status tagihan baru berubah resmi menjadi "LUNAS".
                </p>
            </div>

            <!-- FAQ 3 -->
            <div class="card-solid rounded-xl p-5 hover:border-zinc-300 transition-colors">
                <h3 class="text-sm font-bold text-zinc-900 mb-2 flex items-center gap-2">
                    <i class="fa-light fa-circle-question text-emerald-700 text-xs"></i>
                    <span>QRIS apa saja yang didukung oleh PayMe?</span>
                </h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed pl-5">
                    Semua QRIS statis merchant didukung, seperti dari GoPay Merchant, ShopeePay Merchant, QRIS Interaktif, maupun penyedia merchant lainnya (bukan QR transfer antar-rekening pribadi bank biasa).
                </p>
            </div>

            <!-- FAQ 4 -->
            <div class="card-solid rounded-xl p-5 hover:border-zinc-300 transition-colors">
                <h3 class="text-sm font-bold text-zinc-900 mb-2 flex items-center gap-2">
                    <i class="fa-light fa-circle-question text-emerald-700 text-xs"></i>
                    <span>Apakah bayarnya cuma bisa lewat QRIS saja?</span>
                </h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed pl-5">
                    Nggak juga! Selain QRIS dinamis, host juga bisa mencantumkan nomor rekening bank konvensional (BCA, Mandiri, BRI, BNI, dll) maupun nomor e-wallet (GoPay, Dana, ShopeePay, OVO, dll). Teman bisa langsung menyalin nomor rekening dengan 1 kali klik.
                </p>
            </div>

            <!-- FAQ 5 -->
            <div class="card-solid rounded-xl p-5 hover:border-zinc-300 transition-colors">
                <h3 class="text-sm font-bold text-zinc-900 mb-2 flex items-center gap-2">
                    <i class="fa-light fa-circle-question text-emerald-700 text-xs"></i>
                    <span>Bagaimana kalau ada biaya di luar struk (misal parkir kurir, tips, atau ongkir manual)?</span>
                </h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed pl-5">
                    Host bisa menginput atau mengedit biaya tambahan secara manual setelah struk selesai di-scan oleh AI Vision. Kamu juga bisa melakukan koreksi mandiri jika AI melakukan kekeliruan atau kekurangan dalam membaca menu struk.
                </p>
            </div>

            <!-- FAQ 6 -->
            <div class="card-solid rounded-xl p-5 hover:border-zinc-300 transition-colors">
                <h3 class="text-sm font-bold text-zinc-900 mb-2 flex items-center gap-2">
                    <i class="fa-light fa-circle-question text-emerald-700 text-xs"></i>
                    <span>Bagaimana jika struk memiliki diskon atau promo voucher?</span>
                </h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed pl-5">
                    PayMe memiliki fitur perhitungan diskon otomatis. Potongan harga promo atau voucher akan dialokasikan secara proporsional sesuai persentase nilai pesanan produk masing-masing orang, sehingga pembagian tetap adil dan presisi.
                </p>
            </div>

            <!-- FAQ 7 -->
            <div class="card-solid rounded-xl p-5 hover:border-zinc-300 transition-colors">
                <h3 class="text-sm font-bold text-zinc-900 mb-2 flex items-center gap-2">
                    <i class="fa-light fa-circle-question text-emerald-700 text-xs"></i>
                    <span>Apakah ada potongan atau biaya menggunakan PayMe?</span>
                </h3>
                <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed pl-5">
                    Gak ada, PayMe 100% gratis digunakan tanpa potongan transaksi sepeserpun! Tapi kalau kamu merasa sangat terbantu dan mau donate atau beliin kopi buat developer-nya, tentu boleh banget hehe ☕.
                </p>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 6. FINAL BOTTOM CTA BANNER                 -->
    <!-- ========================================== -->
    <section class="card-solid rounded-2xl p-8 sm:p-12 text-center bg-white border border-zinc-200/90 shadow-xs relative overflow-hidden">
        <div class="max-w-xl mx-auto space-y-4">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight">
                Siap Berhenti Ribet Hitung Tagihan Patungan?
            </h2>
            <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed">
                Mulai hitung struk lebih adil dan pembayaran lebih praktis pakai QRIS dinamis.
            </p>
            <div class="pt-2">
                <a href="{{ route('register') }}" class="touch-target inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold btn-primary shadow-xs">
                    <span>Mulai Gratis Sekarang</span>
                    <i class="fa-light fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemRows = document.querySelectorAll('.item-row');
        const subtotalEl = document.getElementById('demo-subtotal');
        const discountEl = document.getElementById('demo-discount');
        const shippingEl = document.getElementById('demo-shipping');
        const extraEl = document.getElementById('demo-extra');
        const grandTotalEl = document.getElementById('demo-grand-total');
        const qrTotalEl = document.getElementById('qr-total-display');

        // Flow elements
        const btnClaim = document.getElementById('btn-step-claim');
        const btnApprove = document.getElementById('btn-step-approve');
        const btnReset = document.getElementById('btn-step-reset');
        const statusPill = document.getElementById('flow-status-pill');
        const stamp = document.getElementById('hero-stamp');

        function formatRupiah(num) {
            return 'Rp ' + num.toLocaleString('id-ID');
        }

        function calculateBill() {
            let subtotal = 0;

            itemRows.forEach(row => {
                const qty = parseInt(row.getAttribute('data-qty') || '0', 10);
                const price = parseInt(row.getAttribute('data-price') || '0', 10);
                const max = parseInt(row.getAttribute('data-max') || '1', 10);
                const itemTotal = qty * price;

                subtotal += itemTotal;

                // Update row subtotal display
                const subtotalDisplay = row.querySelector('.item-subtotal');
                if (subtotalDisplay) {
                    subtotalDisplay.textContent = formatRupiah(itemTotal);
                    if (qty > 0) {
                        subtotalDisplay.className = 'item-subtotal text-xs font-bold text-zinc-900 tabular-nums w-16 text-right';
                    } else {
                        subtotalDisplay.className = 'item-subtotal text-xs font-bold text-zinc-400 tabular-nums w-16 text-right';
                    }
                }

                // Update row styling
                if (qty > 0) {
                    row.className = 'item-row p-3 rounded-xl bg-white border-2 border-emerald-600/80 shadow-2xs flex items-center justify-between transition-colors';
                } else {
                    row.className = 'item-row p-3 rounded-xl bg-zinc-50 border border-zinc-200/80 flex items-center justify-between transition-colors';
                }

                // Update minus/plus buttons state
                const btnMinus = row.querySelector('.btn-qty-minus');
                const btnPlus = row.querySelector('.btn-qty-plus');
                const qtyDisplay = row.querySelector('.qty-display');

                if (qtyDisplay) qtyDisplay.textContent = qty;
                if (btnMinus) btnMinus.disabled = (qty <= 0);
                if (btnPlus) btnPlus.disabled = (qty >= max);
            });

            const TOTAL_RECEIPT_PRODUCT = 261000;
            const TOTAL_RECEIPT_DISCOUNT = 26100; // Total Diskon Struk (10%)
            const TOTAL_RECEIPT_SHIPPING = 15000; // Total Ongkir Struk
            const TOTAL_RECEIPT_EXTRA = 31100;    // Total Pajak & Kemasan Struk

            const percentageEl = document.getElementById('demo-percentage');
            const pctLabels = document.querySelectorAll('.demo-pct-label');

            let discount = 0;
            let shipping = 0;
            let extra = 0;
            let grandTotal = 0;

            if (subtotal > 0) {
                // Rasio proporsional murni terhadap total belanja produk di struk
                const ratio = subtotal / TOTAL_RECEIPT_PRODUCT;
                const pctFormatted = (ratio * 100).toFixed(1).replace('.', ',') + '%';

                discount = Math.round(TOTAL_RECEIPT_DISCOUNT * ratio);
                shipping = Math.round(TOTAL_RECEIPT_SHIPPING * ratio);
                extra = Math.round(TOTAL_RECEIPT_EXTRA * ratio);
                grandTotal = subtotal - discount + shipping + extra;

                if (percentageEl) percentageEl.textContent = pctFormatted;
                pctLabels.forEach(lbl => lbl.textContent = pctFormatted);

                if (btnClaim && !btnClaim.hasAttribute('data-claimed')) {
                    btnClaim.classList.remove('opacity-50', 'pointer-events-none');
                    btnClaim.disabled = false;
                }
            } else {
                if (percentageEl) percentageEl.textContent = '0%';
                pctLabels.forEach(lbl => lbl.textContent = '0%');

                if (btnClaim && !btnClaim.hasAttribute('data-claimed')) {
                    btnClaim.classList.add('opacity-50', 'pointer-events-none');
                    btnClaim.disabled = true;
                }
            }

            if (subtotalEl) subtotalEl.textContent = formatRupiah(subtotal);
            if (discountEl) discountEl.textContent = discount > 0 ? '-' + formatRupiah(discount) : 'Rp 0';
            if (shippingEl) shippingEl.textContent = shipping > 0 ? '+' + formatRupiah(shipping) : 'Rp 0';
            if (extraEl) extraEl.textContent = extra > 0 ? '+' + formatRupiah(extra) : 'Rp 0';
            if (grandTotalEl) grandTotalEl.textContent = formatRupiah(grandTotal);
            if (qrTotalEl) qrTotalEl.textContent = formatRupiah(grandTotal);
        }

        // Stepper click handlers
        itemRows.forEach(row => {
            const btnMinus = row.querySelector('.btn-qty-minus');
            const btnPlus = row.querySelector('.btn-qty-plus');
            const max = parseInt(row.getAttribute('data-max') || '1', 10);

            if (btnMinus) {
                btnMinus.addEventListener('click', function () {
                    let currentQty = parseInt(row.getAttribute('data-qty') || '0', 10);
                    if (currentQty > 0) {
                        currentQty--;
                        row.setAttribute('data-qty', currentQty);
                        calculateBill();
                    }
                });
            }

            if (btnPlus) {
                btnPlus.addEventListener('click', function () {
                    let currentQty = parseInt(row.getAttribute('data-qty') || '0', 10);
                    if (currentQty < max) {
                        currentQty++;
                        row.setAttribute('data-qty', currentQty);
                        calculateBill();
                    }
                });
            }
        });

        // 1. Teman: Klaim Sudah Bayar
        if (btnClaim) {
            btnClaim.addEventListener('click', function () {
                btnClaim.setAttribute('data-claimed', 'true');
                statusPill.textContent = 'Status: Menunggu Konfirmasi Host';
                statusPill.className = 'mb-3 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800';

                // Disable step 1, enable step 2
                btnClaim.classList.add('opacity-50', 'pointer-events-none');
                btnClaim.disabled = true;

                btnApprove.classList.remove('opacity-50', 'pointer-events-none');
                btnApprove.disabled = false;
                btnApprove.classList.add('animate-pulse');

                btnReset.classList.remove('hidden');
            });
        }

        // 2. Host: Konfirmasi Penerimaan Dana
        if (btnApprove) {
            btnApprove.addEventListener('click', function () {
                btnApprove.classList.remove('animate-pulse');
                btnApprove.classList.add('opacity-50', 'pointer-events-none');
                btnApprove.disabled = true;

                statusPill.textContent = 'Status: Lunas Terverifikasi';
                statusPill.className = 'mb-3 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800';

                if (stamp) {
                    stamp.classList.remove('hidden');
                }
            });
        }

        // Reset
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                if (stamp) stamp.classList.add('hidden');

                btnClaim.removeAttribute('data-claimed');
                statusPill.textContent = 'Status: Belum Dibayar';
                statusPill.className = 'mb-3 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-zinc-200 text-zinc-700';

                btnClaim.classList.remove('opacity-50', 'pointer-events-none');
                btnClaim.disabled = false;

                btnApprove.classList.remove('animate-pulse');
                btnApprove.classList.add('opacity-50', 'pointer-events-none');
                btnApprove.disabled = true;

                btnReset.classList.add('hidden');
            });
        }

        // Initial run
        calculateBill();
    });
</script>
@endpush
