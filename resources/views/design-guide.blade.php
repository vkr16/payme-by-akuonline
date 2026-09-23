@extends('layouts.app')

@section('title', 'PayMe - UI Design System & Component Playground')

@section('content')
<div class="space-y-8 max-w-4xl mx-auto">

    <!-- Page Title & Scope -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-zinc-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 tracking-tight">Design System & UI Components</h1>
            <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                Standar antarmuka PayMe: Solid surface, kontras tajam, mobile-first touch targets, dan tanpa gimmick.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="togglePaidStateBtn" class="touch-target inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white border border-zinc-300 hover:bg-zinc-50 shadow-2xs text-zinc-700 transition-colors">
                <i class="fa-light fa-stamp text-emerald-600"></i>
                <span id="stampToggleLabel">Simulasikan Status: LUNAS</span>
            </button>
        </div>
    </div>

    <!-- MODULE PREVIEW: SPLIT BILL CARD (REAL-WORLD FINTECH UTILITY) -->
    <div class="card-solid rounded-xl overflow-hidden relative" id="billCardContainer">

        <!-- LUNAS RUBBER STAMP OVERLAY (Controlled by toggle) -->
        <div id="lunasStampOverlay" class="hidden absolute top-8 right-6 sm:right-12 z-20 pointer-events-none">
            <div class="stamp-lunas stamp-lunas-animate px-6 py-2.5 rounded-xl border-[3.5px] border-emerald-800 text-emerald-800">
                <div class="flex flex-col items-center leading-none">
                    <span class="text-2xl sm:text-3xl font-black tracking-widest uppercase">LUNAS</span>
                    <span class="text-[9px] font-bold tracking-wider uppercase mt-0.5 opacity-90">Terbayar Penuh</span>
                </div>
            </div>
        </div>

        <!-- Card Header -->
        <div class="p-4 sm:p-5 border-b border-zinc-200 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <span class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider block">Patungan Pesanan</span>
                    <h2 class="text-lg font-bold text-zinc-900 tracking-tight">Makan Siang Sederhana Tim</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-zinc-100 text-zinc-700 border border-zinc-200">
                        <i class="fa-light fa-user text-zinc-400 text-[10px]"></i>
                        <span>Ditalangi Fikri</span>
                    </span>
                    <span id="badgeStatusHeader" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                        <i class="fa-light fa-clock text-amber-600 text-[10px]"></i>
                        <span>Belum Lunas</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Progress Bar Pengumpulan Dana (Fund Tracker) -->
        <div class="px-4 py-3 sm:px-5 sm:py-3.5 bg-zinc-50/70 border-b border-zinc-200">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="font-semibold text-zinc-700 flex items-center gap-1.5">
                    <i class="fa-light fa-chart-pie text-emerald-700 text-[11px]"></i>
                    <span>Progress Terkumpul</span>
                </span>
                <span class="font-bold text-zinc-900 tabular-nums" id="progressPercentageText">
                    Rp 150.000 / Rp 200.000 (75%)
                </span>
            </div>
            <div class="w-full h-2 rounded-full bg-zinc-200 overflow-hidden">
                <div class="h-full rounded-full bg-emerald-700 transition-all duration-300" id="progressBarFill" style="width: 75%;"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-zinc-500 mt-1.5">
                <span>2 dari 4 orang sudah bayar</span>
                <span>Sisa kekurangan: <strong class="text-zinc-800 tabular-nums">Rp 50.000</strong></span>
            </div>
        </div>

        <!-- Order Items List (High Density & Tactile Stepper) -->
        <div class="p-4 sm:p-5 space-y-3 bg-white">
            <div class="flex items-center justify-between text-xs font-semibold text-zinc-500 uppercase tracking-wider pb-1">
                <span>Rincian Item Pesanan</span>
                <span>Subtotal Porsi</span>
            </div>

            <!-- Item 1: Interactive Stepper -->
            <div class="p-3.5 rounded-lg border border-zinc-200 bg-zinc-50/50 hover:bg-zinc-50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex-grow">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-zinc-900">Ayam Geprek Sambal Bawang</span>
                        <span class="text-[11px] text-zinc-500 bg-zinc-200/60 px-1.5 py-0.2 rounded font-medium">Level 3</span>
                    </div>
                    <span class="text-xs text-zinc-500 tabular-nums">@ Rp 25.000</span>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-4">
                    <!-- Tactile Stepper Control -->
                    <div class="inline-flex items-center rounded-lg border border-zinc-300 bg-white shadow-2xs">
                        <button type="button" class="touch-target w-9 h-9 flex items-center justify-center text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 active:bg-zinc-200 rounded-l-lg transition-colors font-bold" id="btnDecItem">
                            <i class="fa-light fa-minus text-[10px]"></i>
                        </button>
                        <span class="w-10 text-center text-sm font-bold text-zinc-900 tabular-nums select-none" id="qtyItemDisplay">2</span>
                        <button type="button" class="touch-target w-9 h-9 flex items-center justify-center text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 active:bg-zinc-200 rounded-r-lg transition-colors font-bold" id="btnIncItem">
                            <i class="fa-light fa-plus text-[10px]"></i>
                        </button>
                    </div>

                    <span class="text-sm font-bold text-zinc-900 tabular-nums w-24 text-right" id="itemSubtotalDisplay">
                        Rp 50.000
                    </span>
                </div>
            </div>

            <!-- Item 2: Static Example -->
            <div class="p-3.5 rounded-lg border border-zinc-200 bg-zinc-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex-grow">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-zinc-900">Es Teh Manis Jumbo</span>
                        <span class="text-[11px] text-zinc-500 bg-zinc-200/60 px-1.5 py-0.2 rounded font-medium">Less Sugar</span>
                    </div>
                    <span class="text-xs text-zinc-500 tabular-nums">1x @ Rp 6.000</span>
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold text-zinc-900 tabular-nums">Rp 6.000</span>
                </div>
            </div>
        </div>

        <!-- Calculation & Summary Box -->
        <div class="p-4 sm:p-5 bg-zinc-50 border-t border-zinc-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-end">
                <!-- Share Breakdown -->
                <div class="space-y-1.5 text-xs text-zinc-600 tabular-nums">
                    <div class="flex justify-between py-0.5">
                        <span>Porsi Makanan:</span>
                        <span class="font-medium text-zinc-900" id="summaryPortionDisplay">Rp 56.000</span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span>Ongkir Proporsional:</span>
                        <span class="font-medium text-zinc-900">Rp 4.000</span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span>Biaya Layanan & Pembulatan:</span>
                        <span class="font-medium text-zinc-900">Rp 1.000</span>
                    </div>
                    <div class="flex justify-between py-0.5 text-emerald-700">
                        <span>Diskon Promo Aplikasi:</span>
                        <span class="font-medium">-Rp 10.000</span>
                    </div>
                </div>

                <!-- Final Amount & Action (Floating Glass Surface) -->
                <div class="glass-surface p-4 rounded-xl border border-zinc-200/90 flex flex-col justify-between">
                    <div class="flex items-baseline justify-between mb-3">
                        <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Harus Dibayar</span>
                        <span class="text-2xl font-black text-zinc-950 tabular-nums" id="finalTotalPayableDisplay">
                            Rp 51.000
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" class="touch-target flex-1 px-4 py-2.5 rounded-lg btn-primary font-semibold text-xs inline-flex items-center justify-center gap-1.5 transition-all">
                            <i class="fa-light fa-qrcode text-xs"></i>
                            <span>Bayar via QRIS</span>
                        </button>
                        <button type="button" class="touch-target px-3.5 py-2.5 rounded-lg bg-white border border-zinc-300 hover:bg-zinc-50 text-zinc-700 font-semibold text-xs shadow-2xs transition-colors inline-flex items-center justify-center" title="Salin Rincian">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODULE PREVIEW: INSTANT DYNAMIC QRIS (PRECISION FINTECH UI) -->
    <div class="card-solid rounded-xl p-5 sm:p-6 space-y-5">
        <div class="border-b border-zinc-200 pb-3">
            <h2 class="text-base font-bold text-zinc-900">Instant Dynamic QRIS Generator</h2>
            <p class="text-xs text-zinc-500 mt-0.5">Mengubah QRIS statis toko/pengguna menjadi QRIS dinamis ber-nominal otomatis.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            <!-- Left: Inputs -->
            <div class="space-y-4">
                <!-- Nominal Input -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-zinc-700">Nominal Transfer (Rp) <span class="text-rose-500">*</span></label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 font-bold text-zinc-400 text-sm pointer-events-none">Rp</span>
                        <input type="text" id="demoAmountInput" value="50.000" class="touch-target w-full pl-10 pr-3 py-2 text-base font-bold text-zinc-900 bg-white border border-zinc-300 rounded-lg focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 tabular-nums">
                    </div>
                </div>

                <!-- Preset Chips -->
                <div class="space-y-1.5">
                    <span class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider block">Pilih Cepat:</span>
                    <div class="flex flex-wrap gap-1.5" id="presetChipContainer">
                        <button type="button" class="preset-chip px-2.5 py-1 rounded-md text-xs font-medium bg-zinc-100 hover:bg-zinc-200 text-zinc-800 border border-zinc-200" data-val="10.000">10rb</button>
                        <button type="button" class="preset-chip px-2.5 py-1 rounded-md text-xs font-medium bg-zinc-100 hover:bg-zinc-200 text-zinc-800 border border-zinc-200" data-val="20.000">20rb</button>
                        <button type="button" class="preset-chip px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300" data-val="50.000">50rb</button>
                        <button type="button" class="preset-chip px-2.5 py-1 rounded-md text-xs font-medium bg-zinc-100 hover:bg-zinc-200 text-zinc-800 border border-zinc-200" data-val="100.000">100rb</button>
                        <button type="button" class="preset-chip px-2.5 py-1 rounded-md text-xs font-medium bg-zinc-100 hover:bg-zinc-200 text-zinc-800 border border-zinc-200" data-val="150.000">150rb</button>
                    </div>
                </div>

                <!-- Dropzone (Tactile & Clean) -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-zinc-700">QRIS Statis Sumber</label>
                    <div class="p-4 rounded-lg border border-dashed border-zinc-300 hover:border-zinc-400 bg-zinc-50/50 hover:bg-zinc-50 text-center cursor-pointer transition-colors">
                        <i class="fa-light fa-cloud-arrow-up text-zinc-400 text-lg mb-1 block"></i>
                        <span class="text-xs font-semibold text-zinc-800 block">Pilih file QRIS atau Drag & Drop</span>
                        <span class="text-[11px] text-zinc-400 block mt-0.5">Bisa langsung paste (<strong class="text-zinc-600">Ctrl+V</strong>)</span>
                    </div>
                </div>
            </div>

            <!-- Right: Simulated Dynamic QR Output -->
            <div class="bg-zinc-50 p-5 rounded-xl border border-zinc-200 flex flex-col items-center text-center space-y-3">
                <div class="w-full pb-2 border-b border-zinc-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-zinc-800 flex items-center gap-1.5">
                        <i class="fa-light fa-store text-emerald-600 text-xs"></i>
                        <span>WARUNG MAKAN SEDERHANA</span>
                    </span>
                    <span class="text-[10px] font-mono text-zinc-400">JAKARTA</span>
                </div>

                <!-- QR Display Container -->
                <div class="bg-white p-3 rounded-lg border border-zinc-300 shadow-2xs">
                    <div class="w-44 h-44 bg-zinc-900 rounded-sm flex items-center justify-center p-2 relative overflow-hidden">
                        <!-- Stylized QR Representation -->
                        <div class="w-full h-full bg-white p-2 rounded-xs flex flex-col items-center justify-center text-zinc-900">
                            <i class="fa-light fa-qrcode text-7xl text-zinc-900"></i>
                            <span class="text-[9px] font-mono font-bold tracking-tight text-zinc-600 mt-1">EMVCo DYNAMIC</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-0.5">
                    <span class="text-[11px] text-zinc-400 block font-medium">Nominal Terkunci Otomatis</span>
                    <span class="text-xl font-black text-zinc-900 tabular-nums" id="qrNominalDisplay">Rp 50.000</span>
                </div>

                <div class="flex gap-2 w-full pt-1">
                    <button type="button" class="touch-target flex-1 px-3 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white font-semibold text-xs transition-colors inline-flex items-center justify-center gap-1.5">
                        <i class="fa-light fa-download text-[11px]"></i>
                        <span>Simpan Gambar</span>
                    </button>
                    <button type="button" class="touch-target flex-1 px-3 py-2 rounded-lg bg-white border border-zinc-300 hover:bg-zinc-50 text-zinc-800 font-semibold text-xs shadow-2xs transition-colors inline-flex items-center justify-center gap-1.5">
                        <i class="fa-light fa-link text-[11px]"></i>
                        <span>Salin Payload</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- UI COMPONENT ATOMS: BUTTONS, STATUS, & INPUTS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Button System -->
        <div class="card-solid rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-bold text-zinc-900 border-b border-zinc-200 pb-2">Hierarki Tombol (Buttons)</h3>
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-zinc-500">Primary (Flat Emerald 800/700):</span>
                    <button type="button" class="touch-target px-4 py-2 rounded-lg btn-primary font-semibold text-xs transition-all">
                        Simpan & Buat Patungan
                    </button>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-zinc-500">Secondary (Batal/Opsi):</span>
                    <button type="button" class="touch-target px-4 py-2 rounded-lg bg-white border border-zinc-300 hover:bg-zinc-50 text-zinc-800 font-semibold text-xs shadow-2xs transition-colors">
                        Kembali ke Form
                    </button>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-zinc-500">Subtle / Action Kecil:</span>
                    <button type="button" class="touch-target px-3 py-1.5 rounded-lg bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold text-xs transition-colors">
                        <i class="fa-light fa-plus text-[10px] mr-1"></i> Tambah Item
                    </button>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-zinc-500">Destructive (Hapus):</span>
                    <button type="button" class="touch-target px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-semibold text-xs transition-colors">
                        <i class="fa-regular fa-trash-can text-[10px] mr-1"></i> Hapus Tagihan
                    </button>
                </div>
            </div>
        </div>

        <!-- Semantic Status Badges -->
        <div class="card-solid rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-bold text-zinc-900 border-b border-zinc-200 pb-2">Status Badges (High Contrast)</h3>
            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between p-2 rounded-md bg-zinc-50">
                    <span class="text-zinc-600">Sudah Lunas Penuh:</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-semibold bg-emerald-100 text-emerald-900 border border-emerald-300">
                        <i class="fa-light fa-circle-check text-emerald-700 text-[11px]"></i> Lunas (100%)
                    </span>
                </div>

                <div class="flex items-center justify-between p-2 rounded-md bg-zinc-50">
                    <span class="text-zinc-600">Sebagian Terbayar:</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-semibold bg-amber-100 text-amber-900 border border-amber-300">
                        <i class="fa-light fa-clock text-amber-700 text-[11px]"></i> Sisa Rp 25.000
                    </span>
                </div>

                <div class="flex items-center justify-between p-2 rounded-md bg-zinc-50">
                    <span class="text-zinc-600">Batal / Expired:</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-semibold bg-rose-100 text-rose-900 border border-rose-300">
                        <i class="fa-light fa-ban text-rose-700 text-[11px]"></i> Dibatalkan
                    </span>
                </div>

                <div class="flex items-center justify-between p-2 rounded-md bg-zinc-50">
                    <span class="text-zinc-600">Format Nilai Tabular:</span>
                    <span class="font-bold text-zinc-900 tabular-nums">Rp 1.450.000</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MULTI-BANK REKENING & PAYMENT ACTIVITY FEED -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">

        <!-- Multi-Bank Cards with 1-Click Copy -->
        <div class="card-solid rounded-xl p-5 space-y-4">
            <div class="border-b border-zinc-200 pb-3">
                <h3 class="text-sm font-bold text-zinc-900">Rekening Bank & E-Wallet</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Alternatif pembayaran transfer dengan 1-click salin no. rekening.</p>
            </div>

            <div class="space-y-3">
                <!-- Bank 1: BCA -->
                <div class="p-3.5 rounded-lg border border-zinc-200 bg-zinc-50/60 hover:bg-zinc-50 transition-colors flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center font-black text-xs flex-shrink-0">
                            BCA
                        </div>
                        <div class="min-w-0">
                            <span class="text-sm font-extrabold text-zinc-900 tabular-nums block tracking-tight">5271829011</span>
                            <span class="text-xs text-zinc-500 truncate block">a.n. Fikri M (Utama)</span>
                        </div>
                    </div>
                    <button type="button" class="copy-btn touch-target px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-zinc-300 hover:bg-zinc-100 text-zinc-700 shadow-2xs inline-flex items-center gap-1.5 active:scale-95 transition-all flex-shrink-0" data-copy="5271829011" data-label="No. Rek BCA">
                        <i class="fa-regular fa-copy text-[11px]"></i>
                        <span>Salin</span>
                    </button>
                </div>

                <!-- Bank 2: Mandiri -->
                <div class="p-3.5 rounded-lg border border-zinc-200 bg-zinc-50/60 hover:bg-zinc-50 transition-colors flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 flex items-center justify-center font-bold text-xs flex-shrink-0">
                            MDR
                        </div>
                        <div class="min-w-0">
                            <span class="text-sm font-extrabold text-zinc-900 tabular-nums block tracking-tight">1370019283019</span>
                            <span class="text-xs text-zinc-500 truncate block">a.n. Fikri M</span>
                        </div>
                    </div>
                    <button type="button" class="copy-btn touch-target px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-zinc-300 hover:bg-zinc-100 text-zinc-700 shadow-2xs inline-flex items-center gap-1.5 active:scale-95 transition-all flex-shrink-0" data-copy="1370019283019" data-label="No. Rek Mandiri">
                        <i class="fa-regular fa-copy text-[11px]"></i>
                        <span>Salin</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Activity Feed (Siapa yang Sudah Bayar) -->
        <div class="card-solid rounded-xl p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900">Riwayat Pembayaran</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Partisipan yang telah konfirmasi bayar.</p>
                </div>
                <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">2 Konfirmasi</span>
            </div>

            <div class="space-y-3">
                <!-- Activity 1 -->
                <div class="p-3.5 rounded-lg border border-zinc-200 bg-zinc-50/60 flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-emerald-800 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                            BS
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-zinc-900 block truncate">Budi Santoso</span>
                            <span class="text-[11px] text-zinc-500 block truncate">2x Ayam Geprek, 1x Es Teh</span>
                            <span class="text-[10px] text-zinc-400 mt-0.5 block">12 menit lalu &bull; via QRIS</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="text-xs font-black text-emerald-800 tabular-nums block">Rp 51.000</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-100/70 px-1.5 py-0.2 rounded mt-1">
                            <i class="fa-light fa-circle-check text-[9px]"></i> Lunas
                        </span>
                    </div>
                </div>

                <!-- Activity 2 -->
                <div class="p-3.5 rounded-lg border border-zinc-200 bg-zinc-50/60 flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-zinc-700 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                            SR
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-zinc-900 block truncate">Siti Rahma</span>
                            <span class="text-[11px] text-zinc-500 block truncate">1x Es Teh, Tahu & Tempe</span>
                            <span class="text-[10px] text-zinc-400 mt-0.5 block">35 menit lalu &bull; via BCA</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="text-xs font-black text-emerald-800 tabular-nums block">Rp 24.000</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-100/70 px-1.5 py-0.2 rounded mt-1">
                            <i class="fa-light fa-circle-check text-[9px]"></i> Lunas
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- TOAST NOTIFICATION SNACKBAR -->
    <div id="copyToast" class="fixed bottom-5 right-5 z-50 transform translate-y-12 opacity-0 pointer-events-none transition-all duration-200 flex items-center gap-2.5 px-4 py-3 rounded-lg bg-zinc-900 text-white text-xs font-medium shadow-xl border border-zinc-700">
        <i class="fa-light fa-circle-check text-emerald-400 text-sm"></i>
        <span id="copyToastMessage">Nomor rekening berhasil disalin!</span>
    </div>

    <!-- MODAL DIALOGS SHOWCASE -->
    <div class="card-solid rounded-xl p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-200 pb-3">
            <div>
                <h3 class="text-sm font-bold text-zinc-900">Modal Dialogs & Sheets</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Komponen dialog mengambang dengan backdrop blur halus, sudut terstruktur, dan ramah mobile.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" id="btnOpenClaimModal" class="touch-target px-3.5 py-2 rounded-lg btn-primary font-semibold text-xs inline-flex items-center gap-1.5 transition-all">
                    <i class="fa-light fa-receipt text-xs"></i>
                    <span>Buka Modal: Konfirmasi Bayar</span>
                </button>
            </div>
        </div>

        <p class="text-xs text-zinc-500">
            Klik tombol di atas untuk menguji modal: backdrop gelap transparan dengan blur halus (<code class="bg-zinc-100 px-1 py-0.5 rounded text-zinc-700">backdrop-blur-xs</code>), kartu dialog terstruktur <code class="bg-zinc-100 px-1 py-0.5 rounded text-zinc-700">rounded-xl</code>, serta tombol aksi flat emerald-800 yang tegas. Mendukung tombol <kbd class="px-1.5 py-0.5 rounded bg-zinc-200 text-zinc-700 font-mono text-[10px]">ESC</kbd> dan klik di luar untuk menutup.
        </p>
    </div>

    <!-- SAMPLE MODAL: KONFIRMASI PEMBAYARAN -->
    <div id="sampleClaimModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/40 backdrop-blur-xs transition-opacity duration-200 hidden opacity-0" aria-modal="true" role="dialog">
        <!-- Modal Dialog Box -->
        <div class="card-solid rounded-xl max-w-md w-full overflow-hidden shadow-xl border border-zinc-200 transform transition-transform duration-200 scale-95" id="sampleClaimDialog">
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-zinc-200 bg-white flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-zinc-900 tracking-tight">Konfirmasi Pembayaran</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Catat pembayaran patungan Anda</p>
                </div>
                <button type="button" id="btnCloseModalX" class="touch-target w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                    <i class="fa-light fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-5 space-y-4 bg-white">
                <!-- Payer Name -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-zinc-700">Nama Anda <span class="text-rose-500">*</span></label>
                    <input type="text" id="modalPayerName" value="Budi Santoso" class="touch-target w-full px-3 py-2 text-sm text-zinc-900 bg-white border border-zinc-300 rounded-lg focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600" placeholder="Masukkan nama Anda">
                </div>

                <!-- Payment Method Selector -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-zinc-700">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex flex-col items-center p-2.5 rounded-lg border border-emerald-600 bg-emerald-50/50 cursor-pointer text-center">
                            <input type="radio" name="modal_method" value="qris" checked class="sr-only">
                            <i class="fa-light fa-qrcode text-emerald-700 text-sm mb-1"></i>
                            <span class="text-xs font-bold text-emerald-900">QRIS</span>
                        </label>
                        <label class="flex flex-col items-center p-2.5 rounded-lg border border-zinc-200 hover:bg-zinc-50 cursor-pointer text-center">
                            <input type="radio" name="modal_method" value="bank" class="sr-only">
                            <i class="fa-light fa-building-columns text-zinc-500 text-sm mb-1"></i>
                            <span class="text-xs font-semibold text-zinc-700">Bank</span>
                        </label>
                        <label class="flex flex-col items-center p-2.5 rounded-lg border border-zinc-200 hover:bg-zinc-50 cursor-pointer text-center">
                            <input type="radio" name="modal_method" value="cash" class="sr-only">
                            <i class="fa-light fa-money-bill-1 text-zinc-500 text-sm mb-1"></i>
                            <span class="text-xs font-semibold text-zinc-700">Tunai</span>
                        </label>
                    </div>
                </div>

                <!-- Items & Nominal Summary -->
                <div class="p-3.5 rounded-lg bg-zinc-50 border border-zinc-200 space-y-1.5 text-xs">
                    <div class="flex justify-between text-zinc-600">
                        <span>Item yang Diklaim:</span>
                        <span class="font-semibold text-zinc-900">2x Ayam Geprek, 1x Es Teh</span>
                    </div>
                    <div class="flex justify-between text-zinc-600">
                        <span>Biaya Bersih & Diskon:</span>
                        <span class="font-medium text-emerald-700">-Rp 5.000</span>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-zinc-200 text-sm font-bold text-zinc-900">
                        <span>Total yang Dibayar:</span>
                        <span class="text-emerald-800 tabular-nums">Rp 51.000</span>
                    </div>
                </div>

                <!-- Round up check -->
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs text-zinc-600">Bulatkan sisa uang kecil ke atas (+Rp 4.000)</span>
                </label>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-5 border-t border-zinc-200 bg-zinc-50 flex items-center justify-end gap-2.5">
                <button type="button" id="btnCloseModalBtn" class="touch-target px-4 py-2 rounded-lg bg-white border border-zinc-300 hover:bg-zinc-100 text-zinc-700 font-semibold text-xs shadow-2xs transition-colors">
                    Batal
                </button>
                <button type="button" id="btnConfirmClaimSubmit" class="touch-target px-4 py-2 rounded-lg btn-primary font-semibold text-xs transition-all">
                    Konfirmasi Sudah Bayar
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // 1. Interactive Stepper Demo (Ayam Geprek item)
    let itemQty = 2;
    const baseItemPrice = 25000;
    const extraItems = 6000; // Es teh
    const netFees = 4000 + 1000 - 10000; // -5000

    const qtyDisplay = document.getElementById('qtyItemDisplay');
    const itemSubDisplay = document.getElementById('itemSubtotalDisplay');
    const summaryPortionDisplay = document.getElementById('summaryPortionDisplay');
    const finalTotalDisplay = document.getElementById('finalTotalPayableDisplay');

    document.getElementById('btnIncItem').addEventListener('click', () => {
        itemQty++;
        recalc();
    });

    document.getElementById('btnDecItem').addEventListener('click', () => {
        if (itemQty > 1) {
            itemQty--;
            recalc();
        }
    });

    function recalc() {
        qtyDisplay.textContent = itemQty;
        const itemSubtotal = itemQty * baseItemPrice;
        itemSubDisplay.textContent = 'Rp ' + itemSubtotal.toLocaleString('id-ID');

        const totalPortion = itemSubtotal + extraItems;
        summaryPortionDisplay.textContent = 'Rp ' + totalPortion.toLocaleString('id-ID');

        const finalTotal = Math.max(0, totalPortion + netFees);
        finalTotalDisplay.textContent = 'Rp ' + finalTotal.toLocaleString('id-ID');
    }

    // 2. Toggle LUNAS Stamp Demo
    let isPaid = false;
    const togglePaidBtn = document.getElementById('togglePaidStateBtn');
    const stampOverlay = document.getElementById('lunasStampOverlay');
    const badgeStatus = document.getElementById('badgeStatusHeader');
    const toggleLabel = document.getElementById('stampToggleLabel');

    togglePaidBtn.addEventListener('click', () => {
        isPaid = !isPaid;
        if (isPaid) {
            stampOverlay.classList.remove('hidden');
            badgeStatus.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-900 border border-emerald-300';
            badgeStatus.innerHTML = '<i class="fa-light fa-circle-check text-emerald-700 text-[10px]"></i><span>Lunas Penuh</span>';
            toggleLabel.textContent = 'Kembalikan ke: Belum Lunas';
        } else {
            stampOverlay.classList.add('hidden');
            badgeStatus.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200';
            badgeStatus.innerHTML = '<i class="fa-light fa-clock text-amber-600 text-[10px]"></i><span>Belum Lunas</span>';
            toggleLabel.textContent = 'Simulasikan Status: LUNAS';
        }
    });

    // 3. Preset chips for dynamic QR
    const amountInput = document.getElementById('demoAmountInput');
    const qrDisplay = document.getElementById('qrNominalDisplay');
    const chips = document.querySelectorAll('.preset-chip');

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => {
                c.className = 'preset-chip px-2.5 py-1 rounded-md text-xs font-medium bg-zinc-100 hover:bg-zinc-200 text-zinc-800 border border-zinc-200';
            });
            chip.className = 'preset-chip px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300';

            const val = chip.getAttribute('data-val');
            amountInput.value = val;
            qrDisplay.textContent = 'Rp ' + val;
        });
    });

    amountInput.addEventListener('input', (e) => {
        qrDisplay.textContent = 'Rp ' + (e.target.value || '0');
    });

    // 4. Sample Modal Control (Smooth fade & scale)
    const modalEl = document.getElementById('sampleClaimModal');
    const modalDialog = document.getElementById('sampleClaimDialog');
    const btnOpenModal = document.getElementById('btnOpenClaimModal');
    const btnCloseX = document.getElementById('btnCloseModalX');
    const btnCloseBtn = document.getElementById('btnCloseModalBtn');
    const btnConfirmSubmit = document.getElementById('btnConfirmClaimSubmit');

    function openModal() {
        modalEl.classList.remove('hidden');
        setTimeout(() => {
            modalEl.classList.remove('opacity-0');
            modalDialog.classList.remove('scale-95');
            modalDialog.classList.add('scale-100');
        }, 15);
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modalEl.classList.add('opacity-0');
        modalDialog.classList.remove('scale-100');
        modalDialog.classList.add('scale-95');
        setTimeout(() => {
            modalEl.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    }

    btnOpenModal.addEventListener('click', openModal);
    btnCloseX.addEventListener('click', closeModal);
    btnCloseBtn.addEventListener('click', closeModal);
    btnConfirmSubmit.addEventListener('click', () => {
        alert('Simulasi: Konfirmasi pembayaran berhasil disimpan!');
        closeModal();
    });

    // Close on backdrop click
    modalEl.addEventListener('click', (e) => {
        if (e.target === modalEl) {
            closeModal();
        }
    });

    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modalEl.classList.contains('hidden')) {
            closeModal();
        }
    });

    // 5. One-Click Copy Buttons & Toast Feedback
    const toast = document.getElementById('copyToast');
    const toastMsg = document.getElementById('copyToastMessage');
    let toastTimer = null;

    function showToast(message) {
        if (toastTimer) clearTimeout(toastTimer);
        toastMsg.textContent = message;
        toast.classList.remove('translate-y-12', 'opacity-0', 'pointer-events-none');
        toast.classList.add('translate-y-0', 'opacity-100');

        toastTimer = setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-12', 'opacity-0', 'pointer-events-none');
        }, 2500);
    }

    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-copy');
            const label = btn.getAttribute('data-label') || 'Teks';
            navigator.clipboard.writeText(val).catch(() => {});

            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-light fa-check text-emerald-600 text-[11px]"></i><span class="text-emerald-700">Tersalin!</span>';
            showToast(`${label} (${val}) berhasil disalin!`);

            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1800);
        });
    });
</script>
@endpush
