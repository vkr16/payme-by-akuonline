@extends('layouts.app')

@section('title', 'Kelola Rekening & QRIS - PayMe')
@section('meta_description', 'Kelola daftar QRIS statis dan rekening bank atau e-wallet Anda untuk tagihan patungan PayMe.')

@section('styles')
<style>
    .tactile-btn {
        touch-action: manipulation;
        user-select: none;
    }
    .tactile-btn:active {
        transform: scale(0.97);
    }
    /* Modal backdrop fade */
    .modal-backdrop {
        transition: opacity 0.2s ease-out;
    }
</style>
@endsection

@section('content')
<div class="space-y-6 pb-20">

    <!-- Top Navigation & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-emerald-800 transition-colors">
                    <i class="fa-light fa-arrow-left text-xs"></i>
                    <span>Dashboard</span>
                </a>
                <span class="text-zinc-300">&bull;</span>
                <span class="text-xs font-medium text-zinc-400">Metode Pembayaran</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-zinc-900 tracking-tight">Rekening & QRIS Tersimpan</h1>
            <p class="text-xs sm:text-sm text-zinc-500">
                Kelola rekening bank, e-wallet, dan QRIS statis untuk menerima pembayaran patungan dari teman-teman Anda.
            </p>
        </div>

        <div class="flex items-center justify-end gap-2 shrink-0 self-end md:self-center">
            <button type="button" onclick="openAddQrisModal()" class="tactile-btn inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-white border border-zinc-300 hover:border-emerald-700 text-zinc-800 hover:text-emerald-800 shadow-2xs transition-all cursor-pointer">
                <i class="fa-light fa-qrcode text-emerald-700 text-sm"></i>
                <span>+ Tambah QRIS</span>
            </button>
            <button type="button" onclick="openAddBankModal()" class="tactile-btn inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl btn-primary shadow-xs transition-all cursor-pointer">
                <i class="fa-light fa-plus text-xs"></i>
                <span>+ Tambah Rekening</span>
            </button>
        </div>
    </div>

    <!-- Info Banner for Dynamic QRIS -->
    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 text-emerald-950 flex items-start gap-3 shadow-2xs">
        <div class="w-8 h-8 rounded-xl bg-emerald-100/90 text-emerald-800 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fa-light fa-bolt-lightning text-sm"></i>
        </div>
        <div class="text-xs leading-relaxed space-y-1">
            <span class="font-bold block text-emerald-900">Konversi QRIS Dinamis Otomatis</span>
            <p class="text-emerald-800/90">
                QRIS statis Anda (BCA, GoPay, Nobu, ShopeePay, dll.) akan secara otomatis dikonversi oleh PayMe menjadi <strong>QRIS dinamis ber-nominal presisi</strong> saat teman Anda memilih item dan melakukan pembayaran.
            </p>
        </div>
    </div>

    <!-- Main Grid: 2 Columns for QRIS & Banks -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        <!-- ==========================================================
             KOLOM 1: DAFTAR QRIS STATIS
             ========================================================== -->
        <div class="card-solid rounded-2xl p-5 sm:p-6 bg-white border border-zinc-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200/70 flex items-center justify-center text-sm shadow-2xs">
                        <i class="fa-light fa-qrcode"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-zinc-900">QRIS Statis Saya</h2>
                        <p class="text-[11px] text-zinc-400">{{ $qrisList->count() }} QRIS terdaftar</p>
                    </div>
                </div>

                <button type="button" onclick="openAddQrisModal()" class="tactile-btn text-xs font-bold text-emerald-800 hover:text-emerald-900 px-2.5 py-1.5 rounded-lg bg-emerald-50/80 hover:bg-emerald-100/80 transition-colors">
                    + Tambah
                </button>
            </div>

            @if($qrisList->count() > 0)
                <div class="space-y-3" id="qrisCardsContainer">
                    @foreach($qrisList as $qris)
                        <div class="p-4 rounded-xl bg-zinc-50/70 hover:bg-zinc-50 border {{ $qris->is_default ? 'border-emerald-300 ring-1 ring-emerald-200/60 bg-emerald-50/20' : 'border-zinc-200/90' }} transition-all space-y-3" id="qris-card-{{ $qris->id }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-zinc-900 text-sm">
                                            {{ $qris->merchant_name ?: 'Merchant QRIS' }}
                                        </span>
                                        @if($qris->is_default)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" id="default-badge-qris-{{ $qris->id }}">
                                                <i class="fa-light fa-star text-[10px]"></i>
                                                <span>QRIS Utama</span>
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-zinc-500 flex items-center gap-1.5">
                                        <i class="fa-light fa-location-dot text-[11px] text-zinc-400"></i>
                                        <span>{{ $qris->merchant_city ?: 'Indonesia' }}</span>
                                    </p>
                                </div>

                                <button type="button" onclick="previewQris({{ json_encode($qris) }})" class="tactile-btn px-2.5 py-1.5 rounded-lg bg-white border border-zinc-200 hover:border-emerald-600 text-zinc-700 hover:text-emerald-800 text-xs font-semibold shadow-2xs transition-colors flex items-center gap-1.5">
                                    <i class="fa-light fa-eye text-xs"></i>
                                    <span>Lihat QR</span>
                                </button>
                            </div>

                            <!-- Action Buttons Footer -->
                            <div class="pt-2 border-t border-zinc-200/70 flex items-center justify-between text-xs">
                                <div>
                                    @if(!$qris->is_default)
                                        <button type="button" onclick="setDefaultQris({{ $qris->id }})" class="font-semibold text-emerald-800 hover:text-emerald-950 hover:underline flex items-center gap-1">
                                            <i class="fa-light fa-check-circle text-[11px]"></i>
                                            <span>Jadikan Utama</span>
                                        </button>
                                    @else
                                        <span class="text-[11px] text-zinc-400 italic">Default untuk bill baru</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="openEditQrisModal({{ json_encode($qris) }})" class="text-zinc-500 hover:text-zinc-900 font-medium transition-colors flex items-center gap-1">
                                        <i class="fa-light fa-pen text-[11px]"></i>
                                        <span>Edit</span>
                                    </button>
                                    <button type="button" onclick="deleteQris({{ $qris->id }}, '{{ addslashes($qris->merchant_name) }}')" class="text-zinc-400 hover:text-rose-600 font-medium transition-colors flex items-center gap-1">
                                        <i class="fa-light fa-trash-can text-[11px]"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State QRIS -->
                <div class="py-10 px-4 text-center rounded-xl bg-zinc-50 border border-dashed border-zinc-200 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-zinc-100 text-zinc-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-light fa-qrcode"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-xs sm:text-sm font-bold text-zinc-700">Belum Ada QRIS Tersimpan</h3>
                        <p class="text-[11px] text-zinc-500 max-w-xs mx-auto">
                            Unggah QRIS statis toko/usaha Anda sekali saja agar tidak perlu scan ulang setiap kali membuat tagihan patungan.
                        </p>
                    </div>
                    <button type="button" onclick="openAddQrisModal()" class="tactile-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg btn-primary shadow-xs">
                        <i class="fa-light fa-plus text-xs"></i>
                        <span>Unggah QRIS Pertama</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- ==========================================================
             KOLOM 2: DAFTAR REKENING BANK & E-WALLET
             ========================================================== -->
        <div class="card-solid rounded-2xl p-5 sm:p-6 bg-white border border-zinc-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200/70 flex items-center justify-center text-sm shadow-2xs">
                        <i class="fa-light fa-building-columns"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-zinc-900">Rekening Bank & E-Wallet</h2>
                        <p class="text-[11px] text-zinc-400">{{ $bankList->count() }} rekening terdaftar</p>
                    </div>
                </div>

                <button type="button" onclick="openAddBankModal()" class="tactile-btn text-xs font-bold text-emerald-800 hover:text-emerald-900 px-2.5 py-1.5 rounded-lg bg-emerald-50/80 hover:bg-emerald-100/80 transition-colors">
                    + Tambah
                </button>
            </div>

            @if($bankList->count() > 0)
                <div class="space-y-3" id="bankCardsContainer">
                    @foreach($bankList as $bank)
                        <div class="p-4 rounded-xl bg-zinc-50/70 hover:bg-zinc-50 border {{ $bank->is_default ? 'border-emerald-300 ring-1 ring-emerald-200/60 bg-emerald-50/20' : 'border-zinc-200/90' }} transition-all space-y-3" id="bank-card-{{ $bank->id }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-zinc-200/80 text-zinc-800">
                                            {{ $bank->bank_name }}
                                        </span>
                                        @if($bank->is_default)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" id="default-badge-bank-{{ $bank->id }}">
                                                <i class="fa-light fa-star text-[10px]"></i>
                                                <span>Rekening Utama</span>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 pt-0.5">
                                        <span class="font-mono text-sm sm:text-base font-bold text-zinc-900 tracking-wider tabular-nums">
                                            {{ $bank->account_number }}
                                        </span>
                                        <button type="button" onclick="copyToClipboard('{{ $bank->account_number }}', 'Nomor rekening {{ $bank->bank_name }}')" class="text-zinc-400 hover:text-emerald-800 p-1 rounded hover:bg-zinc-200/50 transition-colors cursor-pointer" title="Salin nomor rekening">
                                            <i class="fa-light fa-copy text-xs"></i>
                                        </button>
                                    </div>

                                    <p class="text-xs text-zinc-500">
                                        a.n. <strong class="text-zinc-800">{{ $bank->account_holder }}</strong>
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons Footer -->
                            <div class="pt-2 border-t border-zinc-200/70 flex items-center justify-between text-xs">
                                <div>
                                    @if(!$bank->is_default)
                                        <button type="button" onclick="setDefaultBank({{ $bank->id }})" class="font-semibold text-emerald-800 hover:text-emerald-950 hover:underline flex items-center gap-1 cursor-pointer">
                                            <i class="fa-light fa-check-circle text-[11px]"></i>
                                            <span>Jadikan Utama</span>
                                        </button>
                                    @else
                                        <span class="text-[11px] text-zinc-400 italic">Default untuk bill baru</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="openEditBankModal({{ json_encode($bank) }})" class="text-zinc-500 hover:text-zinc-900 font-medium transition-colors flex items-center gap-1 cursor-pointer">
                                        <i class="fa-light fa-pen text-[11px]"></i>
                                        <span>Edit</span>
                                    </button>
                                    <button type="button" onclick="deleteBank({{ $bank->id }}, '{{ addslashes($bank->bank_name) }} ({{ addslashes($bank->account_number) }})')" class="text-zinc-400 hover:text-rose-600 font-medium transition-colors flex items-center gap-1 cursor-pointer">
                                        <i class="fa-light fa-trash-can text-[11px]"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State Bank -->
                <div class="py-10 px-4 text-center rounded-xl bg-zinc-50 border border-dashed border-zinc-200 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-zinc-100 text-zinc-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-light fa-building-columns"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-xs sm:text-sm font-bold text-zinc-700">Belum Ada Rekening Tersimpan</h3>
                        <p class="text-[11px] text-zinc-500 max-w-xs mx-auto">
                            Simpan nomor rekening Bank atau e-wallet (BCA, Mandiri, GoPay, DANA) agar teman patungan bisa langsung transfer ke rekening Anda.
                        </p>
                    </div>
                    <button type="button" onclick="openAddBankModal()" class="tactile-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg btn-primary shadow-xs cursor-pointer">
                        <i class="fa-light fa-plus text-xs"></i>
                        <span>Tambah Rekening Pertama</span>
                    </button>
                </div>
            @endif
        </div>

    </div>

</div>

<!-- =========================================================================
     MODAL 1: TAMBAH QRIS STATIS (Scan File / String Manual)
     ========================================================================= -->
<div id="modalAddQris" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs hidden modal-backdrop">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl border border-zinc-200 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center text-xs font-bold">
                    <i class="fa-light fa-qrcode"></i>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-zinc-900">Tambah QRIS Statis</h3>
            </div>
            <button type="button" onclick="closeModal('modalAddQris')" class="text-zinc-400 hover:text-zinc-700 p-1 cursor-pointer">
                <i class="fa-light fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formAddQris" onsubmit="submitAddQris(event)" class="space-y-4">
            <!-- Upload Box with jsQR -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-zinc-700">Unggah Gambar QRIS Statis</label>
                <div class="border-2 border-dashed border-zinc-200 hover:border-emerald-600 rounded-xl p-4 text-center cursor-pointer bg-zinc-50/50 transition-colors relative" onclick="document.getElementById('addQrisFileInput').click()">
                    <input type="file" id="addQrisFileInput" accept="image/*" class="hidden">
                    <div class="space-y-1">
                        <i class="fa-light fa-cloud-arrow-up text-2xl text-emerald-700"></i>
                        <p class="text-xs font-semibold text-zinc-700">Pilih atau Seret Foto QRIS</p>
                        <p class="text-[10px] text-zinc-400">Format PNG/JPG/WebP, max 10MB</p>
                    </div>
                </div>
                <div id="addQrisDetectStatus" class="hidden p-2 rounded-lg text-xs"></div>
            </div>

            <!-- Payload QRIS Textarea -->
            <div class="space-y-1">
                <label for="addQrisPayload" class="block text-xs font-bold text-zinc-700">
                    Kode Payload QRIS (EMVCo) <span class="text-rose-500">*</span>
                </label>
                <textarea id="addQrisPayload" name="payload" required rows="3" placeholder="00020101021126580014ID.CO.QRIS.WWW..." class="w-full text-xs font-mono bg-zinc-50 border border-zinc-300 rounded-xl p-2.5 text-zinc-900 focus:outline-none focus:border-emerald-700 break-all"></textarea>
                <p class="text-[10px] text-zinc-400">Otomatis terisi jika Anda memilih gambar QR di atas, atau bisa di-paste manual.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label for="addQrisMerchantName" class="block text-xs font-bold text-zinc-700">Nama Merchant (Opsional)</label>
                    <input type="text" id="addQrisMerchantName" name="merchant_name" placeholder="Contoh: Kopi Kenangan" class="w-full text-xs bg-white border border-zinc-300 rounded-xl px-3 py-2 text-zinc-900 focus:outline-none focus:border-emerald-700">
                </div>
                <div class="space-y-1">
                    <label for="addQrisMerchantCity" class="block text-xs font-bold text-zinc-700">Kota Merchant (Opsional)</label>
                    <input type="text" id="addQrisMerchantCity" name="merchant_city" placeholder="Contoh: JAKARTA SELATAN" class="w-full text-xs bg-white border border-zinc-300 rounded-xl px-3 py-2 text-zinc-900 focus:outline-none focus:border-emerald-700">
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer select-none pt-1">
                <input type="checkbox" id="addQrisIsDefault" name="is_default" value="1" class="w-4 h-4 rounded border-zinc-300 text-emerald-800 focus:ring-emerald-700 accent-emerald-800 cursor-pointer">
                <span class="text-xs text-zinc-700 font-medium">Jadikan sebagai QRIS Utama akun</span>
            </label>

            <div class="pt-3 border-t border-zinc-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalAddQris')" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnAddQrisSubmit" class="tactile-btn px-5 py-2 rounded-xl text-xs font-bold btn-primary shadow-xs transition-all cursor-pointer">
                    Simpan QRIS
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL 2: EDIT QRIS
     ========================================================================= -->
<div id="modalEditQris" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs hidden modal-backdrop">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl border border-zinc-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
            <h3 class="text-sm sm:text-base font-bold text-zinc-900">Edit Data QRIS</h3>
            <button type="button" onclick="closeModal('modalEditQris')" class="text-zinc-400 hover:text-zinc-700 p-1 cursor-pointer">
                <i class="fa-light fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditQris" onsubmit="submitEditQris(event)" class="space-y-4">
            <input type="hidden" id="editQrisId">

            <div class="space-y-1">
                <label for="editQrisMerchantName" class="block text-xs font-bold text-zinc-700">
                    Nama Merchant <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="editQrisMerchantName" required class="w-full text-xs bg-white border border-zinc-300 rounded-xl px-3 py-2 text-zinc-900 focus:outline-none focus:border-emerald-700">
            </div>

            <div class="space-y-1">
                <label for="editQrisMerchantCity" class="block text-xs font-bold text-zinc-700">
                    Kota Merchant
                </label>
                <input type="text" id="editQrisMerchantCity" class="w-full text-xs bg-white border border-zinc-300 rounded-xl px-3 py-2 text-zinc-900 focus:outline-none focus:border-emerald-700">
            </div>

            <div class="pt-3 border-t border-zinc-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditQris')" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnEditQrisSubmit" class="tactile-btn px-5 py-2 rounded-xl text-xs font-bold btn-primary shadow-xs transition-all cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL 3: PREVIEW QRIS (QRCode.js rendering)
     ========================================================================= -->
<div id="modalPreviewQris" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs hidden modal-backdrop">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-zinc-200 text-center space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
            <h3 class="text-sm font-bold text-zinc-900 text-left">Preview QRIS</h3>
            <button type="button" onclick="closeModal('modalPreviewQris')" class="text-zinc-400 hover:text-zinc-700 p-1 cursor-pointer">
                <i class="fa-light fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="space-y-1">
            <h4 class="font-bold text-base text-zinc-900" id="previewMerchantName">Merchant</h4>
            <p class="text-xs text-zinc-500" id="previewMerchantCity">Kota</p>
        </div>

        <!-- QR Code Canvas Container -->
        <div class="flex justify-center p-3 bg-white rounded-xl border border-zinc-200 shadow-2xs inline-block mx-auto">
            <div id="previewQrContainer" class="flex items-center justify-center"></div>
        </div>

        <div class="space-y-2">
            <button type="button" onclick="copyQrisPayload()" class="tactile-btn w-full py-2 px-3 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                <i class="fa-light fa-copy text-xs"></i>
                <span>Salin Payload String EMVCo</span>
            </button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 4: TAMBAH REKENING BANK / E-WALLET (With Fast Presets)
     ========================================================================= -->
<div id="modalAddBank" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs hidden modal-backdrop">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl border border-zinc-200 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center text-xs font-bold">
                    <i class="fa-light fa-building-columns"></i>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-zinc-900">Tambah Rekening / E-Wallet</h3>
            </div>
            <button type="button" onclick="closeModal('modalAddBank')" class="text-zinc-400 hover:text-zinc-700 p-1 cursor-pointer">
                <i class="fa-light fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formAddBank" onsubmit="submitAddBank(event)" class="space-y-4">
            <!-- Preset Bank / E-Wallet Quick Selection -->
            <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-zinc-600 uppercase tracking-wider">Pilih Cepat Bank / E-Wallet</label>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(['BCA', 'Mandiri', 'BRI', 'BNI', 'BSI', 'CIMB Niaga', 'Bank Jago', 'SeaBank', 'Blu', 'DANA', 'GoPay', 'OVO', 'ShopeePay'] as $preset)
                        <button type="button" onclick="selectBankPreset('{{ $preset }}')" class="tactile-btn px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-zinc-100 hover:bg-emerald-50 hover:text-emerald-800 text-zinc-700 border border-zinc-200/70 transition-colors cursor-pointer">
                            {{ $preset }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="space-y-1">
                <label for="addBankName" class="block text-xs font-bold text-zinc-700">
                    Nama Bank / E-Wallet <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="addBankName" name="bank_name" required placeholder="Contoh: BCA, Bank Mandiri, GoPay" class="w-full text-xs bg-white border border-zinc-300 rounded-xl px-3 py-2.5 text-zinc-900 focus:outline-none focus:border-emerald-700">
            </div>

            <div class="space-y-1">
                <label for="addAccountNumber" class="block text-xs font-bold text-zinc-700">
                    Nomor Rekening / No HP E-Wallet <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="addAccountNumber" name="account_number" required placeholder="Contoh: 1234567890 / 081234567890" class="w-full text-xs font-mono font-medium bg-white border border-zinc-300 rounded-xl px-3 py-2.5 text-zinc-900 focus:outline-none focus:border-emerald-700 tabular-nums">
            </div>

            <div class="space-y-1">
                <label for="addAccountHolder" class="block text-xs font-bold text-zinc-700">
                    Nama Pemilik Rekening (Atas Nama) <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="addAccountHolder" name="account_holder" required value="{{ $user->name }}" placeholder="Nama sesuai rekening / akun e-wallet" class="w-full text-xs bg-white border border-zinc-300 rounded-xl px-3 py-2.5 text-zinc-900 focus:outline-none focus:border-emerald-700">
            </div>

            <label class="flex items-center gap-2 cursor-pointer select-none pt-1">
                <input type="checkbox" id="addBankIsDefault" name="is_default" value="1" class="w-4 h-4 rounded border-zinc-300 text-emerald-800 focus:ring-emerald-700 accent-emerald-800 cursor-pointer">
                <span class="text-xs text-zinc-700 font-medium">Jadikan sebagai Rekening Utama</span>
            </label>

            <div class="pt-3 border-t border-zinc-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalAddBank')" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnAddBankSubmit" class="tactile-btn px-5 py-2 rounded-xl text-xs font-bold btn-primary shadow-xs transition-all cursor-pointer">
                    Simpan Rekening
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL 5: EDIT REKENING BANK
     ========================================================================= -->
<div id="modalEditBank" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs hidden modal-backdrop">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl border border-zinc-200 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
            <h3 class="text-sm sm:text-base font-bold text-zinc-900">Edit Rekening Bank</h3>
            <button type="button" onclick="closeModal('modalEditBank')" class="text-zinc-400 hover:text-zinc-700 p-1 cursor-pointer">
                <i class="fa-light fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditBank" onsubmit="submitEditBank(event)" class="space-y-4">
            <input type="hidden" id="editBankId">

            <div class="space-y-1">
                <label for="editBankName" class="block text-xs font-bold text-zinc-700">
                    Nama Bank / E-Wallet <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="editBankName" required class="w-full text-xs bg-white border border-zinc-300 rounded-xl px-3 py-2 text-zinc-900 focus:outline-none focus:border-emerald-700">
            </div>

            <div class="space-y-1">
                <label for="editAccountNumber" class="block text-xs font-bold text-zinc-700">
                    Nomor Rekening <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="editAccountNumber" required class="w-full text-xs font-mono font-medium bg-white border border-zinc-300 rounded-xl px-3 py-2 text-zinc-900 focus:outline-none focus:border-emerald-700 tabular-nums">
            </div>

            <div class="space-y-1">
                <label for="editAccountHolder" class="block text-xs font-bold text-zinc-700">
                    Atas Nama <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="editAccountHolder" required class="w-full text-xs bg-white border border-zinc-300 rounded-xl px-3 py-2 text-zinc-900 focus:outline-none focus:border-emerald-700">
            </div>

            <div class="pt-3 border-t border-zinc-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditBank')" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnEditBankSubmit" class="tactile-btn px-5 py-2 rounded-xl text-xs font-bold btn-primary shadow-xs transition-all cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Canvas for jsQR Image Processing -->
<canvas id="qrCanvas" class="hidden"></canvas>

@endsection

@section('scripts')
<script src="{{ asset('vendor/jsqr/jsQR.min.js') }}"></script>
<script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentPreviewPayload = '';

    // =========================================================================
    // MODAL HELPERS
    // =========================================================================
    window.openModal = function (modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    };

    window.closeModal = function (modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Close modal when clicking on backdrop
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });

    // Copy to clipboard helper
    window.copyToClipboard = function (text, label = 'Teks') {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                if (window.Notiflix) {
                    Notiflix.Notify.success(`${label} berhasil disalin!`);
                }
            }).catch(() => {
                fallbackCopy(text, label);
            });
        } else {
            fallbackCopy(text, label);
        }
    };

    function fallbackCopy(text, label) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            if (window.Notiflix) Notiflix.Notify.success(`${label} berhasil disalin!`);
        } catch (err) {
            if (window.Notiflix) Notiflix.Notify.failure('Gagal menyalin ke clipboard.');
        }
        document.body.removeChild(textArea);
    }

    // =========================================================================
    // QRIS MANAGEMENT (Client-side jsQR + AJAX)
    // =========================================================================
    const addQrisFileInput = document.getElementById('addQrisFileInput');
    const addQrisPayload = document.getElementById('addQrisPayload');
    const addQrisDetectStatus = document.getElementById('addQrisDetectStatus');
    const addQrisMerchantName = document.getElementById('addQrisMerchantName');
    const addQrisMerchantCity = document.getElementById('addQrisMerchantCity');

    window.openAddQrisModal = function () {
        document.getElementById('formAddQris').reset();
        addQrisDetectStatus.className = 'hidden p-2 rounded-lg text-xs';
        addQrisDetectStatus.innerHTML = '';
        openModal('modalAddQris');
    };

    // Standard EMVCo TLV Parser for client-side extraction
    function parseEmvcoTlv(payload) {
        const tags = {};
        let offset = 0;
        const len = payload.length;

        while (offset + 4 <= len) {
            const tag = payload.substring(offset, offset + 2);
            const valLenStr = payload.substring(offset + 2, offset + 4);
            const valLen = parseInt(valLenStr, 10);

            if (isNaN(valLen) || valLen < 0 || !/^\d{2}$/.test(valLenStr)) {
                break;
            }

            offset += 4;
            if (offset + valLen > len) {
                tags[tag] = payload.substring(offset);
                break;
            }

            tags[tag] = payload.substring(offset, offset + valLen);
            offset += valLen;
        }

        return tags;
    }

    function extractMerchantInfoClient(payload) {
        let merchantName = '';
        let merchantCity = '';

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

                if (tag === '59') {
                    merchantName = val.trim();
                } else if (tag === '60') {
                    merchantCity = val.trim();
                } else if (tag === '63') {
                    break;
                }
            }
        }

        // Fallback to full TLV parse
        if (!merchantName || !merchantCity) {
            const tlv = parseEmvcoTlv(payload);
            if (!merchantName && tlv['59']) merchantName = tlv['59'].trim();
            if (!merchantCity && tlv['60']) merchantCity = tlv['60'].trim();
        }

        return { merchantName, merchantCity };
    }

    function autoFillMerchantFromPayload(payload) {
        const info = extractMerchantInfoClient(payload);
        if (info.merchantName && !addQrisMerchantName.value) {
            addQrisMerchantName.value = info.merchantName;
        }
        if (info.merchantCity && !addQrisMerchantCity.value) {
            addQrisMerchantCity.value = info.merchantCity;
        }
    }

    // Auto-parse when user pastes or types payload manually
    if (addQrisPayload) {
        addQrisPayload.addEventListener('input', function () {
            const val = this.value.trim();
            if (val.startsWith('000201') && val.length >= 30) {
                autoFillMerchantFromPayload(val);
            }
        });
    }

    if (addQrisFileInput) {
        addQrisFileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            addQrisDetectStatus.className = 'p-2 rounded-lg text-xs flex items-center gap-2 bg-zinc-100 text-zinc-700';
            addQrisDetectStatus.innerHTML = '<div class="w-3 h-3 border-2 border-zinc-400 border-t-zinc-800 rounded-full animate-spin"></div><span>Membaca QRIS statis dari gambar...</span>';
            addQrisDetectStatus.classList.remove('hidden');

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
                            addQrisPayload.value = payload;
                            addQrisDetectStatus.className = 'p-2 rounded-lg text-xs flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200';
                            addQrisDetectStatus.innerHTML = '<i class="fa-light fa-circle-check text-emerald-600"></i><span>QRIS Statis Valid terdeteksi!</span>';

                            // Accurate EMVCo TLV Extraction
                            try {
                                autoFillMerchantFromPayload(payload);
                            } catch (e) {}
                        } else {
                            addQrisPayload.value = '';
                            addQrisDetectStatus.className = 'p-2 rounded-lg text-xs flex items-center gap-2 bg-amber-50 text-amber-900 border border-amber-200';
                            addQrisDetectStatus.innerHTML = '<i class="fa-light fa-circle-exclamation text-amber-600"></i><span>QR Code terdeteksi bukan standar QRIS EMVCo.</span>';
                        }
                    } else {
                        addQrisPayload.value = '';
                        addQrisDetectStatus.className = 'p-2 rounded-lg text-xs flex items-center gap-2 bg-rose-50 text-rose-800 border border-rose-200';
                        addQrisDetectStatus.innerHTML = '<i class="fa-light fa-circle-xmark text-rose-600"></i><span>Gagal membaca QR Code dari file ini. Pastikan gambar jelas.</span>';
                    }
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // Submit Add QRIS
    window.submitAddQris = async function (e) {
        e.preventDefault();
        const payload = addQrisPayload.value.trim();
        if (!payload) {
            if (window.Notiflix) Notiflix.Notify.failure('Kode payload QRIS wajib diisi.');
            return;
        }

        const btnSubmit = document.getElementById('btnAddQrisSubmit');
        btnSubmit.disabled = true;
        btnSubmit.classList.add('opacity-50');
        if (window.Notiflix) Notiflix.Loading.pulse('Menyimpan QRIS...');

        const formData = new FormData();
        formData.append('payload', payload);
        if (addQrisMerchantName.value) formData.append('merchant_name', addQrisMerchantName.value);
        if (addQrisMerchantCity.value) formData.append('merchant_city', addQrisMerchantCity.value);
        if (document.getElementById('addQrisIsDefault').checked) formData.append('is_default', '1');
        if (addQrisFileInput.files[0]) formData.append('qris_image', addQrisFileInput.files[0]);
        formData.append('_token', csrfToken);

        try {
            const res = await fetch('{{ route("payment_methods.qris.store") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            });

            const data = await res.json();
            if (window.Notiflix) Notiflix.Loading.remove();
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-50');

            if (res.ok && data.success) {
                if (window.Notiflix) Notiflix.Notify.success(data.message);
                closeModal('modalAddQris');
                setTimeout(() => window.location.reload(), 600);
            } else {
                if (window.Notiflix) Notiflix.Notify.failure(data.message || 'Gagal menyimpan QRIS.');
            }
        } catch (err) {
            if (window.Notiflix) Notiflix.Loading.remove();
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-50');
            if (window.Notiflix) Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
        }
    };

    // Open Edit QRIS
    window.openEditQrisModal = function (qris) {
        document.getElementById('editQrisId').value = qris.id;
        document.getElementById('editQrisMerchantName').value = qris.merchant_name || '';
        document.getElementById('editQrisMerchantCity').value = qris.merchant_city || '';
        openModal('modalEditQris');
    };

    // Submit Edit QRIS
    window.submitEditQris = async function (e) {
        e.preventDefault();
        const id = document.getElementById('editQrisId').value;
        const merchantName = document.getElementById('editQrisMerchantName').value.trim();
        const merchantCity = document.getElementById('editQrisMerchantCity').value.trim();

        const btnSubmit = document.getElementById('btnEditQrisSubmit');
        btnSubmit.disabled = true;
        btnSubmit.classList.add('opacity-50');
        if (window.Notiflix) Notiflix.Loading.pulse('Memperbarui QRIS...');

        try {
            const res = await fetch(`/payment-methods/qris/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    merchant_name: merchantName,
                    merchant_city: merchantCity
                })
            });

            const data = await res.json();
            if (window.Notiflix) Notiflix.Loading.remove();
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-50');

            if (res.ok && data.success) {
                if (window.Notiflix) Notiflix.Notify.success(data.message);
                closeModal('modalEditQris');
                setTimeout(() => window.location.reload(), 600);
            } else {
                if (window.Notiflix) Notiflix.Notify.failure(data.message || 'Gagal memperbarui QRIS.');
            }
        } catch (err) {
            if (window.Notiflix) Notiflix.Loading.remove();
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-50');
            if (window.Notiflix) Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
        }
    };

    // Set Default QRIS
    window.setDefaultQris = async function (id) {
        if (window.Notiflix) Notiflix.Loading.pulse('Mengatur QRIS utama...');
        try {
            const res = await fetch(`/payment-methods/qris/${id}/default`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await res.json();
            if (window.Notiflix) Notiflix.Loading.remove();

            if (res.ok && data.success) {
                if (window.Notiflix) Notiflix.Notify.success(data.message);
                setTimeout(() => window.location.reload(), 500);
            } else {
                if (window.Notiflix) Notiflix.Notify.failure(data.message || 'Gagal mengubah QRIS utama.');
            }
        } catch (err) {
            if (window.Notiflix) Notiflix.Loading.remove();
            if (window.Notiflix) Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
        }
    };

    // Delete QRIS
    window.deleteQris = function (id, merchantName) {
        if (window.Notiflix) {
            Notiflix.Confirm.show(
                'Hapus QRIS?',
                `Apakah Anda yakin ingin menghapus QRIS "${merchantName}"?`,
                'Ya, Hapus',
                'Batal',
                async function () {
                    Notiflix.Loading.pulse('Menghapus QRIS...');
                    try {
                        const res = await fetch(`/payment-methods/qris/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const data = await res.json();
                        Notiflix.Loading.remove();

                        if (res.ok && data.success) {
                            Notiflix.Notify.success(data.message);
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            Notiflix.Notify.failure(data.message || 'Gagal menghapus QRIS.');
                        }
                    } catch (err) {
                        Notiflix.Loading.remove();
                        Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
                    }
                }
            );
        } else {
            if (confirm(`Hapus QRIS "${merchantName}"?`)) {
                fetch(`/payment-methods/qris/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                }).then(() => window.location.reload());
            }
        }
    };

    // Preview QRIS
    window.previewQris = function (qris) {
        currentPreviewPayload = qris.payload;
        document.getElementById('previewMerchantName').textContent = qris.merchant_name || 'Merchant QRIS';
        document.getElementById('previewMerchantCity').textContent = qris.merchant_city || 'Indonesia';

        const container = document.getElementById('previewQrContainer');
        container.innerHTML = '';

        new QRCode(container, {
            text: qris.payload,
            width: 220,
            height: 220,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });

        openModal('modalPreviewQris');
    };

    window.copyQrisPayload = function () {
        if (currentPreviewPayload) {
            copyToClipboard(currentPreviewPayload, 'Payload EMVCo QRIS');
        }
    };

    // =========================================================================
    // BANK ACCOUNT MANAGEMENT (Presets + AJAX)
    // =========================================================================
    window.openAddBankModal = function () {
        document.getElementById('formAddBank').reset();
        document.getElementById('addAccountHolder').value = '{{ addslashes($user->name) }}';
        openModal('modalAddBank');
    };

    window.selectBankPreset = function (bankName) {
        document.getElementById('addBankName').value = bankName;
        document.getElementById('addAccountNumber').focus();
    };

    // Submit Add Bank
    window.submitAddBank = async function (e) {
        e.preventDefault();
        const bankName = document.getElementById('addBankName').value.trim();
        const accountNumber = document.getElementById('addAccountNumber').value.trim();
        const accountHolder = document.getElementById('addAccountHolder').value.trim();
        const isDefault = document.getElementById('addBankIsDefault').checked ? 1 : 0;

        const btnSubmit = document.getElementById('btnAddBankSubmit');
        btnSubmit.disabled = true;
        btnSubmit.classList.add('opacity-50');
        if (window.Notiflix) Notiflix.Loading.pulse('Menyimpan rekening...');

        try {
            const res = await fetch('{{ route("payment_methods.banks.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    bank_name: bankName,
                    account_number: accountNumber,
                    account_holder: accountHolder,
                    is_default: isDefault
                })
            });

            const data = await res.json();
            if (window.Notiflix) Notiflix.Loading.remove();
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-50');

            if (res.ok && data.success) {
                if (window.Notiflix) Notiflix.Notify.success(data.message);
                closeModal('modalAddBank');
                setTimeout(() => window.location.reload(), 500);
            } else {
                if (window.Notiflix) Notiflix.Notify.failure(data.message || 'Gagal menyimpan rekening.');
            }
        } catch (err) {
            if (window.Notiflix) Notiflix.Loading.remove();
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-50');
            if (window.Notiflix) Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
        }
    };

    // Open Edit Bank
    window.openEditBankModal = function (bank) {
        document.getElementById('editBankId').value = bank.id;
        document.getElementById('editBankName').value = bank.bank_name || '';
        document.getElementById('editAccountNumber').value = bank.account_number || '';
        document.getElementById('editAccountHolder').value = bank.account_holder || '';
        openModal('modalEditBank');
    };

    // Submit Edit Bank
    window.submitEditBank = async function (e) {
        e.preventDefault();
        const id = document.getElementById('editBankId').value;
        const bankName = document.getElementById('editBankName').value.trim();
        const accountNumber = document.getElementById('editAccountNumber').value.trim();
        const accountHolder = document.getElementById('editAccountHolder').value.trim();

        const btnSubmit = document.getElementById('btnEditBankSubmit');
        btnSubmit.disabled = true;
        btnSubmit.classList.add('opacity-50');
        if (window.Notiflix) Notiflix.Loading.pulse('Memperbarui rekening...');

        try {
            const res = await fetch(`/payment-methods/banks/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    bank_name: bankName,
                    account_number: accountNumber,
                    account_holder: accountHolder
                })
            });

            const data = await res.json();
            if (window.Notiflix) Notiflix.Loading.remove();
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-50');

            if (res.ok && data.success) {
                if (window.Notiflix) Notiflix.Notify.success(data.message);
                closeModal('modalEditBank');
                setTimeout(() => window.location.reload(), 500);
            } else {
                if (window.Notiflix) Notiflix.Notify.failure(data.message || 'Gagal memperbarui rekening.');
            }
        } catch (err) {
            if (window.Notiflix) Notiflix.Loading.remove();
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-50');
            if (window.Notiflix) Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
        }
    };

    // Set Default Bank
    window.setDefaultBank = async function (id) {
        if (window.Notiflix) Notiflix.Loading.pulse('Mengatur rekening utama...');
        try {
            const res = await fetch(`/payment-methods/banks/${id}/default`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await res.json();
            if (window.Notiflix) Notiflix.Loading.remove();

            if (res.ok && data.success) {
                if (window.Notiflix) Notiflix.Notify.success(data.message);
                setTimeout(() => window.location.reload(), 500);
            } else {
                if (window.Notiflix) Notiflix.Notify.failure(data.message || 'Gagal mengubah rekening utama.');
            }
        } catch (err) {
            if (window.Notiflix) Notiflix.Loading.remove();
            if (window.Notiflix) Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
        }
    };

    // Delete Bank
    window.deleteBank = function (id, bankLabel) {
        if (window.Notiflix) {
            Notiflix.Confirm.show(
                'Hapus Rekening?',
                `Apakah Anda yakin ingin menghapus rekening ${bankLabel}?`,
                'Ya, Hapus',
                'Batal',
                async function () {
                    Notiflix.Loading.pulse('Menghapus rekening...');
                    try {
                        const res = await fetch(`/payment-methods/banks/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const data = await res.json();
                        Notiflix.Loading.remove();

                        if (res.ok && data.success) {
                            Notiflix.Notify.success(data.message);
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            Notiflix.Notify.failure(data.message || 'Gagal menghapus rekening.');
                        }
                    } catch (err) {
                        Notiflix.Loading.remove();
                        Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
                    }
                }
            );
        } else {
            if (confirm(`Hapus rekening ${bankLabel}?`)) {
                fetch(`/payment-methods/banks/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                }).then(() => window.location.reload());
            }
        }
    };

});
</script>
@endsection
