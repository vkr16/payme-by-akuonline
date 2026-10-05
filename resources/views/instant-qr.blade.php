@extends('layouts.app')

@section('title', 'QR Instant - PayMe')
@section('meta_description', 'Generate QRIS Dinamis Instan dengan nominal terkunci tanpa buat tagihan.')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-16">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200/90">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/70 flex items-center justify-center shadow-2xs">
                    <i class="fa-light fa-bolt text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-zinc-900 tracking-tight flex items-center gap-2">
                        <span>QR Instant</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                            <i class="fa-light fa-sparkles text-[10px]"></i>
                            <span>New Feature</span>
                        </span>
                    </h1>
                </div>
            </div>
            <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                Ketik nominal, langsung jadi QRIS dinamis ber-nominal pas. Tunjukkan ke pembayar atau unduh kartu gambar.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('payment_methods.index') }}" class="touch-target inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-zinc-300 hover:bg-zinc-50 shadow-2xs text-zinc-700 transition-colors">
                <i class="fa-light fa-wallet text-zinc-400"></i>
                <span>Kelola&nbsp;QRIS</span>
            </a>
            <a href="{{ route('dashboard') }}" class="touch-target inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-zinc-300 hover:bg-zinc-50 shadow-2xs text-zinc-700 transition-colors">
                <i class="fa-light fa-arrow-left text-zinc-400"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    @if($qrisList->isEmpty())
        <!-- ONBOARDING STATE: Host belum punya QRIS -->
        <div class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm text-center max-w-xl mx-auto space-y-5">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200/60 mx-auto flex items-center justify-center text-2xl shadow-2xs">
                <i class="fa-light fa-qrcode"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-zinc-900">Unggah QRIS Pertamamu</h2>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1 leading-relaxed">
                    Untuk menggunakan fitur QR Instant, unggah gambar QRIS statis merchant Anda (GoPay, ShopeePay, BCA, dll). QRIS akan otomatis tersimpan di akun Anda sehingga untuk transaksi berikutnya langsung instan tanpa upload lagi!
                </p>
            </div>

            <!-- Upload Form -->
            <form id="formFirstQris" class="space-y-4 text-left">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1.5">
                        Pilih Gambar QRIS Statis Merchant Anda
                    </label>
                    <label class="relative flex flex-col items-center justify-center p-6 rounded-xl border-2 border-dashed border-zinc-300 hover:border-emerald-600 bg-zinc-50/50 hover:bg-emerald-50/20 cursor-pointer transition-all">
                        <i class="fa-light fa-cloud-arrow-up text-2xl text-zinc-400 mb-2"></i>
                        <span class="text-xs font-semibold text-zinc-700">Klik untuk pilih gambar QRIS</span>
                        <span class="text-[11px] text-zinc-400 mt-0.5">JPG, PNG, atau WebP (maks. 10MB)</span>
                        <input type="file" id="onboardFileInput" accept="image/*" class="sr-only">
                    </label>
                    <div id="onboardDetectStatus" class="hidden mt-2 p-2.5 rounded-lg text-xs"></div>
                </div>

                <div class="pt-2">
                    <label class="block text-xs font-bold text-zinc-700 mb-1.5">
                        Kode QRIS (Auto-detected dari gambar atau paste manual)
                    </label>
                    <textarea id="onboardPayload" name="payload" rows="2" placeholder="00020101021126..." class="w-full text-xs font-mono p-3 rounded-xl border border-zinc-300 focus:border-emerald-700 focus:ring-1 focus:ring-emerald-700 bg-zinc-50/50 focus:bg-white resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Nama Merchant</label>
                        <input type="text" id="onboardMerchantName" name="merchant_name" placeholder="cth: Kopi Senja" class="w-full text-xs p-2.5 rounded-xl border border-zinc-300 focus:border-emerald-700 focus:ring-1 focus:ring-emerald-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Kota Merchant</label>
                        <input type="text" id="onboardMerchantCity" name="merchant_city" placeholder="cth: Jakarta Selatan" class="w-full text-xs p-2.5 rounded-xl border border-zinc-300 focus:border-emerald-700 focus:ring-1 focus:ring-emerald-700">
                    </div>
                </div>

                <button type="submit" id="btnSaveOnboardQris" class="touch-target w-full py-3 px-4 rounded-xl btn-primary font-bold text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer shadow-xs transition-all">
                    <i class="fa-light fa-floppy-disk text-xs"></i>
                    <span>Simpan & Lanjutkan ke QR Instant</span>
                </button>
            </form>
        </div>
    @else
        <!-- NORMAL STATE: Host sudah punya QRIS (True Instant Experience) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN: INPUT & KONFIGURASI (7 cols) -->
            <div class="lg:col-span-7 space-y-5">

                <!-- 1. Pilihan QRIS Aktif -->
                <div class="card-solid rounded-2xl p-5 bg-white border border-zinc-200/90 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-zinc-700 flex items-center gap-1.5 uppercase tracking-wider">
                            <i class="fa-light fa-store text-emerald-700"></i>
                            <span>Tujuan Pembayaran (QRIS Aktif)</span>
                        </label>
                        @if($qrisList->count() > 1)
                            <span class="text-[11px] text-zinc-400 font-medium">Klik untuk ganti</span>
                        @endif
                    </div>

                    @if($qrisList->count() > 1)
                        <!-- Multi-QRIS Pill Switcher -->
                        <div class="flex items-center gap-2 overflow-x-auto pb-1" id="qrisPillContainer">
                            @foreach($qrisList as $qris)
                                <button type="button"
                                    data-qris-id="{{ $qris->id }}"
                                    data-merchant-name="{{ $qris->merchant_name ?: $user->name }}"
                                    data-merchant-city="{{ $qris->merchant_city ?: 'Indonesia' }}"
                                    data-payload="{{ $qris->payload }}"
                                    class="qris-pill touch-target flex-shrink-0 px-3 py-2 rounded-xl text-xs font-semibold border transition-all text-left flex items-center gap-2 {{ $qris->id === ($defaultQris?->id ?? null) ? 'bg-emerald-50 border-emerald-600 text-emerald-900 shadow-2xs' : 'bg-zinc-50 hover:bg-zinc-100 border-zinc-200 text-zinc-700' }}">
                                    <i class="fa-light {{ $qris->id === ($defaultQris?->id ?? null) ? 'fa-circle-check text-emerald-700' : 'fa-circle text-zinc-300' }} text-xs"></i>
                                    <div>
                                        <div class="font-bold leading-tight">{{ $qris->merchant_name ?: $user->name }}</div>
                                        <div class="text-[10px] text-zinc-400 leading-tight">{{ $qris->merchant_city ?: 'Indonesia' }}</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <!-- Single QRIS Badge Info -->
                        <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200/80 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-emerald-100/70 text-emerald-800 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-light fa-qrcode"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-zinc-900" id="currentMerchantNameDisplay">{{ $defaultQris?->merchant_name ?: $user->name }}</div>
                                    <div class="text-xs text-zinc-500" id="currentMerchantCityDisplay">{{ ($defaultQris?->merchant_city ?: 'Indonesia') . ' • Host: ' . $user->name }}</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                                <i class="fa-light fa-check text-[10px]"></i> Default
                            </span>
                        </div>
                    @endif
                </div>

                <!-- 2. Input Nominal Pembayaran (Tactile & Auto-focus) -->
                <div class="card-solid rounded-2xl p-5 sm:p-6 bg-white border border-zinc-200/90 shadow-sm space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="amountInput" class="text-xs font-bold text-zinc-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-light fa-money-bill-wave text-emerald-700"></i>
                                <span>Nominal Pembayaran</span>
                            </label>
                            <span class="text-[11px] text-zinc-400">Nominal pas terkunci di QR</span>
                        </div>

                        <!-- Big Input with Rp prefix -->
                        <div class="relative rounded-2xl border-2 border-emerald-800/40 focus-within:border-emerald-800 focus-within:ring-2 focus-within:ring-emerald-800/20 bg-zinc-50/30 transition-all">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg sm:text-xl font-black text-zinc-400 select-none">
                                Rp
                            </span>
                            <input type="text"
                                id="amountInput"
                                inputmode="numeric"
                                autofocus
                                placeholder="0"
                                class="w-full pl-13 pr-4 py-3.5 sm:py-4 text-2xl sm:text-3xl font-black text-zinc-900 tabular-nums bg-transparent focus:outline-none placeholder:text-zinc-300">
                        </div>
                    </div>

                    <!-- 3. Catatan Pembayaran Opsional -->
                    <div class="pt-3 border-t border-zinc-100">
                        <label for="noteInput" class="block text-xs font-bold text-zinc-700 mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-light fa-comment-dots text-zinc-400"></i>
                                <span>Keterangan / Catatan (Opsional)</span>
                            </span>
                            <span class="text-[10px] text-zinc-400 font-normal">Maks. 50 karakter</span>
                        </label>
                        <input type="text"
                            id="noteInput"
                            maxlength="50"
                            placeholder="cth: Ganti Bensin, Kopi Siang, Uang Kas..."
                            class="w-full text-xs sm:text-sm p-3 rounded-xl border border-zinc-300 focus:border-emerald-700 focus:ring-1 focus:ring-emerald-700 bg-zinc-50/50 focus:bg-white transition-all">
                    </div>
                </div>

                <!-- Info Tips Box -->
                <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200/60 text-emerald-900 text-xs flex items-start gap-3">
                    <i class="fa-light fa-shield-check text-emerald-700 text-base mt-0.5 flex-shrink-0"></i>
                    <div class="space-y-1">
                        <span class="font-bold block">Tanpa Tagihan & Tanpa Potongan</span>
                        <p class="text-emerald-800/90 leading-relaxed text-[11px]">
                            Fitur ini murni mengonversi QRIS statis menjadi QRIS dinamis ber-nominal pas secara instan. Uang langsung masuk 100% ke rekening Anda tanpa perantara dan tanpa potongan apapun.
                        </p>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: LIVE DYNAMIC QR CARD PREVIEW (5 cols) -->
            <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-20">

                <!-- The Live Payment Card -->
                <div class="card-solid rounded-2xl bg-white border border-zinc-200/90 shadow-sm overflow-hidden" id="instantQrCard">

                    <!-- Top Emerald Header -->
                    <div class="bg-emerald-900 text-white px-5 py-3.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-emerald-800/80 flex items-center justify-center text-xs text-emerald-200 font-bold">
                                <i class="fa-light fa-bolt"></i>
                            </div>
                            <span class="text-xs font-bold tracking-tight">PayMe • QRIS DINAMIS</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[10px] text-emerald-200 font-medium">
                            <i class="fa-light fa-lock text-[9px]"></i> Nominal Terkunci
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 text-center space-y-4">

                        <!-- Merchant Info & Note -->
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-zinc-400 tracking-wider uppercase block">Tujuan Pembayaran</span>
                            <h2 class="text-base font-black text-zinc-900" id="cardMerchantName">{{ $defaultQris?->merchant_name ?: $user->name }}</h2>
                            <p class="text-xs text-zinc-500" id="cardMerchantCity">{{ ($defaultQris?->merchant_city ?: 'Indonesia') . ' • Host: ' . $user->name }}</p>

                            <div id="cardNoteContainer" class="hidden pt-1">
                                <span id="cardNoteBadge" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                                    <i class="fa-light fa-tag text-[10px]"></i>
                                    <span id="cardNoteText"></span>
                                </span>
                            </div>
                        </div>

                        <!-- QR Code Canvas Display -->
                        <div class="relative mx-auto w-56 h-56 p-2 rounded-2xl bg-white border-2 border-dashed border-zinc-200 flex items-center justify-center shadow-2xs" id="qrContainerWrapper">
                            <!-- Empty / Zero Amount Placeholder -->
                            <div id="qrEmptyPlaceholder" class="text-center p-4 space-y-2">
                                <i class="fa-light fa-qrcode text-5xl text-zinc-300"></i>
                                <p class="text-xs text-zinc-400 font-medium leading-relaxed">
                                    Ketik nominal di samping untuk memunculkan QRIS Dinamis
                                </p>
                            </div>

                            <!-- Live Rendered QR -->
                            <div id="instantQrCanvasContainer" class="hidden flex items-center justify-center"></div>
                        </div>

                        <!-- Grand Total Display -->
                        <div class="space-y-1 pt-1">
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Total yang Harus Dibayar</span>
                            <div class="text-3xl font-black text-emerald-800 tabular-nums" id="cardNominalDisplay">
                                Rp 0
                            </div>
                            <span class="text-[10px] text-zinc-400 block">
                                Scan via BCA, Mandiri, BRI, GoPay, OVO, ShopeePay, DANA dll.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2.5">
                    <!-- 1. Tunjukkan ke Pembayar (Fullscreen Focus Mode) -->
                    <button type="button"
                        id="btnOpenFullscreen"
                        disabled
                        class="touch-target w-full py-3.5 px-4 rounded-xl btn-primary font-bold text-sm flex items-center justify-center gap-2 shadow-xs transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <i class="fa-light fa-expand text-base"></i>
                        <span>Tunjukkan ke Pembayar</span>
                    </button>

                    <!-- Secondary Action Grid -->
                    <div class="grid grid-cols-2 gap-2">
                        <!-- 2. Unduh Card (PNG) -->
                        <button type="button"
                            id="btnDownloadCard"
                            disabled
                            class="touch-target py-2.5 px-3 rounded-xl bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-2xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <i class="fa-light fa-download text-emerald-700 text-sm"></i>
                            <span>Unduh Card (PNG)</span>
                        </button>

                        <!-- 3. Salin Kode QRIS -->
                        <button type="button"
                            id="btnCopyPayload"
                            disabled
                            class="touch-target py-2.5 px-3 rounded-xl bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-2xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <i class="fa-light fa-copy text-zinc-500 text-sm"></i>
                            <span>Salin Kode</span>
                        </button>
                    </div>

                    <!-- 4. Share button (Only if Web Share is supported) -->
                    <button type="button"
                        id="btnShareCard"
                        disabled
                        class="hidden touch-target w-full py-2.5 px-3 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-light fa-share-nodes text-sm"></i>
                        <span>Bagikan Gambar ke WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

<!-- FULLSCREEN FOCUS MODAL (Tunjukkan ke Pembayar) -->
<div id="fullscreenModal" class="hidden fixed inset-0 z-50 bg-zinc-950/95 backdrop-blur-md flex flex-col items-center justify-center p-4 select-none">
    <!-- Close Button Top Right -->
    <button type="button"
        id="btnCloseFullscreen"
        class="absolute top-4 right-4 touch-target w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xl transition-all cursor-pointer">
        <i class="fa-light fa-xmark"></i>
    </button>

    <!-- Modal Content Card -->
    <div class="w-full max-w-sm bg-white rounded-3xl p-6 sm:p-8 text-center space-y-5 shadow-2xl relative animate-in fade-in zoom-in-95 duration-200">

        <!-- Top Emerald Badge -->
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
            <i class="fa-light fa-bolt text-amber-500"></i>
            <span>QRIS DINAMIS • NOMINAL TERKUNCI</span>
        </div>

        <!-- Destination -->
        <div class="space-y-0.5">
            <div class="text-lg font-black text-zinc-900" id="fsMerchantName">{{ $defaultQris?->merchant_name ?: $user->name }}</div>
            <div class="text-xs text-zinc-500" id="fsMerchantCity">{{ ($defaultQris?->merchant_city ?: 'Indonesia') . ' • Host: ' . $user->name }}</div>
            <div id="fsNoteContainer" class="hidden pt-1">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800" id="fsNoteText"></span>
            </div>
        </div>

        <!-- Giant QR Container -->
        <div class="w-64 h-64 mx-auto p-3 rounded-2xl bg-white border border-zinc-200 flex items-center justify-center shadow-inner" id="fsQrContainer">
            <!-- Canvas will be placed here -->
        </div>

        <!-- Giant Nominal Display -->
        <div class="space-y-1">
            <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block">Nominal Terkunci</span>
            <div class="text-4xl font-black text-emerald-800 tabular-nums" id="fsNominalDisplay">Rp 0</div>
            <p class="text-xs text-zinc-400 pt-1">
                Scan dengan QRIS scanner di aplikasi mobile banking atau e&dash;wallet anda.
            </p>
        </div>

        <!-- Big Close Button at bottom -->
        <button type="button"
            id="btnCloseFullscreenBottom"
            class="touch-target w-full py-3 px-4 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-bold text-xs sm:text-sm transition-colors cursor-pointer">
            Selesai / Tutup Layar Penuh
        </button>
    </div>
</div>

<!-- Hidden Canvas for jsQR Image Processing on Onboard -->
<canvas id="qrCanvas" class="hidden"></canvas>
@endsection

@push('scripts')
<script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>
<script src="{{ asset('vendor/jsqr/jsQR.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const hostName = "{{ addslashes($user->name) }}";

    // Helper EMVCo extraction
    function extractMerchantInfoClient(payload) {
        let merchantName = '';
        let merchantCity = '';
        if (!payload) return { merchantName, merchantCity };
        const pos58 = payload.indexOf('5802ID');
        if (pos58 !== -1) {
            const afterCountry = payload.substring(pos58 + 6);
            let offset = 0;
            const afterLen = afterCountry.length;
            while (offset + 4 <= afterLen) {
                const tag = afterCountry.substring(offset, offset + 2);
                const valLenStr = afterCountry.substring(offset + 2, offset + 4);
                if (!/^\d{2}$/.test(valLenStr)) break;
                const valLen = parseInt(valLenStr, 10);
                offset += 4;
                const val = afterCountry.substring(offset, offset + valLen);
                offset += valLen;
                if (tag === '59') merchantName = val.trim();
                else if (tag === '60') merchantCity = val.trim();
                else if (tag === '63') break;
            }
        }
        return { merchantName, merchantCity };
    }

    // =========================================================================
    // ONBOARDING FIRST QRIS (If user has no QRIS yet)
    // =========================================================================
    const formFirstQris = document.getElementById('formFirstQris');
    if (formFirstQris) {
        const onboardFileInput = document.getElementById('onboardFileInput');
        const onboardPayload = document.getElementById('onboardPayload');
        const onboardDetectStatus = document.getElementById('onboardDetectStatus');
        const onboardMerchantName = document.getElementById('onboardMerchantName');
        const onboardMerchantCity = document.getElementById('onboardMerchantCity');

        if (onboardFileInput) {
            onboardFileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                onboardDetectStatus.className = 'mt-2 p-2.5 rounded-lg text-xs flex items-center gap-2 bg-zinc-100 text-zinc-700';
                onboardDetectStatus.innerHTML = '<div class="w-3 h-3 border-2 border-zinc-400 border-t-zinc-800 rounded-full animate-spin"></div><span>Membaca QRIS dari gambar...</span>';
                onboardDetectStatus.classList.remove('hidden');

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
                            if (payload.startsWith('000201') && (payload.includes('5802ID') || payload.includes('5303360'))) {
                                onboardPayload.value = payload;
                                onboardDetectStatus.className = 'mt-2 p-2.5 rounded-lg text-xs flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200';
                                onboardDetectStatus.innerHTML = '<i class="fa-light fa-circle-check text-emerald-600"></i><span>QRIS Statis Valid terdeteksi!</span>';

                                // Auto extract merchant info
                                const info = extractMerchantInfoClient(payload);
                                if (info.merchantName && !onboardMerchantName.value) onboardMerchantName.value = info.merchantName;
                                if (info.merchantCity && !onboardMerchantCity.value) onboardMerchantCity.value = info.merchantCity;
                            } else {
                                onboardPayload.value = '';
                                onboardDetectStatus.className = 'mt-2 p-2.5 rounded-lg text-xs flex items-center gap-2 bg-amber-50 text-amber-900 border border-amber-200';
                                onboardDetectStatus.innerHTML = '<i class="fa-light fa-circle-exclamation text-amber-600"></i><span>QR Code terdeteksi bukan standar QRIS EMVCo.</span>';
                            }
                        } else {
                            onboardPayload.value = '';
                            onboardDetectStatus.className = 'mt-2 p-2.5 rounded-lg text-xs flex items-center gap-2 bg-rose-50 text-rose-800 border border-rose-200';
                            onboardDetectStatus.innerHTML = '<i class="fa-light fa-circle-xmark text-rose-600"></i><span>Gagal membaca QR Code dari file. Pastikan gambar tajam.</span>';
                        }
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        formFirstQris.addEventListener('submit', async function (e) {
            e.preventDefault();
            const payload = onboardPayload.value.trim();
            if (!payload || payload.length < 30) {
                if (window.Notiflix) Notiflix.Notify.failure('Unggah atau masukkan kode QRIS yang valid.');
                return;
            }

            const formData = new FormData(formFirstQris);
            formData.append('is_default', '1');

            try {
                if (window.Notiflix) Notiflix.Loading.pulse('Menyimpan QRIS Anda...');
                const res = await fetch("{{ route('payment_methods.qris.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (window.Notiflix) Notiflix.Loading.remove();

                if (data.success) {
                    if (window.Notiflix) Notiflix.Notify.success('QRIS berhasil disimpan! Membuka QR Instant...');
                    window.location.reload();
                } else {
                    if (window.Notiflix) Notiflix.Notify.failure(data.message || 'Gagal menyimpan QRIS.');
                }
            } catch (err) {
                if (window.Notiflix) Notiflix.Loading.remove();
                if (window.Notiflix) Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
            }
        });

        return; // Stop execution of normal mode script
    }

    // =========================================================================
    // NORMAL STATE: TRUE INSTANT GENERATOR
    // =========================================================================

    // Current State
    let activeQris = {
        id: {{ $defaultQris?->id ?? 'null' }},
        merchantName: "{{ addslashes($defaultQris?->merchant_name ?? '') }}",
        merchantCity: "{{ addslashes($defaultQris?->merchant_city ?? '') }}",
        payload: "{{ $defaultQris?->payload ?? '' }}"
    };

    // Auto-extract merchant name and city from payload if missing or generic
    if (!activeQris.merchantName || activeQris.merchantName === 'Merchant QRIS' || activeQris.merchantName === 'Merchant') {
        if (activeQris.payload) {
            try {
                const parsed = extractMerchantInfoClient(activeQris.payload);
                if (parsed.merchantName) activeQris.merchantName = parsed.merchantName;
                if (parsed.merchantCity && (!activeQris.merchantCity || activeQris.merchantCity === 'Indonesia')) activeQris.merchantCity = parsed.merchantCity;
            } catch (e) {}
        }
    }
    if (!activeQris.merchantName) activeQris.merchantName = hostName;
    if (!activeQris.merchantCity) activeQris.merchantCity = 'Indonesia';

    let currentNominal = 0;
    let currentNote = '';
    let currentDynamicPayload = '';
    let qrcodeInstance = null;

    // Elements
    const amountInput = document.getElementById('amountInput');
    const noteInput = document.getElementById('noteInput');

    const cardMerchantName = document.getElementById('cardMerchantName');
    const cardMerchantCity = document.getElementById('cardMerchantCity');
    const cardNoteContainer = document.getElementById('cardNoteContainer');
    const cardNoteText = document.getElementById('cardNoteText');
    const cardNominalDisplay = document.getElementById('cardNominalDisplay');

    const qrEmptyPlaceholder = document.getElementById('qrEmptyPlaceholder');
    const instantQrCanvasContainer = document.getElementById('instantQrCanvasContainer');

    const btnOpenFullscreen = document.getElementById('btnOpenFullscreen');
    const btnDownloadCard = document.getElementById('btnDownloadCard');
    const btnCopyPayload = document.getElementById('btnCopyPayload');
    const btnShareCard = document.getElementById('btnShareCard');

    // Fullscreen Elements
    const fullscreenModal = document.getElementById('fullscreenModal');
    const btnCloseFullscreen = document.getElementById('btnCloseFullscreen');
    const btnCloseFullscreenBottom = document.getElementById('btnCloseFullscreenBottom');
    const fsMerchantName = document.getElementById('fsMerchantName');
    const fsMerchantCity = document.getElementById('fsMerchantCity');
    const fsNoteContainer = document.getElementById('fsNoteContainer');
    const fsNoteText = document.getElementById('fsNoteText');
    const fsQrContainer = document.getElementById('fsQrContainer');
    const fsNominalDisplay = document.getElementById('fsNominalDisplay');

    function updateMerchantDisplay() {
        const mName = activeQris.merchantName || hostName;
        const mCity = activeQris.merchantCity || 'Indonesia';

        if (cardMerchantName) cardMerchantName.textContent = mName;
        if (cardMerchantCity) cardMerchantCity.textContent = `${mCity} • Host: ${hostName}`;
        if (fsMerchantName) fsMerchantName.textContent = mName;
        if (fsMerchantCity) fsMerchantCity.textContent = `${mCity} • Host: ${hostName}`;

        const currentMName = document.getElementById('currentMerchantNameDisplay');
        const currentMCity = document.getElementById('currentMerchantCityDisplay');
        if (currentMName) currentMName.textContent = mName;
        if (currentMCity) currentMCity.textContent = `${mCity} • Host: ${hostName}`;
    }

    // Call immediately on load to sync all displays with default QRIS!
    updateMerchantDisplay();

    // Multi-QRIS Pill Switching
    const qrisPills = document.querySelectorAll('.qris-pill');
    qrisPills.forEach(pill => {
        pill.addEventListener('click', function () {
            qrisPills.forEach(p => {
                p.classList.remove('bg-emerald-50', 'border-emerald-600', 'text-emerald-900', 'shadow-2xs');
                p.classList.add('bg-zinc-50', 'border-zinc-200', 'text-zinc-700');
                const icon = p.querySelector('i');
                if (icon) {
                    icon.className = 'fa-light fa-circle text-zinc-300 text-xs';
                }
            });

            this.classList.remove('bg-zinc-50', 'border-zinc-200', 'text-zinc-700');
            this.classList.add('bg-emerald-50', 'border-emerald-600', 'text-emerald-900', 'shadow-2xs');
            const icon = this.querySelector('i');
            if (icon) {
                icon.className = 'fa-light fa-circle-check text-emerald-700 text-xs';
            }

            activeQris.id = parseInt(this.dataset.qrisId, 10);
            activeQris.merchantName = this.dataset.merchantName;
            activeQris.merchantCity = this.dataset.merchantCity || 'Indonesia';
            activeQris.payload = this.dataset.payload;

            if (!activeQris.merchantName || activeQris.merchantName === 'Merchant QRIS' || activeQris.merchantName === 'Merchant') {
                if (activeQris.payload) {
                    try {
                        const parsed = extractMerchantInfoClient(activeQris.payload);
                        if (parsed.merchantName) activeQris.merchantName = parsed.merchantName;
                        if (parsed.merchantCity && (!activeQris.merchantCity || activeQris.merchantCity === 'Indonesia')) activeQris.merchantCity = parsed.merchantCity;
                    } catch (e) {}
                }
            }
            if (!activeQris.merchantName) activeQris.merchantName = hostName;

            updateMerchantDisplay();
            regenerateDynamicQr();
        });
    });

    // Amount Formatting Helpers
    function parseRupiahNumber(str) {
        if (!str) return 0;
        const cleaned = str.toString().replace(/[^\d]/g, '');
        return parseInt(cleaned, 10) || 0;
    }

    function formatRupiah(num) {
        return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function setNominal(newAmount) {
        currentNominal = Math.max(0, Math.round(newAmount));
        amountInput.value = currentNominal > 0 ? currentNominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        cardNominalDisplay.textContent = formatRupiah(currentNominal);
        fsNominalDisplay.textContent = formatRupiah(currentNominal);
        regenerateDynamicQr();
    }

    // Input Event: Live Format
    amountInput.addEventListener('input', function () {
        const val = parseRupiahNumber(this.value);
        currentNominal = val;
        this.value = val > 0 ? val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        cardNominalDisplay.textContent = formatRupiah(currentNominal);
        fsNominalDisplay.textContent = formatRupiah(currentNominal);
        regenerateDynamicQr();
    });

    // Note Input Event
    if (noteInput) {
        noteInput.addEventListener('input', function () {
            currentNote = this.value.trim();
            if (currentNote) {
                cardNoteText.textContent = currentNote;
                cardNoteContainer.classList.remove('hidden');
                fsNoteText.textContent = currentNote;
                fsNoteContainer.classList.remove('hidden');
            } else {
                cardNoteContainer.classList.add('hidden');
                fsNoteContainer.classList.add('hidden');
            }
        });
    }

    // =========================================================================
    // DYNAMIC QRIS GENERATION
    // =========================================================================

    // Client-side EMVCo Dynamic Converter (0ms lag!)
    function convertToDynamicClient(staticPayload, amount) {
        const nominalStr = Math.round(amount).toString();
        // Remove trailing 4-char CRC
        let cleanQris = staticPayload.slice(0, -4);
        // Replace Tag 010211 (Static) with 010212 (Dynamic)
        cleanQris = cleanQris.replace('010211', '010212');

        const parts = cleanQris.split('5802ID');
        if (parts.length < 2) return staticPayload;

        const prefix = parts[0];
        const suffix = parts[1];

        // Tag 54: Transaction Amount
        const lenStr = nominalStr.length.toString().padStart(2, '0');
        const nominalData = '54' + lenStr + nominalStr;

        const payloadToCrc = prefix + nominalData + '5802ID' + suffix;
        const crc = calculateCrc16(payloadToCrc);

        return payloadToCrc + crc;
    }

    // CRC16-CCITT for QRIS
    function calculateCrc16(str) {
        let crc = 0xFFFF;
        for (let c = 0; c < str.length; c++) {
            crc ^= (str.charCodeAt(c) << 8);
            for (let i = 0; i < 8; i++) {
                if ((crc & 0x8000) !== 0) {
                    crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    crc = (crc << 1) & 0xFFFF;
                }
            }
        }
        return (crc & 0xFFFF).toString(16).toUpperCase().padStart(4, '0');
    }

    function regenerateDynamicQr() {
        if (!activeQris.payload || currentNominal <= 0) {
            // Disabled state
            qrEmptyPlaceholder.classList.remove('hidden');
            instantQrCanvasContainer.classList.add('hidden');
            instantQrCanvasContainer.innerHTML = '';
            currentDynamicPayload = '';

            btnOpenFullscreen.disabled = true;
            btnDownloadCard.disabled = true;
            btnCopyPayload.disabled = true;
            if (btnShareCard) btnShareCard.disabled = true;
            return;
        }

        // Generate dynamic payload
        currentDynamicPayload = convertToDynamicClient(activeQris.payload, currentNominal);

        // Render QR in preview card
        instantQrCanvasContainer.innerHTML = '';
        qrEmptyPlaceholder.classList.add('hidden');
        instantQrCanvasContainer.classList.remove('hidden');

        new QRCode(instantQrCanvasContainer, {
            text: currentDynamicPayload,
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });

        // Enable buttons
        btnOpenFullscreen.disabled = false;
        btnDownloadCard.disabled = false;
        btnCopyPayload.disabled = false;
        if (btnShareCard) btnShareCard.disabled = false;
    }

    // =========================================================================
    // ACTIONS: FULLSCREEN FOCUS MODE
    // =========================================================================
    if (btnOpenFullscreen) {
        btnOpenFullscreen.addEventListener('click', function () {
            if (currentNominal <= 0 || !currentDynamicPayload) return;

            updateMerchantDisplay();

            // Render giant QR for fullscreen
            fsQrContainer.innerHTML = '';
            new QRCode(fsQrContainer, {
                text: currentDynamicPayload,
                width: 240,
                height: 240,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });

            fullscreenModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
    }

    function closeFullscreen() {
        fullscreenModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    if (btnCloseFullscreen) btnCloseFullscreen.addEventListener('click', closeFullscreen);
    if (btnCloseFullscreenBottom) btnCloseFullscreenBottom.addEventListener('click', closeFullscreen);

    // Close on backdrop click
    fullscreenModal.addEventListener('click', function (e) {
        if (e.target === fullscreenModal) closeFullscreen();
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !fullscreenModal.classList.contains('hidden')) {
            closeFullscreen();
        }
    });

    // =========================================================================
    // ACTIONS: DOWNLOAD CARD AS PNG (High Resolution 3x Canvas)
    // =========================================================================
    // Helper to get active QR Image / Canvas Source
    function getQrSource(callback) {
        if (!instantQrCanvasContainer || currentNominal <= 0) return;
        const qrCanvas = instantQrCanvasContainer.querySelector('canvas');
        const qrImg = instantQrCanvasContainer.querySelector('img');

        if (qrCanvas) {
            callback(qrCanvas);
        } else if (qrImg && qrImg.src) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => callback(img);
            img.src = qrImg.src;
        } else {
            if (window.Notiflix) Notiflix.Notify.failure('QR Code belum selesai dimuat.');
        }
    }

    // Helper to render high-resolution 3x branded PayMe Card Canvas
    function createCardCanvas(qrSource) {
        const cardCanvas = document.createElement('canvas');
        const ctx = cardCanvas.getContext('2d');
        const scale = 3;
        const cardWidth = 380 * scale;
        const cardHeight = (currentNote ? 540 : 510) * scale;

        cardCanvas.width = cardWidth;
        cardCanvas.height = cardHeight;

        // Background
        ctx.fillStyle = '#F4F4F5';
        ctx.fillRect(0, 0, cardWidth, cardHeight);

        // Top emerald header
        ctx.fillStyle = '#064E3B';
        ctx.fillRect(0, 0, cardWidth, 68 * scale);

        // Header text
        ctx.fillStyle = '#FFFFFF';
        ctx.font = `bold ${14 * scale}px "Plus Jakarta Sans", sans-serif`;
        ctx.textAlign = 'left';
        ctx.fillText('PayMe • QRIS DINAMIS', 24 * scale, 32 * scale);

        ctx.font = `${10 * scale}px "Plus Jakarta Sans", sans-serif`;
        ctx.fillStyle = '#A7F3D0';
        ctx.fillText('Scan & Bayar Otomatis Nominal Pas', 24 * scale, 50 * scale);

        // Merchant Info
        const dlMerchantName = activeQris.merchantName || hostName;
        const dlMerchantCity = activeQris.merchantCity || 'Indonesia';

        ctx.textAlign = 'center';
        ctx.fillStyle = '#71717A';
        ctx.font = `600 ${9 * scale}px "Plus Jakarta Sans", sans-serif`;
        ctx.fillText('TUJUAN PEMBAYARAN', cardWidth / 2, 95 * scale);

        ctx.fillStyle = '#18181B';
        ctx.font = `bold ${15 * scale}px "Plus Jakarta Sans", sans-serif`;
        ctx.fillText(dlMerchantName, cardWidth / 2, 115 * scale);

        ctx.fillStyle = '#71717A';
        ctx.font = `${10 * scale}px "Plus Jakarta Sans", sans-serif`;
        ctx.fillText(`${dlMerchantCity} • Host: ${hostName}`, cardWidth / 2, 132 * scale);

        let qrStartY = 148 * scale;

        // If note exists, draw note badge
        if (currentNote) {
            ctx.fillStyle = '#064E3B';
            ctx.font = `600 ${10 * scale}px "Plus Jakarta Sans", sans-serif`;
            ctx.fillText(`"${currentNote}"`, cardWidth / 2, 148 * scale);
            qrStartY = 162 * scale;
        }

        // QR Box Background
        const qrBoxSize = 220 * scale;
        const qrBoxX = (cardWidth - qrBoxSize) / 2;
        const qrBoxY = qrStartY;

        ctx.fillStyle = '#FFFFFF';
        ctx.beginPath();
        ctx.roundRect(qrBoxX, qrBoxY, qrBoxSize, qrBoxSize, 16 * scale);
        ctx.fill();

        // Draw QR Code onto Card
        const qrSize = 190 * scale;
        const qrX = (cardWidth - qrSize) / 2;
        const qrY = qrBoxY + (qrBoxSize - qrSize) / 2;
        ctx.drawImage(qrSource, qrX, qrY, qrSize, qrSize);

        // Amount Section
        const amountY = qrBoxY + qrBoxSize + (25 * scale);
        ctx.fillStyle = '#71717A';
        ctx.font = `600 ${9 * scale}px "Plus Jakarta Sans", sans-serif`;
        ctx.fillText('TOTAL PEMBAYARAN', cardWidth / 2, amountY);

        ctx.fillStyle = '#064E3B';
        ctx.font = `900 ${22 * scale}px "Plus Jakarta Sans", sans-serif`;
        ctx.fillText(formatRupiah(currentNominal), cardWidth / 2, amountY + (25 * scale));

        // Footer Note
        ctx.fillStyle = '#A1A1AA';
        ctx.font = `${9 * scale}px "Plus Jakarta Sans", sans-serif`;
        ctx.fillText('Scan dengan aplikasi BCA, Mandiri, BRI, GoPay, OVO, ShopeePay, DANA dll.', cardWidth / 2, amountY + (48 * scale));

        // Border around card
        ctx.strokeStyle = '#E4E4E7';
        ctx.lineWidth = 1 * scale;
        ctx.strokeRect(0, 0, cardWidth, cardHeight);

        return cardCanvas;
    }

    // =========================================================================
    // ACTIONS: DOWNLOAD CARD AS PNG (High Resolution 3x Canvas)
    // =========================================================================
    if (btnDownloadCard) {
        btnDownloadCard.addEventListener('click', function () {
            getQrSource(function (qrSource) {
                const cardCanvas = createCardCanvas(qrSource);
                const link = document.createElement('a');
                link.download = `QRIS-Instant-${currentNominal}-${Date.now()}.png`;
                link.href = cardCanvas.toDataURL('image/png');
                link.click();

                if (window.Notiflix) Notiflix.Notify.success('Kartu QRIS berhasil diunduh!');
            });
        });
    }

    // =========================================================================
    // ACTIONS: COPY RAW PAYLOAD
    // =========================================================================
    if (btnCopyPayload) {
        btnCopyPayload.addEventListener('click', function () {
            if (!currentDynamicPayload) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(currentDynamicPayload).then(() => {
                    if (window.Notiflix) Notiflix.Notify.success('Kode QRIS Dinamis berhasil disalin!');
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = currentDynamicPayload;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                if (window.Notiflix) Notiflix.Notify.success('Kode QRIS Dinamis berhasil disalin!');
            }
        });
    }

    // =========================================================================
    // ACTIONS: SHARE CARD (Web Share API Level 2 - Shares the exact same card!)
    // =========================================================================
    if (navigator.share && btnShareCard) {
        btnShareCard.classList.remove('hidden');
        btnShareCard.addEventListener('click', function () {
            getQrSource(function (qrSource) {
                const cardCanvas = createCardCanvas(qrSource);
                cardCanvas.toBlob(async function (blob) {
                    if (!blob) return;
                    const fileName = `QRIS-Instant-${currentNominal}.png`;
                    const file = new File([blob], fileName, { type: 'image/png' });
                    const mName = activeQris.merchantName || hostName;

                    const shareData = {
                        title: `QRIS Pembayaran ${formatRupiah(currentNominal)}`,
                        text: `Scan QRIS berikut untuk bayar ${formatRupiah(currentNominal)}${currentNote ? ' (' + currentNote + ')' : ''} ke ${mName}`,
                        files: [file]
                    };

                    try {
                        if (navigator.canShare && navigator.canShare({ files: [file] })) {
                            await navigator.share(shareData);
                        } else {
                            await navigator.share({
                                title: shareData.title,
                                text: shareData.text
                            });
                        }
                    } catch (err) {
                        // Ignore share cancel
                    }
                }, 'image/png');
            });
        });
    }
});
</script>
@endpush
