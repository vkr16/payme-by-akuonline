@extends('layouts.app')

@section('title', 'Bayar Patungan ' . $bill->title . ' - PayMe')
@section('meta_description', 'Pilih menu pesanan dan bayar patungan ' . $bill->title . ' (Ditalangi oleh ' . $bill->user->name . ') via QRIS Dinamis atau Transfer Bank.')

@section('styles')
<style>
    .stepper-btn {
        touch-action: manipulation;
        user-select: none;
    }
    .stepper-btn:active {
        transform: scale(0.92);
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-28">

    <!-- Host Notification Banner (If viewed by Host) -->
    @if($isHost)
        @php
            $pendingCount = $bill->claims->where('status', 'pending')->count();
        @endphp
        <div class="p-4 rounded-2xl bg-emerald-800 text-white shadow-sm flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 text-xs sm:text-sm">
                <div class="w-8 h-8 rounded-lg bg-emerald-700/80 flex items-center justify-center flex-shrink-0">
                    <i class="fa-light fa-crown text-slate-50 text-sm"></i>
                </div>
                <div>
                    <span class="font-bold block">Mode Host (Penagih)</span>
                    <span class="text-[11px] text-slate-50">Kamu adalah pembuat tagihan ini. Pantau & konfirmasi klaim pembayaran teman di bawah.</span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" id="btnHostBannerBatchConfirm" class="{{ $pendingCount > 0 ? 'flex' : 'hidden' }} px-2.5 py-1.5 rounded-lg bg-amber-400 hover:bg-amber-300 text-amber-950 font-bold text-xs transition-colors items-center gap-1.5 shadow-2xs cursor-pointer" style="{{ $pendingCount > 0 ? '' : 'display: none !important;' }}" title="Konfirmasi sekaligus klaim yang menunggu">
                    <i class="fa-light fa-check-double text-xs"></i>
                    <span><span id="bannerPendingCount">{{ $pendingCount }}</span> Menunggu</span>
                </button>
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-600 text-white font-semibold text-xs transition-colors">
                    Dashboard
                </a>
            </div>
        </div>
    @endif

    <!-- Post-Transaction Success Alert Card (Compact) -->
    <div id="postTransactionAlert" class="hidden mb-6 p-3.5 sm:p-4 rounded-2xl bg-emerald-50/90 border border-emerald-200/90 shadow-2xs transition-all animate-in fade-in">
        <div class="flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center flex-shrink-0">
                    <i class="fa-light fa-circle-check text-emerald-600 text-lg"></i>
                </div>
                <div class="min-w-0 space-y-0.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-zinc-900" id="postTxSuccessTitle">Klaim Pembayaran Dicatat! 🎉</span>
                        <span class="inline-flex items-center text-[10px] font-semibold text-emerald-800 bg-emerald-100/80 px-2 py-0.5 rounded-full border border-emerald-200/60">
                            Menunggu Konfirmasi
                        </span>
                    </div>
                    <p class="text-xs text-zinc-600 truncate sm:whitespace-normal" id="postTxSuccessDesc">
                        Klaim pembayaranmu telah dicatat. Suka PayMe? Yuk traktir kopi mas dev!
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0 ml-auto sm:ml-0">
                <button type="button" id="btnOpenCoffeeModalFromAlert" class="touch-target inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-800 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition-all cursor-pointer">
                    <i class="fa-light fa-mug-hot text-slate-50"></i>
                    <span>Traktir Kopi</span>
                </button>
                <button type="button" id="btnDismissPostTxAlert" class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200/50 transition-colors cursor-pointer" title="Tutup">
                    <i class="fa-light fa-xmark text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Bill Header Card -->
    <div class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm text-center relative space-y-4">
        <div class="flex items-center justify-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                <i class="fa-light fa-user-circle text-emerald-700"></i>
                <span>Ditalangi oleh: <strong>{{ $bill->user->name }}</strong></span>
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600 border border-zinc-200">
                <i class="fa-light fa-calendar text-[11px]"></i>
                <span>{{ $bill->created_at->translatedFormat('d M Y') }}</span>
            </span>

            <span id="billSettledBadge" class="{{ $bill->isFullySettled() ? 'inline-flex' : 'hidden' }} items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-emerald-500 text-white shadow-2xs" style="{{ $bill->isFullySettled() ? '' : 'display: none !important;' }}">
                <i class="fa-light fa-badge-check"></i>
                <span>LUNAS TERVERIFIKASI</span>
            </span>
        </div>

        <div>
            <h1 class="text-xl sm:text-2xl font-black text-zinc-900 tracking-tight">{{ $bill->title }}</h1>
            @if($bill->qris_merchant_name)
                <p class="text-xs text-zinc-500 mt-1 flex items-center justify-center gap-1.5">
                    <i class="fa-light fa-store text-emerald-700"></i>
                    <span>Merchant: <strong>{{ $bill->qris_merchant_name }}</strong> @if($bill->qris_merchant_city)({{ $bill->qris_merchant_city }})@endif</span>
                </p>
            @endif
        </div>

        <!-- Target Grand Total & Progress Bar -->
        <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-200/80 max-w-sm mx-auto space-y-2">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Total Tagihan Patungan</span>
            <div class="text-2xl sm:text-3xl font-black text-emerald-900 tabular-nums">
                Rp {{ number_format($bill->grand_total, 0, ',', '.') }}
            </div>

            <!-- Progress Terkumpul -->
            <div class="pt-1 space-y-1">
                <div class="flex justify-between text-[11px] font-semibold text-zinc-600">
                    <span>Terkonfirmasi:</span>
                    <span id="billConfirmedProgressText" class="text-emerald-800 tabular-nums font-bold">
                        Rp {{ number_format($bill->total_confirmed_paid, 0, ',', '.') }} ({{ $bill->progress_percentage }}%)
                    </span>
                </div>
                <div class="w-full bg-zinc-200 rounded-full h-2 overflow-hidden">
                    <div id="billProgressBar" class="bg-emerald-600 h-2 rounded-full transition-all duration-500" style="width: {{ $bill->progress_percentage }}%"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] text-zinc-400">
                    <span id="billRemainingAmountText">Sisa: Rp {{ number_format($bill->remaining_confirmed_amount, 0, ',', '.') }}</span>
                    <span id="billTotalTipsText" class="{{ $bill->total_confirmed_tips > 0 ? '' : 'hidden ' }}text-emerald-700 font-semibold inline-flex items-center gap-1">
                        <i class="fa-light fa-gift text-[9px]"></i> Tip: Rp {{ number_format($bill->total_confirmed_tips, 0, ',', '.') }}
                    </span>
                    <span>{{ $bill->items->sum('qty') }} item total</span>
                </div>
            </div>
        </div>

        <!-- Share Actions -->
        <div class="pt-1 flex flex-col sm:flex-row items-center justify-center gap-2.5">
            <button type="button" id="btnCopyLink" class="touch-target w-full sm:w-auto px-4 py-2 rounded-xl bg-white hover:bg-zinc-50 text-zinc-800 border border-zinc-300 font-semibold text-xs shadow-2xs inline-flex items-center justify-center gap-2 transition-all cursor-pointer">
                <i class="fa-light fa-copy text-xs text-emerald-800" id="copyIcon"></i>
                <span id="copyText">Salin Tautan</span>
            </button>

            @php
                $shareUrl = url('/b/' . $bill->slug);
                $waText = urlencode("Halo teman-teman! Ini link patungan \"{$bill->title}\" (Total: Rp " . number_format($bill->grand_total, 0, ',', '.') . ").\nSilakan pilih item yang kamu ambil dan bayar via QRIS/Transfer di link ini ya:\n{$shareUrl}");
            @endphp
            <a href="https://api.whatsapp.com/send?text={{ $waText }}" target="_blank" rel="noopener noreferrer" class="touch-target w-full sm:w-auto px-4 py-2 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-semibold text-xs shadow-2xs inline-flex items-center justify-center gap-2 transition-all">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Bagikan WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- ==========================================
         PARTICIPANT ITEM SELECTION (Mekanisme Bayar)
         ========================================== -->
    <div class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-zinc-900 flex items-center gap-2">
                    <i class="fa-light fa-layer-group text-emerald-700"></i>
                    <span>Pilih Bagian Tagihanmu</span>
                </h2>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Tentukan item yang kamu ambil. Biaya tambahan & diskon otomatis dihitung secara proporsional.
                </p>
            </div>

            <!-- Search Menu Input (Sticky/Filter via JS) -->
            <div class="relative w-full sm:w-64 flex-shrink-0">
                <i class="fa-light fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
                <input type="text"
                       id="menuSearchInput"
                       placeholder="Cari nama item/pesanan..."
                       class="w-full pl-8 pr-8 py-2 rounded-xl bg-zinc-50 border border-zinc-200 focus:bg-white focus:border-emerald-700 focus:ring-1 focus:ring-emerald-700 text-xs text-zinc-800 placeholder-zinc-400 transition-all outline-none"
                       autocomplete="off">
                <button type="button"
                        id="btnClearMenuSearch"
                        class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 w-5 h-5 flex items-center justify-center rounded-full hover:bg-zinc-100 transition-colors cursor-pointer"
                        title="Hapus pencarian">
                    <i class="fa-light fa-xmark text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Menu Items List with Stepper & Wrap Resilience -->
        <div class="space-y-2.5" id="participantItemsContainer">
            @foreach($bill->items as $item)
                @php
                    $isSoldOut = $item->remaining_qty <= 0;
                @endphp
                <div class="item-selection-card p-3 sm:p-3.5 rounded-xl border transition-all flex items-center justify-between gap-3 {{ $isSoldOut ? 'bg-zinc-50/60 border-zinc-200/60 opacity-60' : 'bg-white border-zinc-200/80 hover:border-emerald-600/70 shadow-2xs' }} cursor-pointer"
                     data-item-id="{{ $item->id }}"
                     data-name="{{ strtolower($item->name) }}"
                     data-raw-name="{{ $item->name }}"
                     data-price="{{ $item->price }}"
                     data-remaining="{{ $item->remaining_qty }}"
                     data-total="{{ $item->qty }}">
                    <!-- Left: Item Name (wrap resilient) & Status info -->
                    <div class="flex-1 pr-2 min-w-0">
                        <div class="text-xs sm:text-sm font-bold text-zinc-800 leading-snug break-words">
                            {{ $item->name }}
                        </div>
                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                            <span class="text-[11px] text-zinc-500 tabular-nums font-medium">@ Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                            <span class="text-zinc-300 text-[10px]">&bull;</span>
                            @if($isSoldOut)
                                <span class="item-stock-badge text-[10px] px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-500 font-semibold border border-zinc-200/60">
                                    Habis terbayar
                                </span>
                            @else
                                <span class="item-stock-badge text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/50">
                                    sisa {{ $item->remaining_qty }}/{{ $item->qty }} item
                                </span>
                            @endif
                            <span class="text-zinc-300 text-[10px]">&bull;</span>
                            <span class="text-emerald-700 hover:text-emerald-900 text-[10px] font-semibold inline-flex items-center gap-1 hover:underline">
                                <i class="fa-light fa-users text-[9px]"></i>
                                <span>Lihat pembayar</span>
                            </span>
                        </div>
                    </div>

                    <!-- Right: Tactile Stepper & Subtotal -->
                    <div class="flex items-center gap-2 sm:gap-2.5 flex-shrink-0">
                        <div class="item-stepper-box flex items-center bg-zinc-50 border border-zinc-200 rounded-lg p-0.5 {{ $isSoldOut ? 'hidden' : '' }}">
                            <button type="button" class="stepper-btn btn-part-minus w-6 h-6 sm:w-7 sm:h-7 rounded flex items-center justify-center text-zinc-600 hover:text-zinc-900 hover:bg-white disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer" disabled>
                                <i class="fa-light fa-minus text-[9px] sm:text-[10px]"></i>
                            </button>
                            <input type="number" class="part-qty-input w-7 sm:w-8 text-center bg-transparent text-xs font-bold text-zinc-900 tabular-nums focus:outline-none" value="0" min="0" max="{{ $item->remaining_qty }}" autocomplete="off" readonly>
                            <button type="button" class="stepper-btn btn-part-plus w-6 h-6 sm:w-7 sm:h-7 rounded flex items-center justify-center text-zinc-600 hover:text-zinc-900 hover:bg-white disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer" {{ $isSoldOut ? 'disabled' : '' }}>
                                <i class="fa-light fa-plus text-[9px] sm:text-[10px]"></i>
                            </button>
                        </div>
                        <span class="item-subtotal text-xs font-bold text-zinc-400 tabular-nums w-16 sm:w-20 text-right {{ $isSoldOut ? 'hidden' : '' }}">Rp 0</span>
                        <span class="item-sold-out-text text-xs font-semibold text-zinc-400 tabular-nums px-2 {{ $isSoldOut ? '' : 'hidden' }}">Selesai</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Empty search results state -->
        <div id="menuSearchEmptyState" class="hidden p-6 rounded-2xl bg-zinc-50 border border-dashed border-zinc-200 text-center text-xs text-zinc-500 space-y-1">
            <i class="fa-light fa-magnifying-glass text-zinc-400 text-xl block mb-1"></i>
            <p class="font-medium text-zinc-700">Item tidak ditemukan</p>
            <p class="text-zinc-400">Tidak ada item yang cocok dengan kata kunci pencarian Anda.</p>
        </div>

        <!-- Proportional Calculation Summary for Current Participant -->
        <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200/80 space-y-2 text-xs">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Kalkulasi Bagianmu</span>

            <div class="flex justify-between text-zinc-600">
                <span>Subtotal Item Terpilih:</span>
                <span id="partSubtotal" class="font-bold text-zinc-900 tabular-nums">Rp 0</span>
            </div>

            <div class="flex justify-between text-zinc-500">
                <span class="flex items-center gap-1">
                    <i class="fa-light fa-chart-pie text-[10px] text-emerald-700"></i> Porsi Belanja dari Total Tagihan:
                </span>
                <span id="partPercentage" class="font-bold text-emerald-800 tabular-nums">0%</span>
            </div>

            <div class="flex justify-between text-zinc-600">
                <span>Alokasi Ongkir & Layanan:</span>
                <span id="partFeeShare" class="font-medium text-zinc-900 tabular-nums">Rp 0</span>
            </div>

            <div class="flex justify-between text-emerald-700">
                <span>Alokasi Potongan Diskon:</span>
                <span id="partDiscountShare" class="font-bold tabular-nums">-Rp 0</span>
            </div>

            <!-- Tip / Bulatkan ke atas Toggle -->
            <div class="pt-2 border-t border-zinc-200/80 flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" id="partRoundUp" class="w-4 h-4 rounded border-zinc-300 text-emerald-800 focus:ring-emerald-700 cursor-pointer accent-emerald-800" autocomplete="off">
                    <span class="text-xs text-zinc-700 font-medium">Bulatkan ke atas (Tip/Donasi untuk Host)</span>
                </label>
                <span id="partRoundUpBadge" class="hidden text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded">+Rp 0</span>
            </div>

            <div class="pt-2.5 border-t border-zinc-200 flex justify-between items-center text-sm font-bold text-zinc-900">
                <span>Total yang Harus Kamu Bayar:</span>
                <span id="partGrandTotal" class="text-base sm:text-lg font-black text-emerald-900 tabular-nums">Rp 0</span>
            </div>
        </div>

        <!-- Payment Actions: Unified Payment Modal & Claim Button -->
        <div class="pt-2 space-y-3">
            @if($bill->qris_payload || $bill->banks->count() > 0)
                <button type="button" id="btnShowPaymentModal" class="touch-target w-full py-3 px-4 rounded-xl btn-primary font-bold text-xs sm:text-sm shadow-md inline-flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <i class="fa-light fa-wallet text-base"></i>
                    <span>Bayar Sekarang</span>
                </button>
            @endif

            <button type="button" id="btnOpenClaimModal" class="touch-target w-full py-2.5 px-4 rounded-xl bg-white hover:bg-emerald-50/50 text-emerald-800 border-2 border-emerald-800/80 font-bold text-xs sm:text-sm shadow-2xs inline-flex items-center justify-center gap-2 transition-all cursor-pointer">
                <i class="fa-light fa-circle-check text-sm text-emerald-700"></i>
                <span>Saya Sudah Transfer / Bayar</span>
            </button>
        </div>
    </div>

    <!-- ==========================================
         BANK ACCOUNTS INFO (Alternative Transfer)
         ========================================== -->
    @if($bill->banks->count() > 0)
        <div class="card-solid rounded-2xl p-6 bg-white border border-zinc-200/90 shadow-sm space-y-3">
            <h2 class="text-sm sm:text-base font-bold text-zinc-900 flex items-center gap-2">
                <i class="fa-light fa-building-columns text-emerald-700"></i>
                <span>Transfer Bank & E-Wallet Host</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                @foreach($bill->banks as $bank)
                    <div class="p-3.5 rounded-xl bg-zinc-50 border {{ $bank->is_primary ? 'border-emerald-700 ring-1 ring-emerald-200/60 bg-emerald-50/20' : 'border-zinc-200/80' }} flex items-center justify-between gap-3">
                        <div class="text-xs leading-tight">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-zinc-900">{{ $bank->bank_name }}</span>
                                @if($bank->is_primary)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-light fa-star text-[8px]"></i>
                                        <span>Utama</span>
                                    </span>
                                @endif
                            </div>
                            <div class="text-zinc-700 tabular-nums font-mono font-bold mt-1 tracking-wide">{{ $bank->account_number }}</div>
                            <div class="text-[10px] text-zinc-400 mt-0.5">a.n {{ $bank->account_holder }}</div>
                        </div>
                        <button type="button" class="btn-copy-acc px-2.5 py-1.5 rounded-lg bg-white border border-zinc-200 hover:border-emerald-600 text-zinc-600 hover:text-emerald-800 text-xs font-semibold shadow-2xs transition-colors cursor-pointer" data-acc="{{ $bank->account_number }}" title="Salin Nomor">
                            <i class="fa-light fa-copy"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ==========================================
         RIWAYAT PEMBAYARAN TEMAN (Unified & Clickable to Modal)
         ========================================== -->
    <div id="riwayat-pembayaran" class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm space-y-4">
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-zinc-900 flex items-center gap-2">
                    <i class="fa-light fa-users text-emerald-700"></i>
                    <span>Riwayat Pembayaran (<span id="totalClaimsCount">{{ $bill->claims->count() }}</span>)</span>
                </h2>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Daftar teman yang sudah konfirmasi bayar &bull; Klik kartu untuk melihat rincian pembayaran
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($isHost)
                    <button type="button" id="btnOpenBatchConfirmModal" class="{{ $pendingCount > 0 ? 'inline-flex' : 'hidden' }} touch-target px-3 py-1.5 rounded-xl bg-emerald-800 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs items-center gap-1.5 transition-all cursor-pointer" style="{{ $pendingCount > 0 ? '' : 'display: none !important;' }}" title="Buka konfirmasi massal">
                        <i class="fa-light fa-check-double text-xs"></i>
                        <span>Konfirmasi Sekaligus (<span id="batchBtnPendingCount">{{ $pendingCount }}</span>)</span>
                    </button>
                @endif
                @if($bill->claims->count() > 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70">
                        <i class="fa-light fa-hand-pointer text-emerald-700"></i>
                        <span>Klik kartu untuk rincian</span>
                    </span>
                @endif
            </div>
        </div>

        <div class="space-y-2.5 {{ $bill->claims->count() > 0 ? '' : 'hidden' }}" id="claimsListContainer">
            @foreach($bill->claims->sortByDesc('created_at') as $claim)
                <div class="claim-history-card p-3.5 sm:p-4 rounded-xl bg-white border border-zinc-200/90 hover:border-emerald-600/70 hover:shadow-2xs transition-all cursor-pointer space-y-2"
                     data-claim-id="{{ $claim->id }}"
                     role="button"
                     tabindex="0"
                     title="Klik untuk melihat rincian pembayaran {{ $claim->payer_name }}">

                    <!-- Top Row: Name, Status & Amount -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 flex-wrap min-w-0">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-900 font-black text-xs flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr(trim($claim->payer_name), 0, 1)) }}
                            </div>
                            <span class="font-bold text-zinc-900 text-sm truncate">{{ $claim->payer_name }}</span>

                            <!-- Status Badge -->
                            @if($claim->status === 'confirmed')
                                <span class="claim-status-badge inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    <i class="fa-light fa-circle-check text-emerald-600"></i>
                                    <span>Lunas Terkonfirmasi</span>
                                </span>
                            @else
                                <span class="claim-status-badge inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/70 animate-pulse">
                                    <i class="fa-light fa-hourglass-clock text-amber-600"></i>
                                    <span>Menunggu Konfirmasi Host</span>
                                </span>
                            @endif

                            <!-- Payment Method Badge -->
                            @php
                                $pm = strtolower($claim->payment_method ?? 'qris');
                            @endphp
                            @if(str_contains($pm, 'qris'))
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200">
                                    <i class="fa-light fa-qrcode text-[10px]"></i> QRIS
                                </span>
                            @elseif(str_contains($pm, 'cash') || str_contains($pm, 'tunai'))
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                    <i class="fa-light fa-money-bill-wave text-[10px]"></i> Tunai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200">
                                    <i class="fa-light fa-building-columns text-[10px]"></i> {{ $claim->payment_method }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="text-right">
                                <div class="text-sm sm:text-base font-extrabold text-emerald-900 tabular-nums">
                                    Rp {{ number_format($claim->amount, 0, ',', '.') }}
                                </div>
                                @if(($claim->tip_amount ?? 0) > 0)
                                    <div class="text-[10px] text-emerald-700 font-semibold flex items-center justify-end gap-1">
                                        <i class="fa-light fa-gift text-[9px]"></i>
                                        <span>+Tip Rp {{ number_format($claim->tip_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                            <i class="fa-light fa-chevron-right text-zinc-400 text-xs"></i>
                        </div>
                    </div>

                    <!-- Bottom Row: Clean Summary (Raw items hidden, click to open modal) -->
                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-zinc-100 text-[11px] text-zinc-500">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-light fa-receipt text-zinc-400 text-[10px]"></i>
                            <span>{{ $claim->claimItems->count() }} jenis ({{ $claim->claimItems->sum('qty') }} item)</span>
                            <span class="text-zinc-300">&bull;</span>
                            <span>{{ $claim->created_at->diffForHumans() }}</span>
                        </div>
                        <span class="text-emerald-700 font-semibold hover:underline flex items-center gap-1">
                            <span>Rincian</span>
                            <i class="fa-light fa-arrow-up-right-from-square text-[9px]"></i>
                        </span>
                    </div>

                    <!-- Host Actions: If viewer is Host and claim is pending (or allow reject) -->
                    @if($isHost)
                        <div class="host-actions-row pt-2 border-t border-zinc-100 flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                            @if($claim->status === 'pending')
                                <button type="button"
                                        class="btn-confirm-claim touch-target px-3 py-1.5 rounded-lg bg-emerald-800 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all cursor-pointer"
                                        data-claim-id="{{ $claim->id }}"
                                        data-name="{{ $claim->payer_name }}"
                                        data-amount="Rp {{ number_format($claim->amount, 0, ',', '.') }}">
                                    <i class="fa-light fa-check text-xs"></i>
                                    <span>Konfirmasi Dana Masuk</span>
                                </button>
                            @endif

                            <button type="button"
                                    class="btn-reject-claim touch-target p-1.5 px-2.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors cursor-pointer text-xs font-semibold inline-flex items-center gap-1"
                                    data-claim-id="{{ $claim->id }}"
                                    data-name="{{ $claim->payer_name }}"
                                    title="Tolak / Hapus Klaim">
                                <i class="fa-light fa-trash-can text-xs"></i>
                                <span class="hidden sm:inline">Tolak</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div id="emptyClaimsHistoryState" class="p-6 rounded-xl bg-zinc-50 border border-zinc-200/80 text-center text-xs text-zinc-500 space-y-1 {{ $bill->claims->count() > 0 ? 'hidden' : '' }}">
            <i class="fa-light fa-clock text-zinc-400 text-xl block mb-1"></i>
            <p class="font-medium text-zinc-700">Belum ada teman yang klaim pembayaran</p>
            <p class="text-zinc-400">Bagikan tautan patungan ini agar teman-temanmu bisa memilih menu dan membayar.</p>
        </div>
    </div>

</div>

<!-- ==========================================
     MODAL: PEMBAYARAN (QRIS Dinamis Card & Bank / E-Wallet)
     ========================================== -->
<div id="paymentModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-5 sm:p-7 max-w-md w-full text-center space-y-4 shadow-xl border border-zinc-200 animate-in fade-in zoom-in duration-200 max-h-[92vh] overflow-y-auto no-scrollbar">
        <!-- Header -->
        <div class="flex justify-between items-center pb-2.5 border-b border-zinc-100">
            <div class="text-left">
                <span class="text-sm font-bold text-zinc-900 block">Pilihan Pembayaran</span>
                <span class="text-[11px] text-zinc-400">Scan QRIS Dinamis atau transfer rekening bank / e-wallet</span>
            </div>
            <button type="button" id="btnClosePaymentModal" class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors cursor-pointer">
                <i class="fa-light fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="space-y-4">
            <!-- SECTION 1: QRIS Dinamis -->
            @if($bill->qris_payload)
            <div class="space-y-3">
                <!-- QRIS Card Container (Styled as official QRIS invoice voucher) -->
                <div id="qrisCardDownloadArea" class="p-4 sm:p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs space-y-3 text-center relative overflow-hidden">
                    <!-- Card Header -->
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2.5">
                        <div class="flex items-center gap-1.5">
                            <div class="w-5 h-5 rounded-md bg-emerald-800 text-white flex items-center justify-center text-[10px] font-bold">
                                <i class="fa-light fa-qrcode"></i>
                            </div>
                            <span class="font-extrabold text-xs text-zinc-900 tracking-tight">QRIS DINAMIS</span>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                            Nominal Pas
                        </span>
                    </div>

                    <!-- Merchant / Destination Info -->
                    <div class="space-y-0.5">
                        <div class="text-[10px] text-zinc-400 font-semibold uppercase tracking-wider">Tujuan Pembayaran</div>
                        <div class="text-sm font-black text-zinc-900 leading-tight">
                            {{ $bill->qris_merchant_name ?: $bill->user->name }}
                        </div>
                        <div class="text-[11px] text-zinc-500">
                            {{ $bill->qris_merchant_city ?: 'Indonesia' }} &bull; Host: {{ $bill->user->name }}
                        </div>
                    </div>

                    <!-- QR Code Box -->
                    <div class="p-3 rounded-2xl bg-zinc-50 border border-zinc-200 inline-block mx-auto">
                        <div id="dynamicQrCanvasContainer" class="w-52 h-52 mx-auto flex items-center justify-center"></div>
                    </div>

                    <!-- Amount / Nominal Locked -->
                    <div class="pt-1 border-t border-zinc-100 space-y-0.5">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Total Tagihan Kamu</span>
                        <div class="text-2xl font-black text-emerald-900 tabular-nums" id="modalQrisNominal">Rp 0</div>
                        <p class="text-[10px] text-zinc-400">
                            Scan via BCA, Mandiri, BRI, GoPay, OVO, ShopeePay, DANA dll.
                        </p>
                    </div>
                </div>

                <!-- Download Card Action -->
                <div>
                    <button type="button" id="btnDownloadQrisCard" class="touch-target w-full py-2.5 px-3 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                        <i class="fa-light fa-download text-xs text-emerald-800" id="downloadQrisCardIcon"></i>
                        <span id="downloadQrisCardText">Unduh Card QR</span>
                    </button>
                </div>
            </div>
            @endif

            <!-- SECTION 2: Transfer Bank & E-Wallet Content -->
            @if($bill->banks->count() > 0)
            <div class="space-y-2.5 text-left {{ $bill->qris_payload ? 'pt-2 border-t border-zinc-100' : '' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <i class="fa-light fa-building-columns text-emerald-700 text-xs"></i>
                        <span class="text-xs font-bold text-zinc-900">Transfer Bank / E-Wallet</span>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-800 tabular-nums" id="modalBankNominal">Rp 0</span>
                </div>

                <div class="space-y-2 max-h-52 overflow-y-auto no-scrollbar pr-0.5">
                    @foreach($bill->banks as $bank)
                    <div class="p-3 rounded-xl bg-zinc-50 border {{ $bank->is_primary ? 'border-emerald-700 ring-1 ring-emerald-200/60 bg-emerald-50/20' : 'border-zinc-200/80' }} flex items-center justify-between gap-2.5">
                        <div class="text-xs leading-tight min-w-0 flex-1">
                            <div class="font-bold text-zinc-900 flex items-center gap-1.5 flex-wrap">
                                <span class="truncate">{{ $bank->bank_name }}</span>
                                @if($bank->is_primary)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-light fa-star text-[8px]"></i>
                                        <span>Utama</span>
                                    </span>
                                @endif
                            </div>
                            <div class="text-zinc-800 tabular-nums font-mono font-bold mt-1 tracking-wide text-xs sm:text-sm">
                                {{ $bank->account_number }}
                            </div>
                            <div class="text-[10px] text-zinc-500 mt-0.5 truncate">
                                a.n {{ $bank->account_holder }}
                            </div>
                        </div>
                        <button type="button" class="btn-copy-acc-modal flex-shrink-0 touch-target px-2.5 py-1.5 rounded-lg bg-white border border-zinc-200 hover:border-emerald-600 text-zinc-700 hover:text-emerald-800 text-xs font-semibold shadow-2xs transition-colors cursor-pointer flex items-center gap-1" data-acc="{{ $bank->account_number }}" data-bank="{{ $bank->bank_name }}">
                            <i class="fa-light fa-copy text-xs"></i>
                            <span>Salin</span>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Collapsible: Rincian Tagihan & Biaya (Crosscheck sebelum bayar) -->
            <div class="rounded-2xl border border-zinc-200/90 bg-zinc-50/70 overflow-hidden text-left transition-all">
                <button type="button" id="btnTogglePayModalDetails" class="w-full p-3.5 flex items-center justify-between gap-2 text-xs font-bold text-zinc-800 hover:text-emerald-800 hover:bg-zinc-100/70 transition-colors cursor-pointer select-none">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-light fa-receipt text-emerald-700 text-sm"></i>
                        <span>Lihat Rincian Item & Biaya</span>
                    </span>
                    <span class="flex items-center gap-1.5 text-zinc-400 font-medium text-[11px]">
                        <span id="payModalItemCountBadge" class="px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">0 item</span>
                        <i class="fa-light fa-chevron-down text-xs transition-transform duration-200" id="payModalDetailsChevron"></i>
                    </span>
                </button>

                <div id="payModalDetailsContent" class="hidden p-3.5 pt-0 space-y-3 border-t border-zinc-200/60 mt-1">
                    <!-- Daftar Item yang Dipilih -->
                    <div class="space-y-1.5 pt-2">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Item yang Kamu Ambil</span>
                        <div class="divide-y divide-zinc-200/60 max-h-36 overflow-y-auto no-scrollbar rounded-xl bg-white border border-zinc-200/70" id="payModalItemsList">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>

                    <!-- Breakdown Proporsi Biaya -->
                    <div class="p-2.5 rounded-xl bg-white border border-zinc-200/70 space-y-1.5 text-xs">
                        <div class="flex justify-between text-zinc-600">
                            <span>Subtotal Item:</span>
                            <span class="font-bold text-zinc-800 tabular-nums" id="payModalBreakdownSubtotal">Rp 0</span>
                        </div>
                        <div class="hidden justify-between text-zinc-600" id="payModalRowDelivery">
                            <span>Proporsi Ongkir:</span>
                            <span class="font-bold text-zinc-800 tabular-nums" id="payModalBreakdownDelivery">+Rp 0</span>
                        </div>
                        <div class="hidden justify-between text-zinc-600" id="payModalRowService">
                            <span>Proporsi Layanan/Pajak:</span>
                            <span class="font-bold text-zinc-800 tabular-nums" id="payModalBreakdownService">+Rp 0</span>
                        </div>
                        <div class="hidden justify-between text-emerald-700" id="payModalRowDiscount">
                            <span>Proporsi Diskon:</span>
                            <span class="font-bold text-emerald-800 tabular-nums" id="payModalBreakdownDiscount">-Rp 0</span>
                        </div>
                        <div class="hidden justify-between text-emerald-700" id="payModalRowRoundUp">
                            <span>Pembulatan:</span>
                            <span class="font-bold text-emerald-800 tabular-nums" id="payModalBreakdownRoundUp">+Rp 0</span>
                        </div>
                        <div class="pt-1.5 border-t border-zinc-200 flex justify-between font-black text-zinc-900 text-xs">
                            <span>Total Tagihan Pokok:</span>
                            <span class="text-emerald-900 tabular-nums font-black" id="payModalBreakdownTotal">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Action: Direct To Claim Modal -->
        <div class="pt-2 border-t border-zinc-100 space-y-2">
            <button type="button" id="btnModalConfirmPaid" class="touch-target w-full py-2.5 px-4 rounded-xl btn-primary font-bold text-xs sm:text-sm shadow-xs inline-flex items-center justify-center gap-2 cursor-pointer transition-all">
                <i class="fa-light fa-circle-check text-xs"></i>
                <span>Saya Sudah Selesai Bayar / Transfer</span>
            </button>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: KLAIM SUDAH BAYAR FORM
     ========================================== -->
<div id="claimModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full text-left space-y-4 shadow-xl border border-zinc-200 animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center pb-2 border-b border-zinc-100">
            <span class="text-sm font-bold text-zinc-900">Konfirmasi Klaim Pembayaran</span>
            <button type="button" id="btnCloseClaimModal" class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors cursor-pointer">
                <i class="fa-light fa-xmark text-sm"></i>
            </button>
        </div>

        <p class="text-xs text-zinc-500 leading-relaxed">
            Klaim ini akan dikirimkan ke Host (<strong>{{ $bill->user->name }}</strong>) untuk diverifikasi dengan mutasi rekeningnya.
        </p>

        <form id="claimPaymentForm" class="space-y-3.5">
            <!-- Payer Name -->
            <div class="space-y-1">
                <label for="claimPayerName" class="block text-xs font-bold text-zinc-700">
                    Nama Kamu <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="claimPayerName" required placeholder="Contoh: Budi Prasetyo" class="touch-target w-full px-3.5 py-2.5 text-xs sm:text-sm bg-white border border-zinc-300 rounded-xl focus:outline-none focus:border-emerald-700 text-zinc-900 font-medium">
            </div>

            <!-- Payment Method -->
            <div class="space-y-1">
                <label for="claimPaymentMethod" class="block text-xs font-bold text-zinc-700">
                    Metode Pembayaran
                </label>
                <select id="claimPaymentMethod" class="touch-target w-full px-3 py-2 text-xs bg-white border border-zinc-300 rounded-xl focus:outline-none focus:border-emerald-700 text-zinc-900 font-medium">
                    @if($bill->qris_payload)
                        <option value="qris" selected>QRIS Dinamis</option>
                    @endif
                    @foreach($bill->banks as $index => $bank)
                        <option value="{{ $bank->bank_name }}" {{ (!$bill->qris_payload && ($bank->is_primary || $index === 0)) ? 'selected' : '' }}>
                            Transfer {{ $bank->bank_name }}{{ $bank->is_primary ? ' (Utama)' : '' }}
                        </option>
                    @endforeach
                    <option value="cash" {{ (!$bill->qris_payload && $bill->banks->isEmpty()) ? 'selected' : '' }}>Tunai / Cash</option>
                </select>
            </div>

            <!-- Nominal Yang Dibayarkan -->
            <div class="space-y-1.5">
                <div class="flex justify-between items-center">
                    <label for="claimCustomAmount" class="block text-xs font-bold text-zinc-700">
                        Nominal Dibayarkan
                    </label>
                    <span id="claimMethodLockNotice" class="text-[10px] text-zinc-400 font-medium flex items-center gap-1">
                        @if($bill->qris_payload)
                            <i class="fa-light fa-lock text-[10px]"></i> Terkunci (QRIS)
                        @else
                            <i class="fa-light fa-pen-to-square text-[10px] text-emerald-600"></i> Bebas edit / tambah Tip
                        @endif
                    </span>
                </div>

                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-400">Rp</span>
                    <input type="text" id="claimCustomAmount" {{ $bill->qris_payload ? 'readonly' : '' }}
                        class="touch-target w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm {{ $bill->qris_payload ? 'bg-zinc-100 text-zinc-600 cursor-not-allowed' : 'bg-white text-zinc-900' }} border border-zinc-300 rounded-xl focus:outline-none font-bold tabular-nums transition-colors"
                        placeholder="0">
                </div>

                <!-- Quick Tip Chips (Visible only for non-QRIS) -->
                <div id="quickTipChipsContainer" class="{{ $bill->qris_payload ? 'hidden' : 'flex' }} pt-1 items-center gap-1.5 flex-wrap">
                    <span class="text-[10px] font-semibold text-zinc-400 mr-1">Opsi Cepat:</span>
                    <button type="button" class="btn-quick-chip px-2 py-0.5 rounded-lg bg-zinc-100 hover:bg-emerald-50 hover:text-emerald-800 text-[11px] font-semibold text-zinc-600 border border-zinc-200 cursor-pointer transition-colors" data-chip="exact">
                        Pas
                    </button>
                    <button type="button" class="btn-quick-chip px-2 py-0.5 rounded-lg bg-zinc-100 hover:bg-emerald-50 hover:text-emerald-800 text-[11px] font-semibold text-zinc-600 border border-zinc-200 cursor-pointer transition-colors" data-chip="2000">
                        +Rp 2.000
                    </button>
                    <button type="button" class="btn-quick-chip px-2 py-0.5 rounded-lg bg-zinc-100 hover:bg-emerald-50 hover:text-emerald-800 text-[11px] font-semibold text-zinc-600 border border-zinc-200 cursor-pointer transition-colors" data-chip="5000">
                        +Rp 5.000
                    </button>
                    <button type="button" class="btn-quick-chip px-2 py-0.5 rounded-lg bg-zinc-100 hover:bg-emerald-50 hover:text-emerald-800 text-[11px] font-semibold text-zinc-600 border border-zinc-200 cursor-pointer transition-colors" data-chip="10000">
                        +Rp 10.000
                    </button>
                </div>

                <!-- Amount Breakdown Preview -->
                <div class="rounded-xl border border-zinc-200/90 bg-zinc-50 overflow-hidden text-left transition-all">
                    <button type="button" id="btnToggleClaimModalDetails" class="w-full p-2.5 sm:p-3 flex items-center justify-between gap-2 text-xs font-bold text-zinc-700 hover:text-emerald-800 transition-colors cursor-pointer select-none">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-light fa-receipt text-emerald-700"></i>
                            <span>Rincian Item & Biaya</span>
                        </span>
                        <span class="flex items-center gap-1 text-[11px] text-zinc-400">
                            <span class="font-bold tabular-nums text-zinc-800" id="claimAmountPreview">Rp 0</span>
                            <i class="fa-light fa-chevron-down text-xs transition-transform duration-200" id="claimModalDetailsChevron"></i>
                        </span>
                    </button>

                    <div id="claimModalDetailsContent" class="hidden p-3 pt-0 space-y-2.5 border-t border-zinc-200/70 mt-1">
                        <!-- Claim Items List -->
                        <div class="space-y-1 pt-1.5">
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Item Kamu</span>
                            <div class="divide-y divide-zinc-200/60 max-h-32 overflow-y-auto no-scrollbar rounded-lg bg-white border border-zinc-200/70" id="claimModalItemsList">
                                <!-- Populated dynamically via JS -->
                            </div>
                        </div>

                        <!-- Cost Breakdown -->
                        <div class="p-2.5 rounded-lg bg-white border border-zinc-200/70 space-y-1 text-xs">
                            <div class="flex justify-between text-zinc-600">
                                <span>Subtotal Item:</span>
                                <span class="font-semibold text-zinc-800 tabular-nums" id="claimModalBreakdownSubtotal">Rp 0</span>
                            </div>
                            <div class="hidden justify-between text-zinc-600" id="claimModalRowDelivery">
                                <span>Proporsi Ongkir:</span>
                                <span class="font-semibold text-zinc-800 tabular-nums" id="claimModalBreakdownDelivery">+Rp 0</span>
                            </div>
                            <div class="hidden justify-between text-zinc-600" id="claimModalRowService">
                                <span>Proporsi Layanan/Pajak:</span>
                                <span class="font-semibold text-zinc-800 tabular-nums" id="claimModalBreakdownService">+Rp 0</span>
                            </div>
                            <div class="hidden justify-between text-emerald-700" id="claimModalRowDiscount">
                                <span>Proporsi Diskon:</span>
                                <span class="font-semibold text-emerald-800 tabular-nums" id="claimModalBreakdownDiscount">-Rp 0</span>
                            </div>
                            <div class="hidden justify-between text-emerald-700" id="claimModalRowRoundUp">
                                <span>Pembulatan:</span>
                                <span class="font-semibold text-emerald-800 tabular-nums" id="claimModalBreakdownRoundUp">+Rp 0</span>
                            </div>
                            <div class="pt-1.5 border-t border-zinc-200 flex justify-between font-bold text-zinc-900 text-xs">
                                <span>Tagihan Pokok:</span>
                                <span class="tabular-nums font-bold text-zinc-900" id="claimModalBreakdownTotal">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tip & Total Transfer Rows -->
                    <div class="p-3 pt-1 border-t border-zinc-200/60 text-xs space-y-1">
                        <div id="claimTipBreakdownRow" class="hidden justify-between text-emerald-700 font-semibold pt-1">
                            <span class="flex items-center gap-1">
                                <i class="fa-light fa-gift text-emerald-600"></i>
                                <span>Tip / Extra untuk Host:</span>
                            </span>
                            <span id="claimTipPreview" class="tabular-nums font-bold">+Rp 0</span>
                        </div>
                        <div id="claimTotalTransferRow" class="hidden justify-between text-zinc-900 font-black pt-1 border-t border-zinc-200">
                            <span>Total Ditransfer:</span>
                            <span id="claimTotalTransferPreview" class="tabular-nums text-emerald-900 font-black">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="claimModalAlert" class="hidden p-2.5 rounded-lg text-xs font-medium"></div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" id="btnCancelClaimModal" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-500 hover:text-zinc-800 cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnSubmitClaim" class="touch-target px-5 py-2.5 rounded-xl btn-primary font-bold text-xs sm:text-sm shadow-xs inline-flex items-center gap-1.5 cursor-pointer transition-all">
                    <span>Kirim Klaim Pembayaran</span>
                    <i class="fa-light fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     MODAL: RINCIAN PEMBAYARAN TEMAN (Claim Detail Popup)
     ========================================== -->
<div id="claimDetailModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-5 sm:p-7 max-w-md w-full text-left space-y-4 shadow-xl border border-zinc-200 animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto no-scrollbar">
        <!-- Header -->
        <div class="flex justify-between items-center pb-3 border-b border-zinc-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm font-bold">
                    <i class="fa-light fa-receipt text-emerald-800"></i>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-zinc-900">Rincian Pembayaran</h3>
            </div>
            <button type="button" id="btnCloseClaimDetailModal" class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors cursor-pointer">
                <i class="fa-light fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Payer Header & Total -->
        <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-200/80 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-emerald-800 text-white font-extrabold text-base flex items-center justify-center shadow-2xs flex-shrink-0" id="detailPayerInitial">
                        U
                    </div>
                    <div class="min-w-0">
                        <h4 class="font-bold text-zinc-900 text-sm truncate" id="detailPayerName">-</h4>
                        <span class="text-[11px] text-zinc-500 block truncate" id="detailTimestamp">-</span>
                    </div>
                </div>
                <div class="text-right space-y-1 flex-shrink-0">
                    <div id="detailStatusBadge"></div>
                    <div id="detailMethodBadge"></div>
                </div>
            </div>

            <div class="pt-2 border-t border-zinc-200/80 flex items-baseline justify-between">
                <span class="text-xs text-zinc-500 font-medium">Total Nominal Dibayar:</span>
                <div class="text-right">
                    <span class="text-xl sm:text-2xl font-black text-emerald-900 tabular-nums" id="detailAmountPaid">Rp 0</span>
                    <div class="hidden" id="detailSurplusBadge">
                        <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full inline-block mt-0.5" id="detailSurplusText">
                            +Rp 0 Tip / Pembulatan
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proporsi Beban Pesanan -->
        <div class="p-3.5 rounded-2xl border border-zinc-200/80 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-zinc-800 flex items-center gap-1.5">
                    <i class="fa-light fa-chart-pie text-emerald-700"></i>
                    <span>Proporsi Beban Pesanan</span>
                </span>
                <span class="font-extrabold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/50" id="detailProportionBadge">0%</span>
            </div>
            <div class="w-full bg-zinc-200 rounded-full h-2 overflow-hidden">
                <div class="bg-emerald-600 h-2 rounded-full transition-all duration-300" id="detailProportionBar" style="width: 0%"></div>
            </div>
            <p class="text-[11px] text-zinc-500 leading-snug" id="detailProportionDesc">
                Kontribusi terhadap total tagihan pesanan menu.
            </p>
        </div>

        <!-- Menu yang Dipesan List -->
        <div class="rounded-2xl border border-zinc-200/80 overflow-hidden">
            <div class="bg-zinc-50 px-3.5 py-2 text-xs font-bold text-zinc-700 border-b border-zinc-200/80 flex justify-between items-center">
                <span><i class="fa-light fa-layer-group text-emerald-700 mr-1"></i> Item Dipilih (<span id="detailItemCount">0</span>)</span>
                <span>Subtotal</span>
            </div>
            <div class="divide-y divide-zinc-100 max-h-40 overflow-y-auto no-scrollbar" id="detailItemsList">
                <!-- Populated via JS -->
            </div>
        </div>

        <!-- Rincian Pembagian Biaya -->
        <div class="p-3.5 rounded-2xl bg-zinc-50 border border-zinc-200/80 space-y-1.5 text-xs">
            <span class="font-bold text-zinc-800 block text-xs mb-1">
                <i class="fa-light fa-calculator text-emerald-700 mr-1"></i> Rincian Pembagian Biaya
            </span>
            <div class="flex justify-between text-zinc-600">
                <span>Subtotal Item:</span>
                <span class="font-semibold text-zinc-900 tabular-nums" id="detailItemsSubtotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-zinc-600 hidden" id="detailRowDelivery">
                <span>Proporsi Ongkir:</span>
                <span class="font-semibold text-zinc-900 tabular-nums" id="detailShareDelivery">+Rp 0</span>
            </div>
            <div class="flex justify-between text-zinc-600 hidden" id="detailRowService">
                <span>Proporsi Biaya Layanan:</span>
                <span class="font-semibold text-zinc-900 tabular-nums" id="detailShareService">+Rp 0</span>
            </div>
            <div class="flex justify-between text-emerald-700 hidden" id="detailRowDiscount">
                <span>Proporsi Diskon:</span>
                <span class="font-bold tabular-nums" id="detailShareDiscount">-Rp 0</span>
            </div>
            <div class="flex justify-between text-zinc-600 hidden" id="detailRowSurplus">
                <span>Tip / Pembulatan ke Atas:</span>
                <span class="font-semibold text-emerald-800 tabular-nums" id="detailShareSurplus">+Rp 0</span>
            </div>
            <div class="pt-2 border-t border-zinc-200/90 flex justify-between font-bold text-zinc-900 text-sm">
                <span>Total Dibayarkan:</span>
                <span class="font-black text-emerald-900 tabular-nums" id="detailFinalAmount">Rp 0</span>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="pt-1 flex items-center justify-between gap-2">
            <button type="button" id="btnCopyClaimSummary" class="touch-target px-3.5 py-2 rounded-xl bg-white hover:bg-zinc-50 text-zinc-800 border border-zinc-300 font-semibold text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                <i class="fa-light fa-copy text-xs text-emerald-800" id="btnCopyClaimIcon"></i>
                <span id="btnCopyClaimText">Salin Rincian</span>
            </button>
            <button type="button" id="btnCloseClaimDetailModalBottom" class="touch-target px-4 py-2 rounded-xl bg-zinc-200 hover:bg-zinc-300 text-zinc-800 font-bold text-xs transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: DAFTAR PEMBAYAR PER MENU (Item Contributors)
     ========================================== -->
<div id="itemContributorsModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-5 sm:p-7 max-w-md w-full text-left space-y-4 shadow-xl border border-zinc-200 animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto no-scrollbar">
        <!-- Header -->
        <div class="flex justify-between items-center pb-3 border-b border-zinc-100">
            <div class="flex items-center gap-2.5 min-w-0 pr-2">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm font-bold flex-shrink-0">
                    <i class="fa-light fa-utensils text-emerald-800"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm sm:text-base font-bold text-zinc-900 truncate" id="itemContributorsModalTitle">Rincian Pembayar Menu</h3>
                    <p class="text-[11px] text-zinc-500" id="itemContributorsModalSubtitle">Siapa saja yang memesan menu ini</p>
                </div>
            </div>
            <button type="button" id="btnCloseItemContributorsModal" class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors cursor-pointer flex-shrink-0">
                <i class="fa-light fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Menu Item Info Box -->
        <div class="p-3.5 rounded-2xl bg-zinc-50 border border-zinc-200/80 flex items-center justify-between gap-3 text-xs">
            <div class="min-w-0">
                <div class="font-bold text-zinc-900 text-sm truncate" id="itemModalItemName">-</div>
                <div class="text-zinc-500 text-[11px] mt-0.5" id="itemModalItemPrice">@ Rp 0</div>
            </div>
            <div class="text-right flex-shrink-0">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70" id="itemModalStockBadge">
                    0/0 Item
                </span>
            </div>
        </div>

        <!-- Contributors List Container -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Teman yang Membayar:</span>
                <span class="text-[11px] text-zinc-500 font-medium" id="itemModalTotalClaimedSummary">Total: 0 item</span>
            </div>

            <div id="itemContributorsList" class="space-y-2 max-h-60 overflow-y-auto no-scrollbar">
                <!-- Dynamically populated via JS -->
            </div>

            <!-- Empty State -->
            <div id="itemContributorsEmptyState" class="hidden p-6 rounded-2xl bg-zinc-50 border border-dashed border-zinc-200 text-center text-xs text-zinc-500 space-y-1">
                <i class="fa-light fa-clock text-zinc-300 text-2xl block mb-1"></i>
                <p class="font-medium text-zinc-700">Belum ada yang mengklaim item ini</p>
                <p class="text-zinc-400">Pilih item kamu menggunakan tombol + di daftar item di atas.</p>
            </div>
        </div>

        <!-- Footer / Close -->
        <div class="pt-2 flex items-center justify-end">
            <button type="button" id="btnBottomCloseItemContributorsModal" class="touch-target w-full sm:w-auto px-5 py-2.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold text-xs transition-colors cursor-pointer text-center">
                Tutup
            </button>
        </div>
    </div>
</div>

@if($isHost)
<!-- ==========================================
     MODAL: KONFIRMASI PEMBAYARAN SEKALIGUS (Host Batch Confirm)
     ========================================== -->
<div id="batchConfirmModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-5 sm:p-7 max-w-md w-full text-left space-y-4 shadow-xl border border-zinc-200 animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto no-scrollbar">
        <!-- Header -->
        <div class="flex justify-between items-center pb-3 border-b border-zinc-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm font-bold">
                    <i class="fa-light fa-check-double text-emerald-800"></i>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-zinc-900">Konfirmasi Sekaligus</h3>
            </div>
            <button type="button" id="btnCloseBatchConfirmModal" class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors cursor-pointer">
                <i class="fa-light fa-xmark text-sm"></i>
            </button>
        </div>

        <p class="text-xs text-zinc-500 leading-relaxed">
            Centang teman yang dananya sudah masuk ke mutasi rekeningmu. Teman yang belum transfer dapat kamu hilangkan centangnya.
        </p>

        <!-- Select All Bar -->
        <div class="p-2.5 rounded-xl bg-zinc-50 border border-zinc-200/80 flex items-center justify-between">
            <label class="flex items-center gap-2.5 text-xs font-bold text-zinc-800 cursor-pointer select-none">
                <input type="checkbox" id="batchSelectAll" class="rounded text-emerald-700 focus:ring-emerald-600 h-4 w-4 border-zinc-300 cursor-pointer" checked>
                <span>Pilih Semua (<span id="batchSelectedRatioText">0/0</span>)</span>
            </label>
            <span class="text-[11px] text-zinc-500">Klik item untuk pilih</span>
        </div>

        <!-- Pending Claims Checklist Container -->
        <div class="rounded-2xl border border-zinc-200/80 overflow-hidden">
            <div class="divide-y divide-zinc-100 max-h-56 overflow-y-auto no-scrollbar" id="batchClaimsList">
                <!-- Dynamically populated via JS -->
            </div>
            <div id="batchClaimsEmptyState" class="hidden p-6 text-center text-xs text-zinc-500">
                <i class="fa-light fa-circle-check text-emerald-600 text-2xl mb-1.5 block"></i>
                <span>Semua klaim pembayaran sudah terkonfirmasi!</span>
            </div>
        </div>

        <!-- Summary Box -->
        <div class="p-3.5 rounded-2xl bg-zinc-50 border border-zinc-200/80 flex items-center justify-between text-xs">
            <div>
                <span class="text-zinc-500 block text-[11px]">Total Akan Dikonfirmasi:</span>
                <span class="font-bold text-zinc-800" id="batchSummaryCount">0 orang terpilih</span>
            </div>
            <div class="text-right">
                <span class="text-base sm:text-lg font-black text-emerald-900 tabular-nums" id="batchSummaryAmount">Rp 0</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-1 flex items-center justify-end gap-2.5">
            <button type="button" id="btnCancelBatchConfirmModal" class="touch-target px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" id="btnSubmitBatchConfirm" class="touch-target px-5 py-2.5 rounded-xl btn-primary font-bold text-xs sm:text-sm shadow-xs inline-flex items-center gap-2 cursor-pointer transition-all">
                <i class="fa-light fa-check-double text-xs" id="batchSubmitIcon"></i>
                <span id="batchSubmitText">Konfirmasi Terpilih</span>
            </button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>
<script>
window.claimDetailsData = @json($claimsDetailData);
window.developerQrisPayload = @json($developerQrisPayload ?? '00020101021126610014COM.GO-JEK.WWW01189360091431618763450210G1618763450303UMI51440014ID.CO.QRIS.WWW0215ID10253850061230303UMI5204729953033605802ID5921AkuOnline IT Services6014KOTA TANGERANG61051514762070703A016304D59A');

document.addEventListener('DOMContentLoaded', function () {
    const slug = "{{ $bill->slug }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const participantItemsContainer = document.getElementById('participantItemsContainer');
    const partSubtotal = document.getElementById('partSubtotal');
    const partPercentage = document.getElementById('partPercentage');
    const partFeeShare = document.getElementById('partFeeShare');
    const partDiscountShare = document.getElementById('partDiscountShare');
    const partRoundUp = document.getElementById('partRoundUp');
    const partRoundUpBadge = document.getElementById('partRoundUpBadge');
    const partGrandTotal = document.getElementById('partGrandTotal');

    const btnShowPaymentModal = document.getElementById('btnShowPaymentModal');
    const btnOpenClaimModal = document.getElementById('btnOpenClaimModal');

    // Payment Modal (Unified QRIS & Bank)
    const paymentModal = document.getElementById('paymentModal');
    const btnClosePaymentModal = document.getElementById('btnClosePaymentModal');
    const dynamicQrCanvasContainer = document.getElementById('dynamicQrCanvasContainer');
    const modalQrisNominal = document.getElementById('modalQrisNominal');
    const modalBankNominal = document.getElementById('modalBankNominal');
    const btnDownloadQrisCard = document.getElementById('btnDownloadQrisCard');
    const btnModalConfirmPaid = document.getElementById('btnModalConfirmPaid');

    // Post-Transaction Developer Support Alert Elements
    const postTransactionAlert = document.getElementById('postTransactionAlert');
    const postTxSuccessTitle = document.getElementById('postTxSuccessTitle');
    const postTxSuccessDesc = document.getElementById('postTxSuccessDesc');
    const btnOpenCoffeeModalFromAlert = document.getElementById('btnOpenCoffeeModalFromAlert');
    const btnDismissPostTxAlert = document.getElementById('btnDismissPostTxAlert');

    // Claim Modal
    const claimModal = document.getElementById('claimModal');
    const btnCloseClaimModal = document.getElementById('btnCloseClaimModal');
    const btnCancelClaimModal = document.getElementById('btnCancelClaimModal');
    const claimPaymentForm = document.getElementById('claimPaymentForm');
    const claimPayerName = document.getElementById('claimPayerName');
    const claimPaymentMethod = document.getElementById('claimPaymentMethod');
    const claimAmountPreview = document.getElementById('claimAmountPreview');
    const claimModalAlert = document.getElementById('claimModalAlert');
    const btnSubmitClaim = document.getElementById('btnSubmitClaim');
    const claimCustomAmount = document.getElementById('claimCustomAmount');
    const claimMethodLockNotice = document.getElementById('claimMethodLockNotice');
    const quickTipChipsContainer = document.getElementById('quickTipChipsContainer');
    const claimTipBreakdownRow = document.getElementById('claimTipBreakdownRow');
    const claimTipPreview = document.getElementById('claimTipPreview');
    const claimTotalTransferRow = document.getElementById('claimTotalTransferRow');
    const claimTotalTransferPreview = document.getElementById('claimTotalTransferPreview');

    // Claim Detail Modal
    const claimDetailModal = document.getElementById('claimDetailModal');
    const btnCloseClaimDetailModal = document.getElementById('btnCloseClaimDetailModal');
    const btnCloseClaimDetailModalBottom = document.getElementById('btnCloseClaimDetailModalBottom');
    const btnCopyClaimSummary = document.getElementById('btnCopyClaimSummary');

    // Payment Modal Collapsible Details
    const btnTogglePayModalDetails = document.getElementById('btnTogglePayModalDetails');
    const payModalDetailsContent = document.getElementById('payModalDetailsContent');
    const payModalDetailsChevron = document.getElementById('payModalDetailsChevron');
    const payModalItemCountBadge = document.getElementById('payModalItemCountBadge');
    const payModalItemsList = document.getElementById('payModalItemsList');
    const payModalBreakdownSubtotal = document.getElementById('payModalBreakdownSubtotal');
    const payModalRowDelivery = document.getElementById('payModalRowDelivery');
    const payModalBreakdownDelivery = document.getElementById('payModalBreakdownDelivery');
    const payModalRowService = document.getElementById('payModalRowService');
    const payModalBreakdownService = document.getElementById('payModalBreakdownService');
    const payModalRowDiscount = document.getElementById('payModalRowDiscount');
    const payModalBreakdownDiscount = document.getElementById('payModalBreakdownDiscount');
    const payModalRowRoundUp = document.getElementById('payModalRowRoundUp');
    const payModalBreakdownRoundUp = document.getElementById('payModalBreakdownRoundUp');
    const payModalBreakdownTotal = document.getElementById('payModalBreakdownTotal');

    // Claim Modal Collapsible Details
    const btnToggleClaimModalDetails = document.getElementById('btnToggleClaimModalDetails');
    const claimModalDetailsContent = document.getElementById('claimModalDetailsContent');
    const claimModalDetailsChevron = document.getElementById('claimModalDetailsChevron');
    const claimModalItemsList = document.getElementById('claimModalItemsList');
    const claimModalBreakdownSubtotal = document.getElementById('claimModalBreakdownSubtotal');
    const claimModalRowDelivery = document.getElementById('claimModalRowDelivery');
    const claimModalBreakdownDelivery = document.getElementById('claimModalBreakdownDelivery');
    const claimModalRowService = document.getElementById('claimModalRowService');
    const claimModalBreakdownService = document.getElementById('claimModalBreakdownService');
    const claimModalRowDiscount = document.getElementById('claimModalRowDiscount');
    const claimModalBreakdownDiscount = document.getElementById('claimModalBreakdownDiscount');
    const claimModalRowRoundUp = document.getElementById('claimModalRowRoundUp');
    const claimModalBreakdownRoundUp = document.getElementById('claimModalBreakdownRoundUp');
    const claimModalBreakdownTotal = document.getElementById('claimModalBreakdownTotal');

    let currentCalculatedTotal = 0;
    let currentDynamicPayload = '';
    let lastCalculatedData = null;
    let isClaimSubmitting = false;
    let activeClaimData = null;

    function formatRupiah(number) {
        return 'Rp ' + (new Intl.NumberFormat('id-ID').format(Math.round(number || 0)));
    }

    // Collect currently selected items: { [item_id]: qty }
    function getSelectedItems() {
        const items = {};
        const cards = participantItemsContainer.querySelectorAll('.item-selection-card');
        cards.forEach(card => {
            const input = card.querySelector('.part-qty-input');
            if (input) {
                const qty = parseInt(input.value) || 0;
                if (qty > 0) {
                    const itemId = card.getAttribute('data-item-id');
                    items[itemId] = qty;
                }
            }
        });
        return items;
    }

    // Collect currently selected items with full metadata for breakdown displays
    function getSelectedItemsDetails() {
        const items = [];
        const cards = participantItemsContainer.querySelectorAll('.item-selection-card');
        cards.forEach(card => {
            const input = card.querySelector('.part-qty-input');
            if (input) {
                const qty = parseInt(input.value) || 0;
                if (qty > 0) {
                    const id = card.getAttribute('data-item-id');
                    const name = card.getAttribute('data-raw-name') || card.getAttribute('data-name') || 'Item';
                    const price = parseFloat(card.getAttribute('data-price')) || 0;
                    items.push({
                        id: id,
                        name: name,
                        qty: qty,
                        price: price,
                        subtotal: qty * price
                    });
                }
            }
        });
        return items;
    }

    // Update item subtotals and button states on cards
    function updateCardVisuals() {
        const cards = participantItemsContainer.querySelectorAll('.item-selection-card');
        cards.forEach(card => {
            const input = card.querySelector('.part-qty-input');
            const price = parseFloat(card.getAttribute('data-price')) || 0;
            const subtotalEl = card.querySelector('.item-subtotal');
            const btnMinus = card.querySelector('.btn-part-minus');
            const btnPlus = card.querySelector('.btn-part-plus');
            const maxRemaining = parseInt(card.getAttribute('data-remaining')) || 0;
            const isSoldOut = maxRemaining <= 0;

            if (input && !isSoldOut) {
                card.classList.add('cursor-pointer');
                const qty = parseInt(input.value) || 0;
                const subtotal = qty * price;

                if (subtotalEl) {
                    if (qty > 0) {
                        subtotalEl.textContent = formatRupiah(subtotal);
                        subtotalEl.classList.remove('text-zinc-400');
                        subtotalEl.classList.add('text-zinc-900', 'font-bold');
                    } else {
                        subtotalEl.textContent = 'Rp 0';
                        subtotalEl.classList.add('text-zinc-400');
                        subtotalEl.classList.remove('text-zinc-900', 'font-bold');
                    }
                }

                if (btnMinus) {
                    btnMinus.disabled = (qty <= 0);
                }
                if (btnPlus) {
                    btnPlus.disabled = (qty >= maxRemaining);
                }

                if (qty > 0) {
                    card.classList.add('border-emerald-600', 'bg-emerald-50/20');
                    card.classList.remove('border-zinc-200/80');
                } else {
                    card.classList.remove('border-emerald-600', 'bg-emerald-50/20');
                    card.classList.add('border-zinc-200/80');
                }
            } else if (isSoldOut) {
                card.classList.remove('cursor-pointer', 'border-emerald-600', 'bg-emerald-50/20');
                if (input) input.value = 0;
                if (subtotalEl) {
                    subtotalEl.textContent = 'Rp 0';
                    subtotalEl.classList.add('hidden');
                }
                if (btnMinus) btnMinus.disabled = true;
                if (btnPlus) btnPlus.disabled = true;
            }
        });
    }

    // Trigger calculation API
    async function updateCalculation() {
        const selectedItems = getSelectedItems();
        const roundUp = partRoundUp ? partRoundUp.checked : false;

        if (Object.keys(selectedItems).length === 0) {
            partSubtotal.textContent = 'Rp 0';
            if (partPercentage) partPercentage.textContent = '0%';
            partFeeShare.textContent = 'Rp 0';
            partDiscountShare.textContent = '-Rp 0';
            if (partRoundUpBadge) partRoundUpBadge.classList.add('hidden');
            partGrandTotal.textContent = 'Rp 0';
            currentCalculatedTotal = 0;
            currentDynamicPayload = '';
            lastCalculatedData = null;
            return;
        }

        try {
            const response = await fetch(`/b/${slug}/calculate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    items: selectedItems,
                    round_up: roundUp
                })
            });

            const data = await response.json();
            if (data.success) {
                lastCalculatedData = data;
                currentCalculatedTotal = data.total_payable;
                currentDynamicPayload = data.dynamic_qris_payload;

                partSubtotal.textContent = formatRupiah(data.items_subtotal);
                if (partPercentage) {
                    partPercentage.textContent = (data.proportion_percent || 0) + '%';
                }
                partFeeShare.textContent = formatRupiah(data.fee_share + data.discount_share);
                partDiscountShare.textContent = '-' + formatRupiah(data.discount_share);

                if (data.round_up_extra > 0) {
                    partRoundUpBadge.textContent = '+' + formatRupiah(data.round_up_extra);
                    partRoundUpBadge.classList.remove('hidden');
                } else {
                    partRoundUpBadge.classList.add('hidden');
                }

                partGrandTotal.textContent = formatRupiah(data.total_payable);
            }
        } catch (err) {
            console.error('Calculation error:', err);
        }
    }

    // Bind Steppers and tactile card selection with dynamic remaining portion checks
    participantItemsContainer.addEventListener('click', function (e) {
        const btnMinus = e.target.closest('.btn-part-minus');
        const btnPlus = e.target.closest('.btn-part-plus');

        if (btnMinus) {
            e.stopPropagation();
            const card = btnMinus.closest('.item-selection-card');
            if (!card) return;
            const input = card.querySelector('.part-qty-input');
            if (!input) return;

            let current = parseInt(input.value) || 0;
            if (current > 0) {
                input.value = current - 1;
                updateCardVisuals();
                updateCalculation();
            }
            return;
        }

        if (btnPlus) {
            e.stopPropagation();
            const card = btnPlus.closest('.item-selection-card');
            if (!card) return;
            const input = card.querySelector('.part-qty-input');
            if (!input) return;

            let current = parseInt(input.value) || 0;
            const maxAllowed = parseInt(card.getAttribute('data-remaining')) || 0;
            if (current < maxAllowed) {
                input.value = current + 1;
                updateCardVisuals();
                updateCalculation();
            }
            return;
        }

        // Clicking card body outside stepper box opens the item contributors modal
        const card = e.target.closest('.item-selection-card');
        if (card && !e.target.closest('.item-stepper-box')) {
            const itemId = card.getAttribute('data-item-id');
            if (itemId) {
                openItemContributorsModal(itemId);
            }
        }
    });

    if (partRoundUp) {
        partRoundUp.addEventListener('change', updateCalculation);
    }

    // Always reset selection to 0 on page load/refresh (prevent browser cache persistence)
    function resetSelection() {
        const qtyInputs = participantItemsContainer.querySelectorAll('.part-qty-input');
        qtyInputs.forEach(input => {
            input.value = 0;
        });
        if (partRoundUp) {
            partRoundUp.checked = false;
        }
        updateCardVisuals();
        updateCalculation();
    }

    // Menu Search Filter (Client-side JS)
    const menuSearchInput = document.getElementById('menuSearchInput');
    const btnClearMenuSearch = document.getElementById('btnClearMenuSearch');
    const menuSearchEmptyState = document.getElementById('menuSearchEmptyState');

    if (menuSearchInput) {
        function filterMenuItems() {
            const query = (menuSearchInput.value || '').trim().toLowerCase();
            const cards = participantItemsContainer.querySelectorAll('.item-selection-card');
            let matchCount = 0;

            cards.forEach(card => {
                const name = (card.getAttribute('data-name') || '').toLowerCase();
                const matches = query === '' || name.includes(query);
                if (matches) {
                    card.classList.remove('hidden');
                    matchCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            if (btnClearMenuSearch) {
                if (query.length > 0) {
                    btnClearMenuSearch.classList.remove('hidden');
                } else {
                    btnClearMenuSearch.classList.add('hidden');
                }
            }

            if (menuSearchEmptyState) {
                if (matchCount === 0 && query !== '') {
                    menuSearchEmptyState.classList.remove('hidden');
                } else {
                    menuSearchEmptyState.classList.add('hidden');
                }
            }
        }

        menuSearchInput.addEventListener('input', filterMenuItems);

        if (btnClearMenuSearch) {
            btnClearMenuSearch.addEventListener('click', function () {
                menuSearchInput.value = '';
                filterMenuItems();
                menuSearchInput.focus();
            });
        }
    }

    resetSelection();
    window.addEventListener('pageshow', resetSelection);

    // Show Unified Payment Modal (QRIS & Bank)
    if (btnShowPaymentModal) {
        btnShowPaymentModal.addEventListener('click', function () {
            const selected = getSelectedItems();
            if (Object.keys(selected).length === 0) {
                if (window.Notiflix) {
                    Notiflix.Notify.warning('Silakan pilih minimal satu menu pesanan kamu terlebih dahulu!');
                } else {
                    alert('Silakan pilih minimal satu menu pesanan kamu terlebih dahulu!');
                }
                return;
            }

            const formattedTotal = formatRupiah(currentCalculatedTotal);
            if (modalQrisNominal) {
                modalQrisNominal.textContent = formattedTotal;
            }
            if (modalBankNominal) {
                modalBankNominal.textContent = formattedTotal;
            }

            if (dynamicQrCanvasContainer) {
                dynamicQrCanvasContainer.innerHTML = '';
                if (currentDynamicPayload) {
                    new QRCode(dynamicQrCanvasContainer, {
                        text: currentDynamicPayload,
                        width: 200,
                        height: 200,
                        colorDark: "#064E3B",
                        colorLight: "#FFFFFF",
                        correctLevel: QRCode.CorrectLevel.M
                    });
                } else {
                    dynamicQrCanvasContainer.innerHTML = '<p class="text-xs text-zinc-500 py-6">QRIS tidak tersedia untuk tagihan ini.</p>';
                }
            }

            // Populate Collapsible Payment Details
            const selectedDetails = getSelectedItemsDetails();
            if (payModalItemCountBadge) {
                const totalItemCount = selectedDetails.reduce((sum, item) => sum + item.qty, 0);
                payModalItemCountBadge.textContent = `${totalItemCount} item`;
            }

            if (payModalItemsList) {
                payModalItemsList.innerHTML = '';
                selectedDetails.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'px-3 py-2 flex items-center justify-between text-xs text-zinc-800';
                    row.innerHTML = `
                        <div class="flex items-center gap-2 min-w-0 pr-2">
                            <span class="font-bold text-emerald-800 tabular-nums flex-shrink-0">${item.qty}x</span>
                            <span class="font-medium text-zinc-800 truncate">${item.name}</span>
                        </div>
                        <span class="tabular-nums font-semibold text-zinc-700 flex-shrink-0">${formatRupiah(item.subtotal)}</span>
                    `;
                    payModalItemsList.appendChild(row);
                });
            }

            if (lastCalculatedData) {
                if (payModalBreakdownSubtotal) payModalBreakdownSubtotal.textContent = formatRupiah(lastCalculatedData.items_subtotal);

                if (payModalRowDelivery && payModalBreakdownDelivery) {
                    if (lastCalculatedData.delivery_fee_share > 0) {
                        payModalRowDelivery.classList.remove('hidden');
                        payModalRowDelivery.classList.add('flex');
                        payModalBreakdownDelivery.textContent = `+${formatRupiah(lastCalculatedData.delivery_fee_share)}`;
                    } else {
                        payModalRowDelivery.classList.add('hidden');
                        payModalRowDelivery.classList.remove('flex');
                    }
                }

                if (payModalRowService && payModalBreakdownService) {
                    if (lastCalculatedData.service_fee_share > 0) {
                        payModalRowService.classList.remove('hidden');
                        payModalRowService.classList.add('flex');
                        payModalBreakdownService.textContent = `+${formatRupiah(lastCalculatedData.service_fee_share)}`;
                    } else {
                        payModalRowService.classList.add('hidden');
                        payModalRowService.classList.remove('flex');
                    }
                }

                if (payModalRowDiscount && payModalBreakdownDiscount) {
                    if (lastCalculatedData.discount_share > 0) {
                        payModalRowDiscount.classList.remove('hidden');
                        payModalRowDiscount.classList.add('flex');
                        payModalBreakdownDiscount.textContent = `-${formatRupiah(lastCalculatedData.discount_share)}`;
                    } else {
                        payModalRowDiscount.classList.add('hidden');
                        payModalRowDiscount.classList.remove('flex');
                    }
                }

                if (payModalRowRoundUp && payModalBreakdownRoundUp) {
                    if (lastCalculatedData.round_up_extra > 0) {
                        payModalRowRoundUp.classList.remove('hidden');
                        payModalRowRoundUp.classList.add('flex');
                        payModalBreakdownRoundUp.textContent = `+${formatRupiah(lastCalculatedData.round_up_extra)}`;
                    } else {
                        payModalRowRoundUp.classList.add('hidden');
                        payModalRowRoundUp.classList.remove('flex');
                    }
                }

                if (payModalBreakdownTotal) {
                    payModalBreakdownTotal.textContent = formatRupiah(lastCalculatedData.total_payable);
                }
            }

            if (paymentModal) {
                paymentModal.classList.remove('hidden');
            }
        });
    }

    // Toggle Collapsible Details in Payment Modal
    if (btnTogglePayModalDetails && payModalDetailsContent) {
        btnTogglePayModalDetails.addEventListener('click', function () {
            const isHidden = payModalDetailsContent.classList.contains('hidden');
            if (isHidden) {
                payModalDetailsContent.classList.remove('hidden');
                if (payModalDetailsChevron) payModalDetailsChevron.classList.add('rotate-180');
            } else {
                payModalDetailsContent.classList.add('hidden');
                if (payModalDetailsChevron) payModalDetailsChevron.classList.remove('rotate-180');
            }
        });
    }

    if (btnClosePaymentModal && paymentModal) {
        btnClosePaymentModal.addEventListener('click', () => paymentModal.classList.add('hidden'));
    }



    // Switch from Payment modal to Claim modal
    if (btnModalConfirmPaid) {
        btnModalConfirmPaid.addEventListener('click', function () {
            if (paymentModal) {
                paymentModal.classList.add('hidden');
            }
            openClaimModal();
        });
    }

    // Download QRIS Card as Image
    if (btnDownloadQrisCard) {
        btnDownloadQrisCard.addEventListener('click', function () {
            if (!dynamicQrCanvasContainer) return;
            const qrCanvas = dynamicQrCanvasContainer.querySelector('canvas');
            const qrImg = dynamicQrCanvasContainer.querySelector('img');

            if (!qrCanvas && (!qrImg || !qrImg.src)) {
                if (window.Notiflix) Notiflix.Notify.failure('QR Code belum selesai dimuat.');
                return;
            }

            const downloadCard = function (qrSource) {
                const cardCanvas = document.createElement('canvas');
                const ctx = cardCanvas.getContext('2d');
                const scale = 3; // High resolution
                const cardWidth = 380 * scale;
                const cardHeight = 520 * scale;

                cardCanvas.width = cardWidth;
                cardCanvas.height = cardHeight;

                // Background
                ctx.fillStyle = '#F4F4F5';
                ctx.fillRect(0, 0, cardWidth, cardHeight);

                // Top decorative banner
                ctx.fillStyle = '#064E3B'; // Emerald 900
                ctx.fillRect(0, 0, cardWidth, 68 * scale);

                // Top Header Text
                ctx.fillStyle = '#ffffff';
                ctx.font = `bold ${14 * scale}px "Plus Jakarta Sans", sans-serif`;
                ctx.textAlign = 'left';
                ctx.fillText('PayMe • QRIS DINAMIS', 24 * scale, 32 * scale);

                ctx.font = `${10 * scale}px "Plus Jakarta Sans", sans-serif`;
                ctx.fillStyle = '#A7F3D0';
                ctx.fillText('Scan & Bayar Otomatis Nominal Pas', 24 * scale, 50 * scale);

                // Destination Info
                const merchantName = "{{ addslashes($bill->qris_merchant_name ?: $bill->user->name) }}";
                const merchantCity = "{{ addslashes($bill->qris_merchant_city ?: 'Indonesia') }}";
                const billTitle = "{{ addslashes($bill->title) }}";

                ctx.textAlign = 'center';
                ctx.fillStyle = '#71717A';
                ctx.font = `600 ${9 * scale}px "Plus Jakarta Sans", sans-serif`;
                ctx.fillText('TUJUAN PEMBAYARAN', cardWidth / 2, 95 * scale);

                ctx.fillStyle = '#18181B';
                ctx.font = `bold ${15 * scale}px "Plus Jakarta Sans", sans-serif`;
                ctx.fillText(merchantName, cardWidth / 2, 115 * scale);

                ctx.fillStyle = '#71717A';
                ctx.font = `${10 * scale}px "Plus Jakarta Sans", sans-serif`;
                ctx.fillText(`${merchantCity} • Host: {{ addslashes($bill->user->name) }}`, cardWidth / 2, 132 * scale);

                // QR Box Background
                const qrBoxSize = 220 * scale;
                const qrBoxX = (cardWidth - qrBoxSize) / 2;
                const qrBoxY = 150 * scale;

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
                const amountY = 398 * scale;
                ctx.fillStyle = '#71717A';
                ctx.font = `600 ${9 * scale}px "Plus Jakarta Sans", sans-serif`;
                ctx.fillText('TOTAL TAGIHAN KAMU', cardWidth / 2, amountY);

                ctx.fillStyle = '#064E3B';
                ctx.font = `900 ${22 * scale}px "Plus Jakarta Sans", sans-serif`;
                ctx.fillText(formatRupiah(currentCalculatedTotal), cardWidth / 2, amountY + (25 * scale));

                // Footer Note
                ctx.fillStyle = '#A1A1AA';
                ctx.font = `${9 * scale}px "Plus Jakarta Sans", sans-serif`;
                ctx.fillText(`Tagihan: ${billTitle}`, cardWidth / 2, amountY + (48 * scale));
                ctx.fillText('BCA, Mandiri, BRI, GoPay, OVO, ShopeePay, DANA dll.', cardWidth / 2, amountY + (63 * scale));

                // Border around card
                ctx.strokeStyle = '#E4E4E7';
                ctx.lineWidth = 1 * scale;
                ctx.strokeRect(0, 0, cardWidth, cardHeight);

                // Trigger Download
                const link = document.createElement('a');
                link.download = `QRIS-PayMe-${slug}.png`;
                link.href = cardCanvas.toDataURL('image/png');
                link.click();

                if (window.Notiflix) Notiflix.Notify.success('Card QRIS berhasil diunduh!');
            };

            if (qrCanvas) {
                downloadCard(qrCanvas);
            } else if (qrImg) {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => downloadCard(img);
                img.src = qrImg.src;
            }
        });
    }

    // ==========================================
    // DEVELOPER DONATION & COFFEE SUPPORT ALERT
    // ==========================================
    function showPostTransactionDonationAlert(payerName, amount) {
        if (!postTransactionAlert) return;
        if (postTxSuccessTitle && payerName) {
            postTxSuccessTitle.textContent = `Terima kasih, ${payerName}! 🎉`;
        }
        if (postTxSuccessDesc && amount) {
            postTxSuccessDesc.innerHTML = `Klaim pembayaranmu sebesar <strong>${formatRupiah(amount)}</strong> telah dicatat. <br>Suka PayMe? Yuk traktir kopi mas dev! ☕`;
        }
        postTransactionAlert.classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    if (btnOpenCoffeeModalFromAlert) {
        btnOpenCoffeeModalFromAlert.addEventListener('click', function () {
            if (typeof window.openBuyCoffeeModal === 'function') {
                window.openBuyCoffeeModal();
            }
        });
    }

    if (btnDismissPostTxAlert && postTransactionAlert) {
        btnDismissPostTxAlert.addEventListener('click', function () {
            postTransactionAlert.classList.add('hidden');
        });
    }

    if (new URLSearchParams(window.location.search).has('donasi')) {
        showPostTransactionDonationAlert('Teman', 0);
        if (typeof window.openBuyCoffeeModal === 'function') {
            window.openBuyCoffeeModal();
        }
    }

    // Modal Copy Bank Account Buttons
    const modalCopyAccBtns = document.querySelectorAll('.btn-copy-acc-modal');
    modalCopyAccBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const acc = this.getAttribute('data-acc');
            const bank = this.getAttribute('data-bank');
            navigator.clipboard.writeText(acc).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-light fa-check text-emerald-600"></i><span>Disalin</span>';
                if (window.Notiflix) Notiflix.Notify.success(`Nomor rekening ${bank} (${acc}) berhasil disalin!`);
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 2000);
            });
        });
    });

    function parseRupiahInput(str) {
        if (!str) return 0;
        const cleaned = String(str).replace(/[^0-9]/g, '');
        return parseInt(cleaned || '0', 10);
    }

    function updateTipBreakdownUI(enteredAmount, exact) {
        if (!claimTipPreview || !claimTipBreakdownRow || !claimTotalTransferRow || !claimTotalTransferPreview) return;
        if (enteredAmount > exact) {
            const tip = enteredAmount - exact;
            claimTipPreview.textContent = `+${formatRupiah(tip)}`;
            claimTipBreakdownRow.classList.remove('hidden');
            claimTipBreakdownRow.classList.add('flex');

            claimTotalTransferPreview.textContent = formatRupiah(enteredAmount);
            claimTotalTransferRow.classList.remove('hidden');
            claimTotalTransferRow.classList.add('flex');
        } else {
            claimTipBreakdownRow.classList.add('hidden');
            claimTipBreakdownRow.classList.remove('flex');

            claimTotalTransferRow.classList.add('hidden');
            claimTotalTransferRow.classList.remove('flex');
        }
    }

    function syncClaimModalAmounts(customVal) {
        const exact = Math.round(currentCalculatedTotal || 0);
        if (claimAmountPreview) {
            claimAmountPreview.textContent = formatRupiah(exact);
        }

        const method = (claimPaymentMethod ? claimPaymentMethod.value : 'qris').toLowerCase();
        const isQris = method === 'qris';

        if (isQris) {
            if (claimCustomAmount) {
                claimCustomAmount.readOnly = true;
                claimCustomAmount.classList.add('bg-zinc-100', 'text-zinc-600', 'cursor-not-allowed');
                claimCustomAmount.classList.remove('bg-white', 'text-zinc-900', 'border-emerald-600');
                claimCustomAmount.value = new Intl.NumberFormat('id-ID').format(exact);
            }
            if (claimMethodLockNotice) {
                claimMethodLockNotice.innerHTML = '<i class="fa-light fa-lock text-[10px]"></i> Terkunci (QRIS)';
            }
            if (quickTipChipsContainer) {
                quickTipChipsContainer.classList.add('hidden');
                quickTipChipsContainer.classList.remove('flex');
            }
            updateTipBreakdownUI(exact, exact);
        } else {
            if (claimCustomAmount) {
                claimCustomAmount.readOnly = false;
                claimCustomAmount.classList.remove('bg-zinc-100', 'text-zinc-600', 'cursor-not-allowed');
                claimCustomAmount.classList.add('bg-white', 'text-zinc-900');

                let targetVal = customVal !== undefined ? customVal : parseRupiahInput(claimCustomAmount.value);
                if (!targetVal || targetVal < exact) {
                    targetVal = exact;
                }
                claimCustomAmount.value = new Intl.NumberFormat('id-ID').format(targetVal);
                updateTipBreakdownUI(targetVal, exact);
            }
            if (claimMethodLockNotice) {
                claimMethodLockNotice.innerHTML = '<i class="fa-light fa-pen-to-square text-[10px] text-emerald-600"></i> Bebas edit / tambah Tip';
            }
            if (quickTipChipsContainer) {
                quickTipChipsContainer.classList.remove('hidden');
                quickTipChipsContainer.classList.add('flex');
            }
        }
    }

    // Open Claim Modal
    function openClaimModal() {
        const selected = getSelectedItems();
        if (Object.keys(selected).length === 0) {
            if (window.Notiflix) {
                Notiflix.Notify.warning('Silakan pilih minimal satu menu pesanan kamu terlebih dahulu!');
            } else {
                alert('Silakan pilih minimal satu menu pesanan kamu terlebih dahulu!');
            }
            return;
        }

        // Populate Collapsible Claim Details
        const selectedDetails = getSelectedItemsDetails();
        if (claimModalItemsList) {
            claimModalItemsList.innerHTML = '';
            selectedDetails.forEach(item => {
                const row = document.createElement('div');
                row.className = 'px-3 py-1.5 flex items-center justify-between text-xs text-zinc-800';
                row.innerHTML = `
                    <div class="flex items-center gap-1.5 min-w-0 pr-2">
                        <span class="font-bold text-emerald-800 tabular-nums flex-shrink-0">${item.qty}x</span>
                        <span class="font-medium text-zinc-800 truncate">${item.name}</span>
                    </div>
                    <span class="tabular-nums font-semibold text-zinc-700 flex-shrink-0">${formatRupiah(item.subtotal)}</span>
                `;
                claimModalItemsList.appendChild(row);
            });
        }

        if (lastCalculatedData) {
            if (claimModalBreakdownSubtotal) claimModalBreakdownSubtotal.textContent = formatRupiah(lastCalculatedData.items_subtotal);

            if (claimModalRowDelivery && claimModalBreakdownDelivery) {
                if (lastCalculatedData.delivery_fee_share > 0) {
                    claimModalRowDelivery.classList.remove('hidden');
                    claimModalRowDelivery.classList.add('flex');
                    claimModalBreakdownDelivery.textContent = `+${formatRupiah(lastCalculatedData.delivery_fee_share)}`;
                } else {
                    claimModalRowDelivery.classList.add('hidden');
                    claimModalRowDelivery.classList.remove('flex');
                }
            }

            if (claimModalRowService && claimModalBreakdownService) {
                if (lastCalculatedData.service_fee_share > 0) {
                    claimModalRowService.classList.remove('hidden');
                    claimModalRowService.classList.add('flex');
                    claimModalBreakdownService.textContent = `+${formatRupiah(lastCalculatedData.service_fee_share)}`;
                } else {
                    claimModalRowService.classList.add('hidden');
                    claimModalRowService.classList.remove('flex');
                }
            }

            if (claimModalRowDiscount && claimModalBreakdownDiscount) {
                if (lastCalculatedData.discount_share > 0) {
                    claimModalRowDiscount.classList.remove('hidden');
                    claimModalRowDiscount.classList.add('flex');
                    claimModalBreakdownDiscount.textContent = `-${formatRupiah(lastCalculatedData.discount_share)}`;
                } else {
                    claimModalRowDiscount.classList.add('hidden');
                    claimModalRowDiscount.classList.remove('flex');
                }
            }

            if (claimModalRowRoundUp && claimModalBreakdownRoundUp) {
                if (lastCalculatedData.round_up_extra > 0) {
                    claimModalRowRoundUp.classList.remove('hidden');
                    claimModalRowRoundUp.classList.add('flex');
                    claimModalBreakdownRoundUp.textContent = `+${formatRupiah(lastCalculatedData.round_up_extra)}`;
                } else {
                    claimModalRowRoundUp.classList.add('hidden');
                    claimModalRowRoundUp.classList.remove('flex');
                }
            }

            if (claimModalBreakdownTotal) {
                claimModalBreakdownTotal.textContent = formatRupiah(lastCalculatedData.total_payable);
            }
        }

        syncClaimModalAmounts();
        if (claimModalAlert) {
            claimModalAlert.className = 'hidden';
            claimModalAlert.innerHTML = '';
        }
        claimModal.classList.remove('hidden');
        claimPayerName.focus();
    }

    // Toggle Collapsible Details in Claim Modal
    if (btnToggleClaimModalDetails && claimModalDetailsContent) {
        btnToggleClaimModalDetails.addEventListener('click', function () {
            const isHidden = claimModalDetailsContent.classList.contains('hidden');
            if (isHidden) {
                claimModalDetailsContent.classList.remove('hidden');
                if (claimModalDetailsChevron) claimModalDetailsChevron.classList.add('rotate-180');
            } else {
                claimModalDetailsContent.classList.add('hidden');
                if (claimModalDetailsChevron) claimModalDetailsChevron.classList.remove('rotate-180');
            }
        });
    }

    if (claimPaymentMethod) {
        claimPaymentMethod.addEventListener('change', function () {
            syncClaimModalAmounts();
        });
    }

    if (claimCustomAmount) {
        claimCustomAmount.addEventListener('input', function () {
            const exact = Math.round(currentCalculatedTotal || 0);
            const raw = parseRupiahInput(this.value);
            this.value = raw > 0 ? new Intl.NumberFormat('id-ID').format(raw) : '';
            updateTipBreakdownUI(raw, exact);
            if (raw < exact) {
                claimModalAlert.className = 'p-2.5 rounded-xl text-xs font-semibold bg-amber-50 text-amber-900 border border-amber-200 block';
                claimModalAlert.innerHTML = `<i class="fa-light fa-circle-exclamation text-amber-600 mr-1"></i> Nominal kurang dari tagihan pokok (${formatRupiah(exact)}).`;
            } else {
                claimModalAlert.className = 'hidden';
                claimModalAlert.innerHTML = '';
            }
        });
    }

    document.querySelectorAll('.btn-quick-chip').forEach(btn => {
        btn.addEventListener('click', function () {
            const chip = this.dataset.chip;
            const exact = Math.round(currentCalculatedTotal || 0);
            let target = exact;
            if (chip === '2000') target = exact + 2000;
            else if (chip === '5000') target = exact + 5000;
            else if (chip === '10000') target = exact + 10000;
            else target = exact;

            syncClaimModalAmounts(target);
            if (claimModalAlert) {
                claimModalAlert.className = 'hidden';
                claimModalAlert.innerHTML = '';
            }
        });
    });

    if (btnOpenClaimModal) {
        btnOpenClaimModal.addEventListener('click', openClaimModal);
    }
    if (btnCloseClaimModal) {
        btnCloseClaimModal.addEventListener('click', () => claimModal.classList.add('hidden'));
    }
    if (btnCancelClaimModal) {
        btnCancelClaimModal.addEventListener('click', () => claimModal.classList.add('hidden'));
    }

    // ==========================================
    // ITEM CONTRIBUTORS MODAL HANDLERS
    // ==========================================
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    const itemContributorsModal = document.getElementById('itemContributorsModal');
    const btnCloseItemContributorsModal = document.getElementById('btnCloseItemContributorsModal');
    const btnBottomCloseItemContributorsModal = document.getElementById('btnBottomCloseItemContributorsModal');

    function closeItemContributorsModal() {
        if (itemContributorsModal) itemContributorsModal.classList.add('hidden');
    }

    if (btnCloseItemContributorsModal) {
        btnCloseItemContributorsModal.addEventListener('click', closeItemContributorsModal);
    }
    if (btnBottomCloseItemContributorsModal) {
        btnBottomCloseItemContributorsModal.addEventListener('click', closeItemContributorsModal);
    }
    if (itemContributorsModal) {
        itemContributorsModal.addEventListener('click', function (e) {
            if (e.target === itemContributorsModal) closeItemContributorsModal();
        });
    }

    function openItemContributorsModal(itemId) {
        const card = participantItemsContainer.querySelector(`.item-selection-card[data-item-id="${itemId}"]`);
        if (!card) return;

        const itemName = card.querySelector('.break-words')?.textContent?.trim() || 'Menu';
        const price = parseFloat(card.getAttribute('data-price')) || 0;
        const remaining = parseInt(card.getAttribute('data-remaining')) || 0;
        const total = parseInt(card.getAttribute('data-total')) || 0;
        const claimed = Math.max(0, total - remaining);

        const titleEl = document.getElementById('itemContributorsModalTitle');
        const itemNameEl = document.getElementById('itemModalItemName');
        const itemPriceEl = document.getElementById('itemModalItemPrice');
        const stockBadgeEl = document.getElementById('itemModalStockBadge');
        const summaryEl = document.getElementById('itemModalTotalClaimedSummary');

        if (titleEl) titleEl.textContent = 'Rincian Pembayar Item';
        if (itemNameEl) itemNameEl.textContent = itemName;
        if (itemPriceEl) itemPriceEl.textContent = '@ ' + formatRupiah(price);
        if (stockBadgeEl) stockBadgeEl.textContent = `${claimed}/${total} Item Terklaim (Sisa ${remaining})`;
        if (summaryEl) summaryEl.textContent = `Total: ${claimed} item`;

        // Gather all contributors from window.claimDetailsData
        const claims = Object.values(window.claimDetailsData || {});
        const contributors = [];

        claims.forEach(claim => {
            (claim.items || []).forEach(it => {
                if (String(it.item_id) === String(itemId)) {
                    contributors.push({
                        claim_id: claim.id,
                        payer_name: claim.payer_name,
                        qty: it.qty,
                        price: it.price,
                        subtotal: it.subtotal,
                        status: claim.status,
                        created_at_relative: claim.created_at_relative || '',
                        created_at_formatted: claim.created_at_formatted || '',
                    });
                }
            });
        });

        const listContainer = document.getElementById('itemContributorsList');
        const emptyState = document.getElementById('itemContributorsEmptyState');
        if (!listContainer) return;
        listContainer.innerHTML = '';

        if (contributors.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
        } else {
            if (emptyState) emptyState.classList.add('hidden');
            contributors.forEach(c => {
                const isConfirmed = c.status === 'confirmed';
                const statusBadge = isConfirmed
                    ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200"><i class="fa-light fa-check text-[9px]"></i> Lunas</span>`
                    : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200"><i class="fa-light fa-clock text-[9px]"></i> Menunggu</span>`;

                const itemRow = document.createElement('div');
                itemRow.className = 'p-3 rounded-xl bg-zinc-50 border border-zinc-200/80 flex items-center justify-between gap-3 text-xs';
                itemRow.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs flex-shrink-0">
                            ${escapeHtml(c.payer_name.charAt(0).toUpperCase())}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-zinc-900 truncate">${escapeHtml(c.payer_name)}</div>
                            <div class="text-[11px] text-zinc-500">${escapeHtml(c.created_at_relative || 'Baru saja')}</div>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 space-y-0.5">
                        <div class="font-bold text-zinc-900 tabular-nums">${c.qty} item <span class="text-zinc-400 font-normal">(${formatRupiah(c.subtotal)})</span></div>
                        <div>${statusBadge}</div>
                    </div>
                `;
                listContainer.appendChild(itemRow);
            });
        }

        if (itemContributorsModal) itemContributorsModal.classList.remove('hidden');
    }

    // Update menu items remaining portions and reset user steppers dynamically
    function updateMenuRemainingQuantities(itemsRemaining) {
        if (!itemsRemaining) return;
        const cards = participantItemsContainer.querySelectorAll('.item-selection-card');
        cards.forEach(card => {
            const itemId = card.getAttribute('data-item-id');
            if (!itemsRemaining[itemId]) return;

            const remaining = parseInt(itemsRemaining[itemId].remaining) || 0;
            const total = parseInt(itemsRemaining[itemId].total) || 0;

            card.setAttribute('data-remaining', remaining);
            card.setAttribute('data-total', total);

            const isSoldOut = remaining <= 0;
            const badgeEl = card.querySelector('.item-stock-badge');
            const stepperBox = card.querySelector('.item-stepper-box');
            const subtotalEl = card.querySelector('.item-subtotal');
            const soldOutText = card.querySelector('.item-sold-out-text');
            const input = card.querySelector('.part-qty-input');
            const btnMinus = card.querySelector('.btn-part-minus');
            const btnPlus = card.querySelector('.btn-part-plus');

            if (isSoldOut) {
                card.className = 'item-selection-card p-3 sm:p-3.5 rounded-xl border transition-all flex items-center justify-between gap-3 bg-zinc-50/60 border-zinc-200/60 opacity-60 cursor-pointer';
                if (badgeEl) {
                    badgeEl.className = 'item-stock-badge text-[10px] px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-500 font-semibold border border-zinc-200/60';
                    badgeEl.innerHTML = 'Habis terbayar';
                }
                if (stepperBox) stepperBox.classList.add('hidden');
                if (subtotalEl) {
                    subtotalEl.classList.add('hidden');
                    subtotalEl.textContent = 'Rp 0';
                }
                if (soldOutText) soldOutText.classList.remove('hidden');
                if (input) {
                    input.value = 0;
                    input.max = 0;
                }
                if (btnMinus) btnMinus.disabled = true;
                if (btnPlus) btnPlus.disabled = true;
            } else {
                card.className = 'item-selection-card p-3 sm:p-3.5 rounded-xl border transition-all flex items-center justify-between gap-3 bg-white border-zinc-200/80 hover:border-emerald-600/70 shadow-2xs cursor-pointer';
                if (badgeEl) {
                    badgeEl.className = 'item-stock-badge text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/50';
                    badgeEl.innerHTML = `sisa ${remaining}/${total} item`;
                }
                if (stepperBox) stepperBox.classList.remove('hidden');
                if (subtotalEl) subtotalEl.classList.remove('hidden');
                if (soldOutText) soldOutText.classList.add('hidden');
                if (input) {
                    input.max = remaining;
                    let currentVal = parseInt(input.value) || 0;
                    if (currentVal > remaining) {
                        input.value = remaining;
                    }
                }
                if (btnMinus) {
                    const currentVal = input ? (parseInt(input.value) || 0) : 0;
                    btnMinus.disabled = (currentVal <= 0);
                }
                if (btnPlus) {
                    const currentVal = input ? (parseInt(input.value) || 0) : 0;
                    btnPlus.disabled = (currentVal >= remaining);
                }
            }
        });

        updateCardVisuals();
        updateCalculation();
    }

    // Render newly added claim card directly into Riwayat Pembayaran (100% AJAX, no reload)
    function renderNewClaimCard(claim) {
        const container = document.getElementById('claimsListContainer');
        if (!container) return;

        const emptyState = document.getElementById('emptyClaimsHistoryState');
        if (emptyState) emptyState.classList.add('hidden');
        container.classList.remove('hidden');

        const initial = claim.payer_name ? claim.payer_name.trim().charAt(0).toUpperCase() : '?';
        const pm = (claim.payment_method || 'qris').toLowerCase();
        let methodBadge = `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200">${(claim.payment_method || 'QRIS').toUpperCase()}</span>`;
        if (pm.includes('qris')) {
            methodBadge = `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200"><i class="fa-light fa-qrcode text-[10px]"></i> QRIS</span>`;
        } else if (pm.includes('cash') || pm.includes('tunai')) {
            methodBadge = `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/50"><i class="fa-light fa-money-bill-wave text-[10px]"></i> Tunai</span>`;
        }

        const isHost = {{ $isHost ? 'true' : 'false' }};
        let hostActionsHtml = '';
        if (isHost) {
            hostActionsHtml = `
                <div class="host-actions-row pt-2 border-t border-zinc-100 flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                    <button type="button"
                            class="btn-confirm-claim touch-target px-3 py-1.5 rounded-lg bg-emerald-800 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all cursor-pointer"
                            data-claim-id="${claim.id}"
                            data-name="${claim.payer_name}"
                            data-amount="${formatRupiah(claim.amount)}">
                        <i class="fa-light fa-check text-xs"></i>
                        <span>Konfirmasi Dana Masuk</span>
                    </button>
                    <button type="button"
                            class="btn-reject-claim touch-target p-1.5 px-2.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors cursor-pointer text-xs font-semibold inline-flex items-center gap-1"
                            data-claim-id="${claim.id}"
                            data-name="${claim.payer_name}"
                            title="Tolak / Hapus Klaim">
                        <i class="fa-light fa-trash-can text-xs"></i>
                        <span class="hidden sm:inline">Tolak</span>
                    </button>
                </div>
            `;
        }

        const card = document.createElement('div');
        card.className = 'claim-history-card p-3.5 sm:p-4 rounded-xl bg-white border border-emerald-500/50 ring-2 ring-emerald-500/20 hover:border-emerald-600/70 hover:shadow-2xs transition-all cursor-pointer space-y-2 animate-in fade-in slide-in-from-top-2 duration-300';
        card.dataset.claimId = claim.id;
        card.setAttribute('role', 'button');
        card.setAttribute('tabindex', '0');
        card.setAttribute('title', `Klik untuk melihat rincian pembayaran ${claim.payer_name}`);

        card.innerHTML = `
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap min-w-0">
                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-900 font-black text-xs flex items-center justify-center flex-shrink-0">
                        ${initial}
                    </div>
                    <span class="font-bold text-zinc-900 text-sm truncate">${claim.payer_name}</span>
                    <span class="claim-status-badge inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/70 animate-pulse">
                        <i class="fa-light fa-hourglass-clock text-amber-600"></i>
                        <span>Menunggu Konfirmasi Host</span>
                    </span>
                    ${methodBadge}
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="text-right">
                        <div class="text-sm sm:text-base font-extrabold text-emerald-900 tabular-nums">
                            ${formatRupiah(claim.amount)}
                        </div>
                        ${(claim.tip_amount && claim.tip_amount > 0) ? `
                            <div class="text-[10px] text-emerald-700 font-semibold flex items-center justify-end gap-1">
                                <i class="fa-light fa-gift text-[9px]"></i>
                                <span>+Tip ${formatRupiah(claim.tip_amount)}</span>
                            </div>
                        ` : ''}
                    </div>
                    <i class="fa-light fa-chevron-right text-zinc-400 text-xs"></i>
                </div>
            </div>
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-zinc-100 text-[11px] text-zinc-500">
                <div class="flex items-center gap-1.5">
                    <i class="fa-light fa-receipt text-zinc-400 text-[10px]"></i>
                    <span>${claim.items_count || 1} jenis (${claim.items_total_qty || 1} item)</span>
                    <span class="text-zinc-300">&bull;</span>
                    <span>Baru saja</span>
                </div>
                <span class="text-emerald-700 font-semibold hover:underline flex items-center gap-1">
                    <span>Rincian</span>
                    <i class="fa-light fa-arrow-up-right-from-square text-[9px]"></i>
                </span>
            </div>
            ${hostActionsHtml}
        `;

        card.addEventListener('click', function () {
            openClaimDetail(claim.id);
        });

        if (isHost) {
            const confirmBtn = card.querySelector('.btn-confirm-claim');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', handleConfirmClaimClick);
            }
            const rejectBtn = card.querySelector('.btn-reject-claim');
            if (rejectBtn) {
                rejectBtn.addEventListener('click', handleRejectClaimClick);
            }
        }

        container.prepend(card);

        setTimeout(() => {
            card.classList.remove('border-emerald-500/50', 'ring-2', 'ring-emerald-500/20');
            card.classList.add('border-zinc-200/90');
        }, 3000);
    }

    // Submit Claim Form with Anti Double-Submit & Pure AJAX (Zero Reload)
    claimPaymentForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (isClaimSubmitting) return;

        const selected = getSelectedItems();
        const name = claimPayerName.value.trim();
        const method = claimPaymentMethod.value;

        if (!name) {
            if (window.Notiflix) {
                Notiflix.Notify.warning('Silakan isi nama kamu terlebih dahulu.');
            } else {
                alert('Silakan isi nama kamu.');
            }
            claimPayerName.focus();
            return;
        }

        // Validate unique payer name per bill
        const isNameDuplicate = Object.values(window.claimDetailsData || {}).some(
            c => (c.payer_name || '').trim().toLowerCase() === name.toLowerCase()
        );

        if (isNameDuplicate) {
            const errorMsg = `Nama "${name}" sudah terdaftar dalam klaim tagihan ini. Gunakan nama lain atau tambahkan pembeda (contoh: ${name} 2).`;
            if (window.Notiflix) {
                Notiflix.Notify.warning(errorMsg);
            } else {
                alert(errorMsg);
            }
            claimModalAlert.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-900 border border-rose-200 block';
            claimModalAlert.innerHTML = `<i class="fa-light fa-circle-exclamation text-rose-600 mr-1"></i> ${escapeHtml(errorMsg)}`;
            claimPayerName.focus();
            return;
        }

        if (Object.keys(selected).length === 0) {
            if (window.Notiflix) {
                Notiflix.Notify.warning('Silakan pilih minimal satu menu pesanan kamu!');
            } else {
                alert('Silakan pilih minimal satu menu pesanan kamu!');
            }
            return;
        }

        const exact = Math.round(currentCalculatedTotal || 0);
        let actualAmount = exact;
        const isQris = method.toLowerCase() === 'qris';
        if (!isQris && claimCustomAmount) {
            actualAmount = parseRupiahInput(claimCustomAmount.value);
            if (actualAmount < exact) {
                const errorMsg = `Nominal yang dibayarkan (${formatRupiah(actualAmount)}) tidak boleh lebih kecil dari tagihan kamu (${formatRupiah(exact)}).`;
                if (window.Notiflix) Notiflix.Notify.warning(errorMsg);
                claimModalAlert.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-900 border border-rose-200 block';
                claimModalAlert.innerHTML = `<i class="fa-light fa-circle-exclamation text-rose-600 mr-1"></i> ${escapeHtml(errorMsg)}`;
                claimCustomAmount.focus();
                return;
            }
        }

        isClaimSubmitting = true;
        btnSubmitClaim.disabled = true;
        btnSubmitClaim.classList.add('opacity-50', 'pointer-events-none');
        btnSubmitClaim.innerHTML = '<div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div><span>Mengirim...</span>';

        if (window.Notiflix) {
            Notiflix.Loading.pulse('Mengirim klaim pembayaran...');
        }

        try {
            const response = await fetch(`/b/${slug}/claim`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payer_name: name,
                    payment_method: method,
                    items: selected,
                    actual_amount: actualAmount,
                    round_up: partRoundUp ? partRoundUp.checked : false
                })
            });

            const data = await response.json();
            if (window.Notiflix) {
                Notiflix.Loading.remove();
            }

            if (response.ok && data.success) {
                if (window.Notiflix) {
                    Notiflix.Notify.success(data.message || 'Klaim pembayaran berhasil dikirim!');
                }
                claimModal.classList.add('hidden');

                // 1. Cache new claim in local storage map
                if (data.claim_data && data.claim_id) {
                    if (!window.claimDetailsData) window.claimDetailsData = {};
                    window.claimDetailsData[data.claim_id] = data.claim_data;
                }

                // 2. Prepend new claim card directly via AJAX
                if (data.claim_data) {
                    renderNewClaimCard(data.claim_data);
                }

                // 3. Update menu item remaining stock and reset steppers
                if (data.items_remaining) {
                    updateMenuRemainingQuantities(data.items_remaining);
                }

                // 4. Update counters (pending and total) and summary
                updatePendingCounters();
                const totalClaimsCountEl = document.getElementById('totalClaimsCount');
                if (totalClaimsCountEl) {
                    totalClaimsCountEl.innerText = Object.keys(window.claimDetailsData || {}).length;
                }
                updateBillSummary(data.bill_summary);

                // 5. Reset form
                claimPaymentForm.reset();
                if (partRoundUp) partRoundUp.checked = false;
                resetSelection();

                // 6. Notify user with post-transaction alert & optional developer donation
                showPostTransactionDonationAlert(name, data.amount || actualAmount);

                isClaimSubmitting = false;
                btnSubmitClaim.disabled = false;
                btnSubmitClaim.classList.remove('opacity-50', 'pointer-events-none');
                btnSubmitClaim.innerHTML = '<span>Kirim Klaim Pembayaran</span><i class="fa-light fa-arrow-right text-xs"></i>';
            } else {
                isClaimSubmitting = false;
                btnSubmitClaim.disabled = false;
                btnSubmitClaim.classList.remove('opacity-50', 'pointer-events-none');
                btnSubmitClaim.innerHTML = '<span>Kirim Klaim Pembayaran</span><i class="fa-light fa-arrow-right text-xs"></i>';
                if (window.Notiflix) {
                    Notiflix.Notify.failure(data.message || 'Gagal mengirim klaim.');
                } else {
                    claimModalAlert.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-900 border border-rose-200 block';
                    claimModalAlert.innerHTML = `<i class="fa-light fa-circle-exclamation text-rose-600 mr-1"></i> ${data.message || 'Gagal mengirim klaim.'}`;
                }
            }
        } catch (err) {
            if (window.Notiflix) {
                Notiflix.Loading.remove();
                Notiflix.Notify.failure('Terjadi kesalahan jaringan. Silakan coba lagi.');
            } else {
                claimModalAlert.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-900 border border-rose-200 block';
                claimModalAlert.innerHTML = '<i class="fa-light fa-circle-exclamation text-rose-600 mr-1"></i> Terjadi kesalahan jaringan.';
            }
            isClaimSubmitting = false;
            btnSubmitClaim.disabled = false;
            btnSubmitClaim.classList.remove('opacity-50', 'pointer-events-none');
            btnSubmitClaim.innerHTML = '<span>Kirim Klaim Pembayaran</span><i class="fa-light fa-arrow-right text-xs"></i>';
        }
    });

    // ==========================================
    // HOST ACTIONS: CONFIRM, REJECT & BATCH CONFIRM VIA AJAX
    // ==========================================
    function updateBillSummary(summary) {
        if (!summary) {
            const claims = Object.values(window.claimDetailsData || {});
            const confirmedClaims = claims.filter(c => c.status === 'confirmed');
            const pendingClaims = claims.filter(c => c.status === 'pending');
            const confirmedBillTotal = confirmedClaims.reduce((sum, c) => sum + parseFloat(c.bill_amount || c.amount || 0), 0);
            const confirmedTipsTotal = confirmedClaims.reduce((sum, c) => sum + parseFloat(c.tip_amount || c.surplus || 0), 0);
            const grandTotal = {{ (float) $bill->grand_total }};
            const remaining = Math.max(0, grandTotal - confirmedBillTotal);
            const pct = grandTotal > 0 ? Math.min(100, Math.round((confirmedBillTotal / grandTotal) * 1000) / 10) : 0;
            // Check item-by-item confirmation
            const cards = document.querySelectorAll('#participantItemsContainer .item-selection-card');
            let allItemsConfirmed = cards.length > 0;
            const confirmedItemQtyMap = {};
            confirmedClaims.forEach(c => {
                (c.items || []).forEach(it => {
                    const iId = it.item_id || it.id;
                    confirmedItemQtyMap[iId] = (confirmedItemQtyMap[iId] || 0) + parseInt(it.qty || 0, 10);
                });
            });

            cards.forEach(card => {
                const itemId = card.getAttribute('data-item-id');
                const totalQty = parseInt(card.getAttribute('data-total') || '0', 10);
                const confirmedQty = confirmedItemQtyMap[itemId] || 0;
                if (confirmedQty < totalQty) {
                    allItemsConfirmed = false;
                }
            });

            const isFullySettled = confirmedBillTotal > 0
                && remaining <= 0.01
                && pendingClaims.length === 0
                && allItemsConfirmed;

            summary = {
                total_confirmed_paid: confirmedBillTotal,
                total_confirmed_tips: confirmedTipsTotal,
                progress_percentage: pct,
                remaining_confirmed_amount: remaining,
                is_fully_settled: isFullySettled,
                total_claims_count: claims.length,
            };
        }

        const confirmedTextEl = document.getElementById('billConfirmedProgressText');
        if (confirmedTextEl) {
            confirmedTextEl.textContent = `${formatRupiah(summary.total_confirmed_paid)} (${summary.progress_percentage}%)`;
        }

        const progressBarEl = document.getElementById('billProgressBar');
        if (progressBarEl) {
            progressBarEl.style.width = `${Math.min(100, Math.max(0, summary.progress_percentage))}%`;
        }

        const remainingTextEl = document.getElementById('billRemainingAmountText');
        if (remainingTextEl) {
            remainingTextEl.textContent = `Sisa: ${formatRupiah(summary.remaining_confirmed_amount)}`;
        }

        const totalTipsEl = document.getElementById('billTotalTipsText');
        if (totalTipsEl) {
            const tips = summary.total_confirmed_tips !== undefined ? summary.total_confirmed_tips : 0;
            if (tips > 0) {
                totalTipsEl.innerHTML = `<i class="fa-light fa-gift text-[9px]"></i> Tip: ${formatRupiah(tips)}`;
                totalTipsEl.classList.remove('hidden');
            } else {
                totalTipsEl.classList.add('hidden');
            }
        }

        const settledBadge = document.getElementById('billSettledBadge');
        if (settledBadge) {
            if (summary.is_fully_settled) {
                settledBadge.classList.remove('hidden');
                settledBadge.classList.add('inline-flex');
                settledBadge.style.setProperty('display', 'inline-flex', 'important');
            } else {
                settledBadge.classList.remove('inline-flex');
                settledBadge.classList.add('hidden');
                settledBadge.style.setProperty('display', 'none', 'important');
            }
        }

        const totalClaimsCountEl = document.getElementById('totalClaimsCount');
        if (totalClaimsCountEl && summary.total_claims_count !== undefined) {
            totalClaimsCountEl.textContent = summary.total_claims_count;
        }
    }

    function markClaimConfirmedLocally(claimId) {
        const card = document.querySelector(`.claim-history-card[data-claim-id="${claimId}"]`);
        if (card) {
            const badgeContainer = card.querySelector('.claim-status-badge');
            if (badgeContainer) {
                badgeContainer.className = 'claim-status-badge inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60';
                badgeContainer.innerHTML = '<i class="fa-light fa-circle-check text-emerald-600"></i><span>Lunas Terkonfirmasi</span>';
            }
            const confirmBtn = card.querySelector('.btn-confirm-claim');
            if (confirmBtn) confirmBtn.remove();
        }

        if (window.claimDetailsData && window.claimDetailsData[claimId]) {
            window.claimDetailsData[claimId].status = 'confirmed';
        }

        updatePendingCounters();
        updateBillSummary();
    }

    function updatePendingCounters() {
        const pendingCount = Object.values(window.claimDetailsData || {}).filter(c => c.status === 'pending').length;

        const bannerBtn = document.getElementById('btnHostBannerBatchConfirm');
        const bannerCount = document.getElementById('bannerPendingCount');
        if (bannerBtn && bannerCount) {
            bannerCount.innerText = pendingCount;
            if (pendingCount > 0) {
                bannerBtn.classList.remove('hidden');
                bannerBtn.classList.add('flex');
                bannerBtn.style.setProperty('display', 'flex', 'important');
            } else {
                bannerBtn.classList.remove('flex');
                bannerBtn.classList.add('hidden');
                bannerBtn.style.setProperty('display', 'none', 'important');
            }
        }

        const batchOpenBtn = document.getElementById('btnOpenBatchConfirmModal');
        const batchBtnCount = document.getElementById('batchBtnPendingCount');
        if (batchOpenBtn && batchBtnCount) {
            batchBtnCount.innerText = pendingCount;
            if (pendingCount > 0) {
                batchOpenBtn.classList.remove('hidden');
                batchOpenBtn.classList.add('inline-flex');
                batchOpenBtn.style.setProperty('display', 'inline-flex', 'important');
            } else {
                batchOpenBtn.classList.remove('inline-flex');
                batchOpenBtn.classList.add('hidden');
                batchOpenBtn.style.setProperty('display', 'none', 'important');
            }
        }
    }

    function handleConfirmClaimClick(e) {
        e.stopPropagation();
        const claimId = this.dataset.claimId;
        const payerName = this.dataset.name;
        const amountStr = this.dataset.amount;

        const executeConfirm = async () => {
            if (window.Notiflix) Notiflix.Loading.pulse('Mengonfirmasi pembayaran...');
            try {
                const response = await fetch(`/b/${slug}/claims/${claimId}/confirm`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                if (window.Notiflix) Notiflix.Loading.remove();

                if (response.ok && data.success) {
                    if (window.Notiflix) {
                        Notiflix.Notify.success(data.message);
                    }
                    markClaimConfirmedLocally(claimId);
                    updateBillSummary(data.bill_summary);
                } else {
                    if (window.Notiflix) {
                        Notiflix.Notify.failure(data.message || 'Gagal mengonfirmasi klaim.');
                    }
                }
            } catch (err) {
                if (window.Notiflix) {
                    Notiflix.Loading.remove();
                    Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
                }
            }
        };

        if (window.Notiflix) {
            Notiflix.Confirm.show(
                'Konfirmasi Dana Masuk',
                `Konfirmasi bahwa dana dari ${payerName} sejumlah ${amountStr} sudah masuk ke rekening/QRIS kamu?`,
                'Ya, Konfirmasi',
                'Batal',
                executeConfirm
            );
        } else if (confirm(`Konfirmasi dana masuk dari ${payerName} (${amountStr})?`)) {
            executeConfirm();
        }
    }

    document.querySelectorAll('.btn-confirm-claim').forEach(btn => {
        btn.addEventListener('click', handleConfirmClaimClick);
    });

    // ==========================================
    // BATCH CONFIRM MODAL LOGIC (Host)
    // ==========================================
    const batchConfirmModal = document.getElementById('batchConfirmModal');
    const btnOpenBatchConfirmModal = document.getElementById('btnOpenBatchConfirmModal');
    const btnHostBannerBatchConfirm = document.getElementById('btnHostBannerBatchConfirm');
    const btnCloseBatchConfirmModal = document.getElementById('btnCloseBatchConfirmModal');
    const btnCancelBatchConfirmModal = document.getElementById('btnCancelBatchConfirmModal');
    const btnSubmitBatchConfirm = document.getElementById('btnSubmitBatchConfirm');
    const batchSelectAll = document.getElementById('batchSelectAll');
    const batchClaimsList = document.getElementById('batchClaimsList');
    const batchClaimsEmptyState = document.getElementById('batchClaimsEmptyState');
    const batchSelectedRatioText = document.getElementById('batchSelectedRatioText');
    const batchSummaryCount = document.getElementById('batchSummaryCount');
    const batchSummaryAmount = document.getElementById('batchSummaryAmount');

    function openBatchConfirmModal() {
        if (!batchConfirmModal) return;
        renderBatchClaimsList();
        batchConfirmModal.classList.remove('hidden');
    }

    function closeBatchConfirmModal() {
        if (!batchConfirmModal) return;
        batchConfirmModal.classList.add('hidden');
    }

    if (btnOpenBatchConfirmModal) {
        btnOpenBatchConfirmModal.addEventListener('click', openBatchConfirmModal);
    }
    if (btnHostBannerBatchConfirm) {
        btnHostBannerBatchConfirm.addEventListener('click', openBatchConfirmModal);
    }
    if (btnCloseBatchConfirmModal) {
        btnCloseBatchConfirmModal.addEventListener('click', closeBatchConfirmModal);
    }
    if (btnCancelBatchConfirmModal) {
        btnCancelBatchConfirmModal.addEventListener('click', closeBatchConfirmModal);
    }

    if (batchConfirmModal) {
        batchConfirmModal.addEventListener('click', function (e) {
            if (e.target === batchConfirmModal) {
                closeBatchConfirmModal();
            }
        });
    }

    function renderBatchClaimsList() {
        if (!batchClaimsList) return;
        const pendingClaims = Object.values(window.claimDetailsData || {}).filter(c => c.status === 'pending');

        batchClaimsList.innerHTML = '';
        if (pendingClaims.length === 0) {
            if (batchClaimsEmptyState) batchClaimsEmptyState.classList.remove('hidden');
            if (batchSelectAll) {
                batchSelectAll.disabled = true;
                batchSelectAll.checked = false;
            }
            updateBatchSummary();
            return;
        }

        if (batchClaimsEmptyState) batchClaimsEmptyState.classList.add('hidden');
        if (batchSelectAll) {
            batchSelectAll.disabled = false;
            batchSelectAll.checked = true;
        }

        pendingClaims.forEach(claim => {
            const initial = claim.payer_name ? claim.payer_name.trim().charAt(0).toUpperCase() : '?';
            const pm = (claim.payment_method || 'qris').toLowerCase();
            let methodBadge = `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200">${claim.payment_method.toUpperCase()}</span>`;
            if (pm.includes('qris')) {
                methodBadge = `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200"><i class="fa-light fa-qrcode text-[10px]"></i> QRIS</span>`;
            } else if (pm.includes('cash') || pm.includes('tunai')) {
                methodBadge = `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/50"><i class="fa-light fa-money-bill-wave text-[10px]"></i> Tunai</span>`;
            }

            const row = document.createElement('label');
            row.className = 'batch-claim-row flex items-center justify-between p-3.5 hover:bg-zinc-50 transition-colors cursor-pointer select-none';
            row.innerHTML = `
                <div class="flex items-center gap-2.5 min-w-0 mr-3">
                    <input type="checkbox" class="batch-claim-item-checkbox rounded text-emerald-700 focus:ring-emerald-600 h-4 w-4 border-zinc-300 cursor-pointer flex-shrink-0" value="${claim.id}" data-amount="${claim.amount}" checked>
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-900 font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                        ${initial}
                    </div>
                    <div class="min-w-0">
                        <span class="font-bold text-zinc-900 text-xs sm:text-sm block truncate">${claim.payer_name}</span>
                        <div class="mt-0.5">${methodBadge}</div>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="font-extrabold text-emerald-900 tabular-nums text-xs sm:text-sm">${formatRupiah(claim.amount)}</span>
                </div>
            `;

            const chk = row.querySelector('.batch-claim-item-checkbox');
            chk.addEventListener('change', () => {
                updateBatchSummary();
            });

            batchClaimsList.appendChild(row);
        });

        updateBatchSummary();
    }

    if (batchSelectAll) {
        batchSelectAll.addEventListener('change', function () {
            const isChecked = this.checked;
            document.querySelectorAll('.batch-claim-item-checkbox').forEach(chk => {
                chk.checked = isChecked;
            });
            updateBatchSummary();
        });
    }

    function updateBatchSummary() {
        const allCheckboxes = document.querySelectorAll('.batch-claim-item-checkbox');
        const checkedBoxes = Array.from(allCheckboxes).filter(chk => chk.checked);

        const totalCount = allCheckboxes.length;
        const selectedCount = checkedBoxes.length;

        if (batchSelectedRatioText) {
            batchSelectedRatioText.innerText = `${selectedCount}/${totalCount}`;
        }
        if (batchSelectAll) {
            batchSelectAll.checked = (totalCount > 0 && selectedCount === totalCount);
            batchSelectAll.indeterminate = (selectedCount > 0 && selectedCount < totalCount);
        }

        let totalAmount = 0;
        checkedBoxes.forEach(chk => {
            totalAmount += parseFloat(chk.dataset.amount || 0);
        });

        if (batchSummaryCount) {
            batchSummaryCount.innerText = `${selectedCount} orang terpilih`;
        }
        if (batchSummaryAmount) {
            batchSummaryAmount.innerText = formatRupiah(totalAmount);
        }

        if (btnSubmitBatchConfirm) {
            if (selectedCount === 0) {
                btnSubmitBatchConfirm.disabled = true;
                btnSubmitBatchConfirm.classList.add('opacity-50', 'pointer-events-none');
            } else {
                btnSubmitBatchConfirm.disabled = false;
                btnSubmitBatchConfirm.classList.remove('opacity-50', 'pointer-events-none');
            }
        }
    }

    let isBatchSubmitting = false;
    if (btnSubmitBatchConfirm) {
        btnSubmitBatchConfirm.addEventListener('click', async function () {
            if (isBatchSubmitting) return;

            const selectedCheckboxes = Array.from(document.querySelectorAll('.batch-claim-item-checkbox:checked'));
            const selectedIds = selectedCheckboxes.map(chk => parseInt(chk.value, 10));

            if (selectedIds.length === 0) {
                if (window.Notiflix) Notiflix.Notify.warning('Pilih minimal satu teman untuk dikonfirmasi.');
                return;
            }

            let totalSelectedAmount = 0;
            selectedCheckboxes.forEach(chk => {
                totalSelectedAmount += parseFloat(chk.dataset.amount || 0);
            });

            const submitBatchAction = async () => {
                isBatchSubmitting = true;
                btnSubmitBatchConfirm.disabled = true;
                btnSubmitBatchConfirm.classList.add('opacity-50', 'pointer-events-none');
                if (window.Notiflix) Notiflix.Loading.pulse('Mengonfirmasi pembayaran...');

                try {
                    const response = await fetch(`/b/${slug}/claims/batch-confirm`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ claim_ids: selectedIds }),
                    });

                    const data = await response.json();
                    if (window.Notiflix) Notiflix.Loading.remove();

                    if (response.ok && data.success) {
                        if (window.Notiflix) Notiflix.Notify.success(data.message);

                        (data.confirmed_ids || selectedIds).forEach(id => {
                            markClaimConfirmedLocally(id);
                        });

                        updateBillSummary(data.bill_summary);

                        closeBatchConfirmModal();
                    } else {
                        if (window.Notiflix) Notiflix.Notify.failure(data.message || 'Gagal memproses konfirmasi.');
                    }
                } catch (err) {
                    if (window.Notiflix) {
                        Notiflix.Loading.remove();
                        Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
                    }
                } finally {
                    isBatchSubmitting = false;
                    btnSubmitBatchConfirm.disabled = false;
                    btnSubmitBatchConfirm.classList.remove('opacity-50', 'pointer-events-none');
                }
            };

            const confirmMsg = `Konfirmasi sekaligus pembayaran dari ${selectedIds.length} teman sejumlah ${formatRupiah(totalSelectedAmount)}?`;
            if (window.Notiflix) {
                Notiflix.Confirm.show(
                    'Konfirmasi Sekaligus',
                    confirmMsg,
                    'Ya, Konfirmasi Semua',
                    'Batal',
                    submitBatchAction
                );
            } else if (confirm(confirmMsg)) {
                submitBatchAction();
            }
        });
    }

    function handleRejectClaimClick(e) {
        e.stopPropagation();
        const claimId = this.dataset.claimId;
        const payerName = this.dataset.name;

        const executeReject = async () => {
            if (window.Notiflix) Notiflix.Loading.pulse('Membatalkan klaim...');
            try {
                const response = await fetch(`/b/${slug}/claims/${claimId}/reject`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                if (window.Notiflix) Notiflix.Loading.remove();

                if (response.ok && data.success) {
                    if (window.Notiflix) {
                        Notiflix.Notify.success(data.message);
                    }
                    const card = document.querySelector(`.claim-history-card[data-claim-id="${claimId}"]`);
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.remove();
                            const container = document.getElementById('claimsListContainer');
                            const emptyState = document.getElementById('emptyClaimsHistoryState');
                            if (container && container.children.length === 0) {
                                container.classList.add('hidden');
                                if (emptyState) emptyState.classList.remove('hidden');
                            }
                        }, 300);
                    }

                    if (window.claimDetailsData && window.claimDetailsData[claimId]) {
                        delete window.claimDetailsData[claimId];
                    }

                    if (data.items_remaining) {
                        updateMenuRemainingQuantities(data.items_remaining);
                    }

                    updatePendingCounters();
                    const totalClaimsCountEl = document.getElementById('totalClaimsCount');
                    if (totalClaimsCountEl) {
                        totalClaimsCountEl.innerText = Object.keys(window.claimDetailsData || {}).length;
                    }
                    updateBillSummary(data.bill_summary);
                } else {
                    if (window.Notiflix) {
                        Notiflix.Notify.failure(data.message || 'Gagal membatalkan klaim.');
                    }
                }
            } catch (err) {
                if (window.Notiflix) {
                    Notiflix.Loading.remove();
                    Notiflix.Notify.failure('Terjadi kesalahan jaringan.');
                }
            }
        };

        if (window.Notiflix) {
            Notiflix.Confirm.show(
                'Tolak / Hapus Klaim',
                `Apakah Anda yakin ingin membatalkan klaim dari ${payerName}? Item akan kembali tersedia.`,
                'Ya, Tolak',
                'Batal',
                executeReject
            );
        } else if (confirm(`Apakah Anda yakin ingin membatalkan klaim dari ${payerName}?`)) {
            executeReject();
        }
    }

    document.querySelectorAll('.btn-reject-claim').forEach(btn => {
        btn.addEventListener('click', handleRejectClaimClick);
    });

    // ==========================================
    // CLAIM DETAIL MODAL POPUP HANDLERS
    // ==========================================
    document.querySelectorAll('.claim-history-card').forEach(card => {
        card.addEventListener('click', function () {
            const claimId = this.dataset.claimId;
            openClaimDetail(claimId);
        });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const claimId = this.dataset.claimId;
                openClaimDetail(claimId);
            }
        });
    });

    function openClaimDetail(claimId) {
        if (!window.claimDetailsData || !window.claimDetailsData[claimId]) return;
        const data = window.claimDetailsData[claimId];
        activeClaimData = data;

        // Payer Initial & Name
        const initial = (data.payer_name || 'U').trim().charAt(0).toUpperCase();
        document.getElementById('detailPayerInitial').innerText = initial;
        document.getElementById('detailPayerName').innerText = data.payer_name;
        document.getElementById('detailTimestamp').innerText = `${data.created_at_formatted} (${data.created_at_relative})`;

        // Status Badge
        const statusContainer = document.getElementById('detailStatusBadge');
        if (data.status === 'confirmed') {
            statusContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60"><i class="fa-light fa-circle-check text-emerald-600"></i><span>Lunas</span></span>';
        } else {
            statusContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/70"><i class="fa-light fa-hourglass-clock text-amber-600"></i><span>Menunggu Konfirmasi</span></span>';
        }

        // Method Badge
        const methodContainer = document.getElementById('detailMethodBadge');
        const pm = (data.payment_method || 'qris').toLowerCase();
        if (pm.includes('qris')) {
            methodContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200"><i class="fa-light fa-qrcode text-[10px]"></i> QRIS Dinamis</span>';
        } else if (pm.includes('cash') || pm.includes('tunai')) {
            methodContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/50"><i class="fa-light fa-money-bill-wave text-[10px]"></i> Tunai</span>';
        } else {
            methodContainer.innerHTML = `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200"><i class="fa-light fa-building-columns text-[10px]"></i> Transfer ${data.payment_method}</span>`;
        }

        // Amount & Surplus
        document.getElementById('detailAmountPaid').innerText = formatRupiah(data.amount);
        const surplusBadge = document.getElementById('detailSurplusBadge');
        if (data.surplus > 0) {
            surplusBadge.classList.remove('hidden');
            document.getElementById('detailSurplusText').innerText = `+${formatRupiah(data.surplus)} Tip / Extra`;
        } else {
            surplusBadge.classList.add('hidden');
        }

        // Proportion
        document.getElementById('detailProportionBadge').innerText = `${data.proportion_percent}%`;
        const barWidth = Math.min(100, Math.max(2, data.proportion_percent));
        document.getElementById('detailProportionBar').style.width = `${barWidth}%`;
        document.getElementById('detailProportionDesc').innerHTML = `Memegang <strong>${data.proportion_percent}%</strong> dari total tagihan pesanan menu (${formatRupiah(data.items_subtotal)} dari total ${formatRupiah(data.bill_subtotal)}).`;

        // Claimed Items List
        const itemsListEl = document.getElementById('detailItemsList');
        itemsListEl.innerHTML = '';
        document.getElementById('detailItemCount').innerText = data.items_count;

        data.items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'px-3.5 py-2 flex items-center justify-between text-xs text-zinc-800';
            row.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="font-bold text-emerald-800">${item.qty}x</span>
                    <span class="font-medium text-zinc-900">${item.name}</span>
                </div>
                <span class="tabular-nums font-semibold text-zinc-700">${formatRupiah(item.subtotal)}</span>
            `;
            itemsListEl.appendChild(row);
        });

        // Cost Breakdown Rows
        document.getElementById('detailItemsSubtotal').innerText = formatRupiah(data.items_subtotal);

        const rowDeliv = document.getElementById('detailRowDelivery');
        if (data.share_delivery > 0) {
            rowDeliv.classList.remove('hidden');
            document.getElementById('detailShareDelivery').innerText = `+${formatRupiah(data.share_delivery)}`;
        } else {
            rowDeliv.classList.add('hidden');
        }

        const rowServ = document.getElementById('detailRowService');
        if (data.share_service > 0) {
            rowServ.classList.remove('hidden');
            document.getElementById('detailShareService').innerText = `+${formatRupiah(data.share_service)}`;
        } else {
            rowServ.classList.add('hidden');
        }

        const rowDisc = document.getElementById('detailRowDiscount');
        if (data.share_discount > 0) {
            rowDisc.classList.remove('hidden');
            document.getElementById('detailShareDiscount').innerText = `-${formatRupiah(data.share_discount)}`;
        } else {
            rowDisc.classList.add('hidden');
        }

        const rowSurplus = document.getElementById('detailRowSurplus');
        if (data.surplus > 0) {
            rowSurplus.classList.remove('hidden');
            document.getElementById('detailShareSurplus').innerText = `+${formatRupiah(data.surplus)}`;
        } else {
            rowSurplus.classList.add('hidden');
        }

        document.getElementById('detailFinalAmount').innerText = formatRupiah(data.amount);

        claimDetailModal.classList.remove('hidden');
    }

    if (btnCloseClaimDetailModal) {
        btnCloseClaimDetailModal.addEventListener('click', () => claimDetailModal.classList.add('hidden'));
    }
    if (btnCloseClaimDetailModalBottom) {
        btnCloseClaimDetailModalBottom.addEventListener('click', () => claimDetailModal.classList.add('hidden'));
    }

    // Copy Claim Summary to WhatsApp / Clipboard
    if (btnCopyClaimSummary) {
        btnCopyClaimSummary.addEventListener('click', function () {
            if (!activeClaimData) return;
            const c = activeClaimData;
            let text = `*Rincian Pembayaran PayMe*\n`;
            text += `Tagihan: {{ $bill->title }}\n`;
            text += `Nama: ${c.payer_name}\n`;
            text += `Status: ${c.status === 'confirmed' ? 'Lunas Terkonfirmasi ✅' : 'Menunggu Konfirmasi ⏳'}\n`;
            text += `Metode: ${c.payment_method.toUpperCase()}\n`;
            text += `Waktu: ${c.created_at_formatted}\n\n`;
            text += `*Item Dipilih:*\n`;
            c.items.forEach(i => {
                text += `- ${i.qty}x ${i.name} (${formatRupiah(i.subtotal)})\n`;
            });
            text += `\n*Rincian Biaya:*\n`;
            text += `Subtotal Item: ${formatRupiah(c.items_subtotal)}\n`;
            if (c.share_delivery > 0) text += `Proporsi Ongkir: +${formatRupiah(c.share_delivery)}\n`;
            if (c.share_service > 0) text += `Proporsi Layanan: +${formatRupiah(c.share_service)}\n`;
            if (c.share_discount > 0) text += `Proporsi Diskon: -${formatRupiah(c.share_discount)}\n`;
            if (c.surplus > 0) text += `Tip / Pembulatan: +${formatRupiah(c.surplus)}\n`;
            text += `*TOTAL DIBAYAR: ${formatRupiah(c.amount)}*\n`;

            navigator.clipboard.writeText(text).then(() => {
                const btnText = document.getElementById('btnCopyClaimText');
                const btnIcon = document.getElementById('btnCopyClaimIcon');
                btnText.innerText = 'Tersalin!';
                btnIcon.className = 'fa-light fa-check text-xs text-emerald-600';
                if (window.Notiflix) Notiflix.Notify.success('Rincian berhasil disalin ke clipboard!');
                setTimeout(() => {
                    btnText.innerText = 'Salin Rincian';
                    btnIcon.className = 'fa-light fa-copy text-xs text-emerald-800';
                }, 2000);
            });
        });
    }

    // Copy link handler
    const btnCopyLink = document.getElementById('btnCopyLink');
    const copyText = document.getElementById('copyText');
    const copyIcon = document.getElementById('copyIcon');

    if (btnCopyLink) {
        btnCopyLink.addEventListener('click', function () {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function () {
                copyText.textContent = 'Tautan Berhasil Disalin!';
                copyIcon.className = 'fa-light fa-check text-xs text-emerald-600';
                if (window.Notiflix) Notiflix.Notify.success('Tautan tagihan berhasil disalin!');
                setTimeout(function () {
                    copyText.textContent = 'Salin Tautan';
                    copyIcon.className = 'fa-light fa-copy text-xs text-emerald-800';
                }, 2500);
            });
        });
    }

    // Copy account numbers
    const copyAccBtns = document.querySelectorAll('.btn-copy-acc');
    copyAccBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const acc = this.getAttribute('data-acc');
            navigator.clipboard.writeText(acc).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-light fa-check text-emerald-600"></i>';
                if (window.Notiflix) Notiflix.Notify.success(`Nomor rekening ${acc} disalin!`);
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 2000);
            });
        });
    });
});
</script>
@endpush
