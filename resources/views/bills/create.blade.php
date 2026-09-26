@extends('layouts.app')

@section('title', 'Buat Patungan Baru - PayMe')
@section('meta_description', 'Buat tagihan patungan baru, scan struk dengan AI Vision atau input pesanan manual dengan pembagian proporsional yang adil.')

@section('styles')
<style>
    /* Tactile stepper button styles */
    .stepper-btn {
        touch-action: manipulation;
        user-select: none;
    }
    .stepper-btn:active {
        transform: scale(0.92);
    }
</style>
@endsection

@section('content')
<div class="max-w-3xl mx-auto pb-28">

    <!-- Top Navigation Breadcrumb -->
    <div class="mb-4">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-emerald-800 transition-colors">
            <i class="fa-light fa-arrow-left text-xs"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Main Form -->
    <form action="{{ route('bills.store') }}" method="POST" enctype="multipart/form-data" id="createBillForm" class="space-y-5">
        @csrf

        <!-- ==========================================
             1. INFORMASI PATUNGAN (Header Ringkas)
             ========================================== -->
        <div class="card-solid rounded-2xl p-5 sm:p-6 bg-white border border-zinc-200/90 shadow-sm space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200/60 flex items-center justify-center text-xs font-bold shadow-2xs">
                        1
                    </span>
                    <h2 class="text-sm sm:text-base font-bold text-zinc-900">Informasi Patungan</h2>
                </div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-zinc-100 text-zinc-700 border border-zinc-200/60">
                    <i class="fa-light fa-user-circle text-emerald-700"></i>
                    <span>Host: <strong class="text-zinc-900">{{ $user->name }}</strong></span>
                </div>
            </div>

            <div>
                <label for="title" class="block text-xs font-bold text-zinc-700 mb-1.5">
                    Nama Acara / Pesanan <span class="text-rose-500">*</span>
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-zinc-400 pointer-events-none text-xs">
                        <i class="fa-light fa-utensils"></i>
                    </span>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Makan Siang Bebek Sinjay, Kopi Sore Tim" class="touch-target w-full pl-9 pr-3.5 py-2.5 text-xs sm:text-sm bg-white border @error('title') border-rose-400 focus:border-rose-500 @else border-zinc-300 focus:border-emerald-700 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-700/10 text-zinc-900 placeholder:text-zinc-400 transition-colors font-medium">
                </div>
                @error('title')
                    <p class="text-[11px] text-rose-600 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- ==========================================
             2. RINCIAN PESANAN & STRUK (Core Items)
             ========================================== -->
        <div class="card-solid rounded-2xl p-5 sm:p-6 bg-white border border-zinc-200/90 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200/60 flex items-center justify-center text-xs font-bold shadow-2xs">
                    2
                </span>
                <h2 class="text-sm sm:text-base font-bold text-zinc-900">Daftar Menu & Biaya</h2>
            </div>

            <!-- AI OCR Dropzone Area -->
            <div id="aiSection" class="p-4 rounded-xl bg-emerald-50/40 border border-emerald-200/70 space-y-3">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                        <i class="fa-light fa-wand-magic-sparkles text-emerald-700"></i>
                        <span>Scan Struk (AI)</span>
                    </span>

                    <!-- Price format selector & guide button -->
                    <div class="flex items-center gap-2 text-[11px] text-zinc-600 flex-wrap">
                        <div class="flex items-center gap-1">
                            <label for="receiptPriceType" class="text-zinc-500 font-medium">Format Struk:</label>
                            <select id="receiptPriceType" name="receipt_price_type" class="bg-white border border-zinc-300 rounded-lg px-2.5 py-1 text-xs text-zinc-800 focus:outline-none focus:border-emerald-700 font-medium shadow-2xs cursor-pointer">
                                <option value="unit_price" selected>Harga Satuan</option>
                                <option value="total_price">Harga Total</option>
                            </select>
                        </div>
                        <button type="button" id="btnToggleFormatInfo" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-800 hover:text-emerald-900 bg-white hover:bg-emerald-50 border border-emerald-300/80 px-2 py-1 rounded-lg shadow-2xs cursor-pointer transition-colors" title="Lihat panduan memilih format">
                            <i class="fa-light fa-circle-question text-emerald-700"></i>
                            <span>Panduan Format</span>
                        </button>
                    </div>
                </div>

                <!-- Dynamic One-Liner Format Hint -->
                <div id="activeFormatHint" class="text-[11px] text-emerald-900 bg-emerald-100/60 border border-emerald-200/70 px-3 py-1.5 rounded-lg flex items-center gap-2">
                    <i class="fa-light fa-circle-info text-emerald-700 shrink-0 text-xs"></i>
                    <span id="activeFormatHintText"><strong>Format Harga Satuan:</strong> Pilih jika nominal di struk adalah harga 1 item (misal <code>2x Nasi Goreng @ 25.000</code>, tertulis 25.000). AI langsung mencatat Rp 25.000.</span>
                </div>

                <!-- Detailed Format Comparison Guide (Collapsible) -->
                <div id="formatDetailBox" class="hidden p-3.5 rounded-xl bg-white border border-emerald-200 shadow-xs space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs">
                                <i class="fa-light fa-book-open"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 text-xs">Kapan Harus Memilih Format?</h4>
                                <p class="text-[10px] text-zinc-400">Panduan agar pembagian harga patungan per orang akurat</p>
                            </div>
                        </div>
                        <button type="button" id="btnCloseFormatDetail" class="text-zinc-400 hover:text-zinc-600 p-1 text-xs cursor-pointer" title="Tutup panduan">
                            <i class="fa-light fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Card 1: Harga Satuan -->
                        <div class="p-3 rounded-xl border border-emerald-200/80 bg-emerald-50/30 space-y-2 flex flex-col justify-between">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-emerald-950 text-xs flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                        1. Harga Satuan (Default)
                                    </span>
                                    <span class="text-[9px] font-bold text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded uppercase">Umum</span>
                                </div>
                                <p class="text-[11px] text-zinc-600 leading-relaxed">
                                    <strong class="text-emerald-950">Kapan harus memilih:</strong><br>
                                    Pilih opsi ini jika kolom nominal harga di struk menampilkan <strong>harga per 1 unit / item</strong> barang, bukan jumlah total dari pesanan tersebut.
                                </p>
                            </div>

                            <div class="space-y-1 pt-1">
                                <div class="bg-white p-2 rounded-lg border border-zinc-200 text-[11px] space-y-1 font-mono text-zinc-700">
                                    <div class="text-[9px] font-sans text-zinc-400 font-semibold uppercase">Contoh Baris di Struk:</div>
                                    <div class="flex justify-between font-bold text-zinc-900">
                                        <span>2x Nasi Goreng @ 25.000</span>
                                        <span>25.000</span>
                                    </div>
                                </div>
                                <div class="text-[10px] text-emerald-800 flex items-center gap-1 pt-0.5 font-medium">
                                    <i class="fa-light fa-check text-emerald-600"></i>
                                    <span>AI mencatat harga satuan: <strong>Rp 25.000 / item</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Harga Total -->
                        <div class="p-3 rounded-xl border border-blue-200/80 bg-blue-50/30 space-y-2 flex flex-col justify-between">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-blue-950 text-xs flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                        2. Harga Total
                                    </span>
                                    <span class="text-[9px] font-bold text-blue-800 bg-blue-100 px-1.5 py-0.5 rounded uppercase">Subtotal Baris</span>
                                </div>
                                <p class="text-[11px] text-zinc-600 leading-relaxed">
                                    <strong class="text-blue-950">Kapan harus memilih:</strong><br>
                                    Pilih opsi ini jika kolom nominal harga di struk menampilkan <strong>total harga dari kuantitas tersebut</strong> (misal 2 item langsung tertulis totalnya).
                                </p>
                            </div>

                            <div class="space-y-1 pt-1">
                                <div class="bg-white p-2 rounded-lg border border-zinc-200 text-[11px] space-y-1 font-mono text-zinc-700">
                                    <div class="text-[9px] font-sans text-zinc-400 font-semibold uppercase">Contoh Baris di Struk:</div>
                                    <div class="flex justify-between font-bold text-zinc-900">
                                        <span>2x Nasi Goreng</span>
                                        <span>50.000</span>
                                    </div>
                                </div>
                                <div class="text-[10px] text-blue-800 flex items-center gap-1 pt-0.5 font-medium">
                                    <i class="fa-light fa-calculator text-blue-600"></i>
                                    <span>AI membagi: Rp 50.000 ÷ 2 = <strong>Rp 25.000 / item</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-2 rounded-lg bg-zinc-50 border border-zinc-200/80 text-[10px] text-zinc-500 flex items-start gap-1.5">
                        <i class="fa-light fa-lightbulb text-amber-500 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Tips Cepat:</strong> Cek baris item yang kuantitasnya lebih dari 1 (misal 2x atau 3x). Jika angka di ujung kolom adalah harga satuan per barang/item, pilih <em>Harga Satuan</em>. Jika angka di ujung kolom adalah total belanja untuk item tersebut, pilih <em>Harga Total</em>.</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <label for="receiptFileInput" class="w-full flex flex-col items-center justify-center p-4 border-2 border-dashed border-emerald-300 hover:border-emerald-500 rounded-xl bg-white hover:bg-emerald-50/30 cursor-pointer transition-colors text-center group">
                        <i class="fa-light fa-cloud-arrow-up text-2xl text-emerald-700 mb-1 transition-transform group-hover:-translate-y-0.5"></i>
                        <span class="text-xs font-bold text-zinc-800">Pilih / Foto Struk Belanja</span>
                        <span class="text-[10px] text-zinc-400 mt-0.5">JPG, PNG, atau Screenshot GoFood/Grab/Shopee</span>
                        <input type="file" id="receiptFileInput" name="receipt_images[]" multiple accept="image/*" class="hidden">
                    </label>
                </div>

                <!-- AI Processing Progress State -->
                <div id="aiLoadingIndicator" class="hidden p-3 rounded-xl bg-emerald-800 text-white flex items-center justify-center gap-3 shadow-sm">
                    <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                    <span class="text-xs font-semibold" id="aiLoadingText">Menganalisis gambar struk via AI Vision...</span>
                </div>

                <!-- AI Alert Message -->
                <div id="aiAlert" class="hidden p-3 rounded-xl text-xs font-medium"></div>
            </div>

            <!-- Items List Container -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-700 uppercase tracking-wider">Item Tagihan</span>
                    <button type="button" id="btnAddItem" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100/80 border border-emerald-200/60 transition-colors">
                        <i class="fa-light fa-plus text-[11px]"></i>
                        <span>Tambah Item</span>
                    </button>
                </div>

                <!-- Dynamic Item Rows -->
                <div id="itemsContainer" class="space-y-2">
                    <!-- Populated by JavaScript or Default Row -->
                </div>

                <!-- Empty items placeholder -->
                <div id="emptyItemsState" class="hidden text-center py-6 border border-dashed border-zinc-300 rounded-xl text-zinc-400 text-xs">
                    <i class="fa-light fa-cart-shopping text-xl mb-1 block text-zinc-300"></i>
                    <span>Belum ada item tagihan. Klik <strong>Tambah Item</strong> atau <strong>Pindai Struk</strong>.</span>
                </div>
            </div>

            <!-- Biaya Tambahan & Diskon (Proporsional Allocation Breakdown) -->
            <div class="pt-3 border-t border-zinc-100 space-y-3">
                <span class="text-xs font-bold text-zinc-700 uppercase tracking-wider block">Biaya Tambahan & Diskon</span>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- 1. Diskon / Promo -->
                    <div class="space-y-1">
                        <label for="inputDiscount" class="block text-[11px] font-semibold text-emerald-800">
                            Diskon / Voucher Promo
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-emerald-600 font-bold text-xs">-Rp</span>
                            <input type="number" id="inputDiscount" name="discount" min="0" step="any" value="{{ old('discount', 0) }}" placeholder="0" class="touch-target w-full pl-10 pr-3 py-2 text-xs sm:text-sm bg-emerald-50/40 border border-emerald-300 rounded-xl focus:outline-none focus:border-emerald-700 text-emerald-950 font-bold tabular-nums">
                        </div>
                    </div>

                    <!-- 2. Ongkos Kirim -->
                    <div class="space-y-1">
                        <label for="inputDeliveryFee" class="block text-[11px] font-semibold text-zinc-700">
                            Ongkos Kirim (Delivery)
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-zinc-400 text-xs">Rp</span>
                            <input type="number" id="inputDeliveryFee" name="delivery_fee" min="0" step="any" value="{{ old('delivery_fee', 0) }}" placeholder="0" class="touch-target w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-white border border-zinc-300 rounded-xl focus:outline-none focus:border-emerald-700 text-zinc-900 font-medium tabular-nums">
                        </div>
                    </div>

                    <!-- 3. Biaya Layanan / Tambahan lainnya -->
                    <div class="space-y-1">
                        <label for="inputServiceFee" class="block text-[11px] font-semibold text-zinc-700">
                            Biaya Layanan / Lainnya
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-zinc-400 text-xs">Rp</span>
                            <input type="number" id="inputServiceFee" name="service_fee" min="0" step="any" value="{{ old('service_fee', 0) }}" placeholder="0" class="touch-target w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-white border border-zinc-300 rounded-xl focus:outline-none focus:border-emerald-700 text-zinc-900 font-medium tabular-nums">
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Box -->
                <div class="p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 space-y-1.5 text-xs">
                    <div class="flex justify-between text-zinc-600">
                        <span>Subtotal Item:</span>
                        <span id="summarySubtotal" class="font-bold text-zinc-900 tabular-nums">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-zinc-600" id="rowSummaryDelivery">
                        <span>Ongkos Kirim:</span>
                        <span id="summaryDelivery" class="font-medium text-zinc-900 tabular-nums">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-zinc-600" id="rowSummaryService">
                        <span>Biaya Layanan:</span>
                        <span id="summaryService" class="font-medium text-zinc-900 tabular-nums">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-emerald-700" id="rowSummaryDiscount">
                        <span>Potongan Diskon:</span>
                        <span id="summaryDiscount" class="font-bold tabular-nums">-Rp 0</span>
                    </div>
                    <div class="pt-2 border-t border-zinc-200 flex justify-between items-center text-sm font-bold text-zinc-900">
                        <span>Grand Total Struk:</span>
                        <span id="summaryGrandTotal" class="text-base text-emerald-800 font-black tabular-nums">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             3. QRIS PENERIMA PEMBAYARAN (Database-Backed)
             ========================================== -->
        <div class="card-solid rounded-2xl p-5 sm:p-6 bg-white border border-zinc-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200/60 flex items-center justify-center text-xs font-bold shadow-2xs">
                        3
                    </span>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-zinc-900">QRIS Penerima Pembayaran</h2>
                        <p class="text-[11px] text-zinc-400">Untuk konversi QRIS dinamis ber-nominal otomatis</p>
                    </div>
                </div>
            </div>

            <!-- Choice Options: Saved / New / None -->
            <div class="space-y-3">
                @if($savedQris->count() > 0)
                    <!-- Option A: Use Saved QRIS from Database -->
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-zinc-200 hover:border-emerald-600 bg-white cursor-pointer transition-all has-[:checked]:border-emerald-800 has-[:checked]:bg-emerald-50/30">
                        <input type="radio" name="qris_choice" value="saved" checked class="mt-0.5 text-emerald-800 focus:ring-emerald-700 cursor-pointer accent-emerald-800">
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-zinc-900">Gunakan QRIS Tersimpan di Akun</span>
                                <span class="text-[10px] font-semibold text-emerald-800 bg-emerald-100/80 px-2 py-0.5 rounded-md">Database</span>
                            </div>

                            <!-- Selector dropdown if multiple saved QRIS -->
                            <select name="saved_qris_id" id="savedQrisSelect" class="w-full text-xs bg-white border border-zinc-300 rounded-lg px-3 py-2 text-zinc-800 focus:outline-none focus:border-emerald-700 font-medium">
                                @foreach($savedQris as $qris)
                                    <option value="{{ $qris->id }}" {{ $qris->is_default ? 'selected' : '' }}>
                                        {{ $qris->merchant_name ?: 'Merchant QRIS' }} ({{ $qris->merchant_city ?: 'Indonesia' }}) {{ $qris->is_default ? '— [Default]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </label>
                @endif

                <!-- Option B: Upload New QRIS -->
                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-zinc-200 hover:border-emerald-600 bg-white cursor-pointer transition-all has-[:checked]:border-emerald-800 has-[:checked]:bg-emerald-50/30">
                    <input type="radio" name="qris_choice" value="new" {{ $savedQris->count() === 0 ? 'checked' : '' }} class="mt-0.5 text-emerald-800 focus:ring-emerald-700 cursor-pointer accent-emerald-800">
                    <div class="flex-1 space-y-2">
                        <span class="text-xs font-bold text-zinc-900 block">Upload Gambar QRIS Statis Baru</span>

                        <!-- Container for new QRIS upload -->
                        <div id="newQrisContainer" class="space-y-2.5 pt-1 {{ $savedQris->count() > 0 ? 'hidden' : '' }}">
                            <div class="flex flex-col sm:flex-row items-center gap-3">
                                <label for="qrisFileInput" class="w-full flex items-center justify-center gap-2 p-3 border border-dashed border-zinc-300 hover:border-emerald-600 rounded-xl bg-zinc-50 hover:bg-emerald-50/20 cursor-pointer text-xs font-semibold text-zinc-700 transition-colors">
                                    <i class="fa-light fa-qrcode text-emerald-700 text-sm"></i>
                                    <span>Pilih File QRIS Statis</span>
                                    <input type="file" id="qrisFileInput" name="new_qris_image" accept="image/*" class="hidden">
                                </label>
                            </div>

                            <input type="hidden" id="newQrisPayload" name="new_qris_payload" value="{{ old('new_qris_payload') }}">

                            <!-- Live QR status feedback -->
                            <div id="qrisDetectStatus" class="hidden p-2.5 rounded-lg text-xs flex items-center gap-2"></div>

                            <!-- Checkbox save to user profile -->
                            <label class="flex items-center gap-2 cursor-pointer select-none pt-1">
                                <input type="checkbox" name="save_new_qris" value="1" checked class="w-4 h-4 rounded border-zinc-300 text-emerald-800 focus:ring-emerald-700 cursor-pointer accent-emerald-800">
                                <span class="text-xs text-zinc-600">Simpan QRIS ini ke profil akun saya untuk tagihan berikutnya</span>
                            </label>
                        </div>
                    </div>
                </label>

                <!-- Option C: None -->
                <label class="flex items-center gap-3 p-3 rounded-xl border border-zinc-200 hover:border-zinc-300 bg-white cursor-pointer transition-all has-[:checked]:border-zinc-400">
                    <input type="radio" name="qris_choice" value="none" class="text-emerald-800 focus:ring-emerald-700 cursor-pointer accent-emerald-800">
                    <span class="text-xs font-semibold text-zinc-600">Tanpa QRIS (Hanya Transfer Bank / E-Wallet)</span>
                </label>
            </div>
        </div>

        <!-- ==========================================
             4. REKENING BANK & E-WALLET ALTERNATIF
             ========================================== -->
        <div class="card-solid rounded-2xl p-5 sm:p-6 bg-white border border-zinc-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200/60 flex items-center justify-center text-xs font-bold shadow-2xs">
                        4
                    </span>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-zinc-900">Transfer Bank & E-Wallet</h2>
                        <p class="text-[11px] text-zinc-400">Alternatif transfer selain QRIS</p>
                    </div>
                </div>

                <!-- Enable Bank Switch -->
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" id="toggleEnableBank" name="enable_bank" value="1" {{ ($savedBanks->count() > 0 || old('enable_bank')) ? 'checked' : '' }} class="w-4 h-4 rounded border-zinc-300 text-emerald-800 focus:ring-emerald-700 cursor-pointer accent-emerald-800">
                    <span class="text-xs font-bold text-zinc-700">Aktifkan</span>
                </label>
            </div>

            <div id="bankSectionContainer" class="space-y-3 {{ ($savedBanks->count() > 0 || old('enable_bank')) ? '' : 'hidden' }}">
                <!-- Saved Banks Checklist from DB -->
                @if($savedBanks->count() > 0)
                    <div class="space-y-2">
                        <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider block">Rekening Tersimpan di Akun</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @php
                                $hasDefault = $savedBanks->contains('is_default', true);
                            @endphp
                            @foreach($savedBanks as $bank)
                                @php
                                    $shouldCheck = old('selected_bank_ids')
                                        ? in_array($bank->id, old('selected_bank_ids', []))
                                        : ($hasDefault ? $bank->is_default : true);
                                @endphp
                                <label class="flex items-start gap-2.5 p-3 rounded-xl border border-zinc-200 hover:border-emerald-600 bg-zinc-50/50 cursor-pointer transition-all has-[:checked]:border-emerald-800 has-[:checked]:bg-emerald-50/30">
                                    <input type="checkbox" name="selected_bank_ids[]" value="{{ $bank->id }}" {{ $shouldCheck ? 'checked' : '' }} class="mt-0.5 text-emerald-800 focus:ring-emerald-700 cursor-pointer accent-emerald-800">
                                    <div class="text-xs leading-tight flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-zinc-900">{{ $bank->bank_name }}</span>
                                            @if($bank->is_default)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <i class="fa-light fa-star text-[8px]"></i>
                                                    <span>Utama</span>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-zinc-600 tabular-nums font-mono font-medium mt-0.5">{{ $bank->account_number }}</div>
                                        <div class="text-[10px] text-zinc-400 mt-0.5">a.n {{ $bank->account_holder }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Dynamic New Bank Inputs -->
                <div class="space-y-2 pt-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider">Tambah Rekening Baru</span>
                        <button type="button" id="btnAddBankRow" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 inline-flex items-center gap-1">
                            <i class="fa-light fa-plus text-xs"></i>
                            <span>Tambah Baris</span>
                        </button>
                    </div>

                    <div id="newBanksList" class="space-y-2.5">
                        <!-- Dynamic new bank rows will be injected here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             5. STICKY FLOATING BOTTOM BAR (Mobile First)
             ========================================== -->
        <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-zinc-200/90 py-3 px-4 sm:px-6 shadow-lg">
            <div class="max-w-3xl mx-auto flex items-center justify-between gap-3">
                <div>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Target Patungan</span>
                    <div class="flex items-baseline gap-1.5">
                        <span id="floatingGrandTotal" class="text-lg sm:text-xl font-black text-emerald-900 tabular-nums">Rp 0</span>
                        <span id="floatingItemCount" class="text-[11px] text-zinc-500 font-medium">(0 item)</span>
                    </div>
                </div>

                <button type="submit" id="btnSubmitBill" class="touch-target px-5 sm:px-6 py-2.5 rounded-xl btn-primary font-bold text-xs sm:text-sm shadow-md inline-flex items-center gap-2 transition-all">
                    <span>Buat Link Patungan</span>
                    <i class="fa-light fa-arrow-right text-xs"></i>
                </button>
            </div>
        </div>

    </form>
</div>

<!-- Hidden Canvas for jsQR decoding -->
<canvas id="qrCanvas" class="hidden"></canvas>
@endsection

@push('scripts')
<script src="{{ asset('vendor/jsqr/jsQR.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let itemCounter = 0;
    let bankCounter = 0;

    const itemsContainer = document.getElementById('itemsContainer');
    const emptyItemsState = document.getElementById('emptyItemsState');
    const btnAddItem = document.getElementById('btnAddItem');

    const inputDiscount = document.getElementById('inputDiscount');
    const inputDeliveryFee = document.getElementById('inputDeliveryFee');
    const inputServiceFee = document.getElementById('inputServiceFee');

    const summarySubtotal = document.getElementById('summarySubtotal');
    const summaryDelivery = document.getElementById('summaryDelivery');
    const summaryService = document.getElementById('summaryService');
    const summaryDiscount = document.getElementById('summaryDiscount');
    const summaryGrandTotal = document.getElementById('summaryGrandTotal');
    const floatingGrandTotal = document.getElementById('floatingGrandTotal');
    const floatingItemCount = document.getElementById('floatingItemCount');


    // QRIS Elements
    const qrisChoiceRadios = document.querySelectorAll('input[name="qris_choice"]');
    const newQrisContainer = document.getElementById('newQrisContainer');
    const qrisFileInput = document.getElementById('qrisFileInput');
    const newQrisPayload = document.getElementById('newQrisPayload');
    const qrisDetectStatus = document.getElementById('qrisDetectStatus');

    // Bank Elements
    const toggleEnableBank = document.getElementById('toggleEnableBank');
    const bankSectionContainer = document.getElementById('bankSectionContainer');
    const btnAddBankRow = document.getElementById('btnAddBankRow');
    const newBanksList = document.getElementById('newBanksList');

    // AI Elements
    const receiptFileInput = document.getElementById('receiptFileInput');
    const receiptPriceType = document.getElementById('receiptPriceType');
    const aiLoadingIndicator = document.getElementById('aiLoadingIndicator');
    const aiLoadingText = document.getElementById('aiLoadingText');
    const aiAlert = document.getElementById('aiAlert');
    const btnToggleFormatInfo = document.getElementById('btnToggleFormatInfo');
    const btnCloseFormatDetail = document.getElementById('btnCloseFormatDetail');
    const formatDetailBox = document.getElementById('formatDetailBox');
    const activeFormatHintText = document.getElementById('activeFormatHintText');

    // Helper currency formatter
    function formatRupiah(number) {
        return 'Rp ' + (new Intl.NumberFormat('id-ID').format(Math.round(number || 0)));
    }

    // Add Item Row
    function addItemRow(name = '', qty = 1, price = 0) {
        const id = itemCounter++;
        const row = document.createElement('div');
        row.id = `item-row-${id}`;
        row.className = 'item-card p-3 rounded-xl bg-white border border-zinc-200/90 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 transition-all';
        row.innerHTML = `
            <div class="flex-1 w-full sm:w-auto">
                <input type="text" name="items[${id}][name]" value="${name}" required placeholder="Nama menu / item" class="item-name touch-target w-full px-3 py-2 text-xs sm:text-sm bg-zinc-50/70 border border-zinc-200 rounded-lg focus:outline-none focus:border-emerald-700 focus:bg-white text-zinc-900 font-medium placeholder:text-zinc-400 transition-colors">
            </div>
            <div class="flex items-center justify-between sm:justify-end gap-2 w-full sm:w-auto">
                <div class="relative flex items-center w-28 sm:w-32">
                    <span class="absolute left-2.5 text-zinc-400 text-xs">Rp</span>
                    <input type="number" name="items[${id}][price]" value="${price}" min="0" step="any" required placeholder="0" class="item-price touch-target w-full pl-7 pr-2.5 py-2 text-xs sm:text-sm bg-zinc-50/70 border border-zinc-200 rounded-lg focus:outline-none focus:border-emerald-700 focus:bg-white text-zinc-900 font-bold tabular-nums text-right transition-colors">
                </div>

                <!-- Stepper Quantity -->
                <div class="inline-flex items-center rounded-lg border border-zinc-200 bg-zinc-50 p-0.5">
                    <button type="button" class="stepper-btn btn-qty-minus w-7 h-7 flex items-center justify-center text-zinc-600 hover:text-zinc-900 hover:bg-white rounded-md text-xs font-bold transition-colors">
                        <i class="fa-light fa-minus text-[10px]"></i>
                    </button>
                    <input type="number" name="items[${id}][qty]" value="${qty}" min="1" class="item-qty w-9 text-center bg-transparent text-xs font-bold text-zinc-900 tabular-nums focus:outline-none" readonly>
                    <button type="button" class="stepper-btn btn-qty-plus w-7 h-7 flex items-center justify-center text-zinc-600 hover:text-zinc-900 hover:bg-white rounded-md text-xs font-bold transition-colors">
                        <i class="fa-light fa-plus text-[10px]"></i>
                    </button>
                </div>

                <!-- Delete Item Button -->
                <button type="button" class="btn-delete-item touch-target w-8 h-8 flex items-center justify-center text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Item">
                    <i class="fa-light fa-trash-can text-xs"></i>
                </button>
            </div>
        `;

        itemsContainer.appendChild(row);
        checkEmptyState();
        calculateTotals();

        // Event listeners for this row
        const btnMinus = row.querySelector('.btn-qty-minus');
        const btnPlus = row.querySelector('.btn-qty-plus');
        const qtyInput = row.querySelector('.item-qty');
        const priceInput = row.querySelector('.item-price');
        const btnDelete = row.querySelector('.btn-delete-item');

        btnMinus.addEventListener('click', function () {
            let current = parseInt(qtyInput.value) || 1;
            if (current > 1) {
                qtyInput.value = current - 1;
                calculateTotals();
            }
        });

        btnPlus.addEventListener('click', function () {
            let current = parseInt(qtyInput.value) || 1;
            qtyInput.value = current + 1;
            calculateTotals();
        });

        priceInput.addEventListener('input', calculateTotals);

        btnDelete.addEventListener('click', function () {
            row.remove();
            checkEmptyState();
            calculateTotals();
        });
    }

    function checkEmptyState() {
        const rows = itemsContainer.querySelectorAll('.item-card');
        if (rows.length === 0) {
            emptyItemsState.classList.remove('hidden');
        } else {
            emptyItemsState.classList.add('hidden');
        }
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalItems = 0;

        const rows = itemsContainer.querySelectorAll('.item-card');
        rows.forEach(row => {
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const qty = parseInt(row.querySelector('.item-qty').value) || 1;
            subtotal += (price * qty);
            totalItems += qty;
        });

        const discount = parseFloat(inputDiscount.value) || 0;
        const delivery = parseFloat(inputDeliveryFee.value) || 0;
        const service = parseFloat(inputServiceFee.value) || 0;

        const grandTotal = Math.max(0, subtotal + delivery + service - discount);

        summarySubtotal.textContent = formatRupiah(subtotal);
        summaryDelivery.textContent = formatRupiah(delivery);
        summaryService.textContent = formatRupiah(service);
        summaryDiscount.textContent = '-' + formatRupiah(discount);
        summaryGrandTotal.textContent = formatRupiah(grandTotal);

        floatingGrandTotal.textContent = formatRupiah(grandTotal);
        floatingItemCount.textContent = `(${totalItems} item)`;
    }

    // Bind fee/discount changes
    inputDiscount.addEventListener('input', calculateTotals);
    inputDeliveryFee.addEventListener('input', calculateTotals);
    inputServiceFee.addEventListener('input', calculateTotals);

    // Add manual item button
    btnAddItem.addEventListener('click', function () {
        addItemRow('', 1, 0);
    });


    // QRIS Choice Radio toggle
    qrisChoiceRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'new') {
                newQrisContainer.classList.remove('hidden');
            } else {
                newQrisContainer.classList.add('hidden');
            }
        });
    });

    // Client-side QR Code Decoder via jsQR
    if (qrisFileInput) {
        qrisFileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            qrisDetectStatus.className = 'p-2.5 rounded-lg text-xs flex items-center gap-2 bg-zinc-100 text-zinc-700';
            qrisDetectStatus.innerHTML = '<div class="w-3 h-3 border-2 border-zinc-400 border-t-zinc-800 rounded-full animate-spin"></div><span>Membaca QRIS statis...</span>';
            qrisDetectStatus.classList.remove('hidden');

            const reader = new FileReader();
            reader.onload = function (event) {
                const img = new Image();
                img.onload = function () {
                    const canvas = document.getElementById('qrCanvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0, img.width, img.height);

                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);

                    if (code && code.data) {
                        const payload = code.data.trim();
                        // Validate EMVCo tags (000201 and 5802ID or 5303360)
                        if (payload.startsWith('000201') && (payload.includes('5802ID') || payload.includes('5303360'))) {
                            newQrisPayload.value = payload;
                            qrisDetectStatus.className = 'p-2.5 rounded-lg text-xs flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200';
                            qrisDetectStatus.innerHTML = '<i class="fa-light fa-circle-check text-emerald-600 text-sm"></i><span>QRIS Statis Valid terdeteksi!</span>';
                        } else {
                            newQrisPayload.value = '';
                            qrisDetectStatus.className = 'p-2.5 rounded-lg text-xs flex items-center gap-2 bg-amber-50 text-amber-900 border border-amber-200';
                            qrisDetectStatus.innerHTML = '<i class="fa-light fa-circle-exclamation text-amber-600 text-sm"></i><span>QR Code terdeteksi namun bukan standar QRIS EMVCo.</span>';
                        }
                    } else {
                        newQrisPayload.value = '';
                        qrisDetectStatus.className = 'p-2.5 rounded-lg text-xs flex items-center gap-2 bg-rose-50 text-rose-800 border border-rose-200';
                        qrisDetectStatus.innerHTML = '<i class="fa-light fa-circle-xmark text-rose-600 text-sm"></i><span>Gagal membaca QR Code dari gambar ini. Pastikan gambar jelas dan tidak buram.</span>';
                    }
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // Toggle Bank Options
    if (toggleEnableBank) {
        toggleEnableBank.addEventListener('change', function () {
            if (this.checked) {
                bankSectionContainer.classList.remove('hidden');
            } else {
                bankSectionContainer.classList.add('hidden');
            }
        });
    }

    // Add Dynamic Bank Row
    function addBankRow() {
        const id = bankCounter++;
        const row = document.createElement('div');
        row.className = 'p-3 rounded-xl bg-zinc-50 border border-zinc-200/90 space-y-2 relative';
        row.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-zinc-700">Rekening Baru #${id + 1}</span>
                <button type="button" class="btn-remove-bank text-zinc-400 hover:text-rose-600 text-xs">
                    <i class="fa-light fa-trash-can"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <input type="text" name="new_banks[${id}][bank_name]" placeholder="Bank / E-Wallet (BCA, GoPay)" class="px-2.5 py-1.5 text-xs bg-white border border-zinc-300 rounded-lg focus:outline-none focus:border-emerald-700 text-zinc-900">
                <input type="text" name="new_banks[${id}][account_number]" placeholder="Nomor Rekening / HP" class="px-2.5 py-1.5 text-xs bg-white border border-zinc-300 rounded-lg focus:outline-none focus:border-emerald-700 text-zinc-900 font-medium tabular-nums">
                <input type="text" name="new_banks[${id}][account_holder]" placeholder="Atas Nama" class="px-2.5 py-1.5 text-xs bg-white border border-zinc-300 rounded-lg focus:outline-none focus:border-emerald-700 text-zinc-900">
            </div>
            <label class="flex items-center gap-2 cursor-pointer select-none pt-0.5">
                <input type="checkbox" name="new_banks[${id}][save_to_profile]" value="1" checked class="w-3.5 h-3.5 rounded border-zinc-300 text-emerald-800 focus:ring-emerald-700 cursor-pointer accent-emerald-800">
                <span class="text-[11px] text-zinc-500">Simpan ke profil akun saya</span>
            </label>
        `;

        newBanksList.appendChild(row);

        row.querySelector('.btn-remove-bank').addEventListener('click', function () {
            row.remove();
        });
    }

    if (btnAddBankRow) {
        btnAddBankRow.addEventListener('click', addBankRow);
    }

    // Format Explanation & Dynamic Hint Handler
    function updateFormatHint() {
        if (!activeFormatHintText || !receiptPriceType) return;
        if (receiptPriceType.value === 'total_price') {
            activeFormatHintText.innerHTML = '<strong>Format Harga Total:</strong> Nominal di baris struk adalah total pesanan menu (misal <code>2x Nasi Goreng Rp 50.000</code>). AI otomatis membagi: <strong>Rp 50.000 ÷ 2 = Rp 25.000 / item</strong>.';
        } else {
            activeFormatHintText.innerHTML = '<strong>Format Harga Satuan:</strong> Nominal di baris struk adalah harga 1 item (misal <code>2x Nasi Goreng @ 25.000</code>, tertulis 25.000). AI langsung mencatat Rp 25.000.';
        }
    }

    if (receiptPriceType) {
        receiptPriceType.addEventListener('change', updateFormatHint);
    }

    if (btnToggleFormatInfo && formatDetailBox) {
        btnToggleFormatInfo.addEventListener('click', function () {
            formatDetailBox.classList.toggle('hidden');
        });
    }

    if (btnCloseFormatDetail && formatDetailBox) {
        btnCloseFormatDetail.addEventListener('click', function () {
            formatDetailBox.classList.add('hidden');
        });
    }

    // AI OCR Vision Handler
    if (receiptFileInput) {
        receiptFileInput.addEventListener('change', async function () {
            const files = Array.from(receiptFileInput.files);
            if (files.length === 0) return;

            aiLoadingIndicator.classList.remove('hidden');
            aiAlert.classList.add('hidden');
            aiLoadingText.textContent = `Menganalisis ${files.length} gambar struk via AI Vision...`;

            const formData = new FormData();
            files.forEach(file => {
                formData.append('receipt_images[]', file);
            });
            formData.append('receipt_price_type', receiptPriceType.value);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            try {
                const response = await fetch('{{ route("bills.parse_receipt") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                aiLoadingIndicator.classList.add('hidden');

                if (data.success) {
                    // Populate Items
                    itemsContainer.innerHTML = '';
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            addItemRow(item.name, item.qty, item.price);
                        });
                    } else {
                        addItemRow('', 1, 0);
                    }

                    // Populate Fees & Discount
                    if (data.discount > 0) inputDiscount.value = data.discount;
                    if (data.delivery_fee > 0) inputDeliveryFee.value = data.delivery_fee;
                    if (data.service_fee > 0) inputServiceFee.value = data.service_fee;

                    // Set Title if empty
                    const titleInput = document.getElementById('title');
                    if (!titleInput.value && data.merchant_name && data.merchant_name !== 'Toko / Restoran') {
                        titleInput.value = 'Makan di ' + data.merchant_name;
                    }

                    calculateTotals();

                    aiAlert.className = 'p-3 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-900 border border-emerald-200 block';
                    aiAlert.innerHTML = `<i class="fa-light fa-circle-check text-emerald-600 mr-1"></i> Struk berhasil dianalisis! Ditemukan <strong>${data.items.length} item</strong> menu pesanan.`;
                } else {
                    aiAlert.className = 'p-3 rounded-xl text-xs font-medium bg-rose-50 text-rose-900 border border-rose-200 block';
                    aiAlert.innerHTML = `<i class="fa-light fa-circle-exclamation text-rose-600 mr-1"></i> ${data.error || 'Gagal memproses gambar struk.'}`;
                }
            } catch (err) {
                aiLoadingIndicator.classList.add('hidden');
                aiAlert.className = 'p-3 rounded-xl text-xs font-medium bg-rose-50 text-rose-900 border border-rose-200 block';
                aiAlert.innerHTML = '<i class="fa-light fa-triangle-exclamation text-rose-600 mr-1"></i> Terjadi kesalahan jaringan saat memproses struk.';
            }
        });
    }

    // Form Submit Loading Feedback
    const createBillForm = document.getElementById('createBillForm');
    if (createBillForm) {
        createBillForm.addEventListener('submit', function () {
            const btnSubmit = document.getElementById('btnSubmitBill');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-50', 'pointer-events-none');
            }
            if (window.Notiflix) {
                Notiflix.Loading.pulse('Menyimpan & menyiapkan tagihan patungan...');
            }
        });
    }

    // Initialize with 1 empty item row by default
    addItemRow('', 1, 0);
});
</script>
@endpush
