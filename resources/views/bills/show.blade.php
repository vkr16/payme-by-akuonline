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
                    <i class="fa-light fa-crown text-amber-300 text-sm"></i>
                </div>
                <div>
                    <span class="font-bold block">Mode Host (Penagih)</span>
                    <span class="text-[11px] text-emerald-200">Kamu adalah pembuat tagihan ini. Pantau & konfirmasi klaim pembayaran kawan di bawah.</span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" id="btnHostBannerBatchConfirm" class="{{ $pendingCount > 0 ? '' : 'hidden ' }}px-2.5 py-1.5 rounded-lg bg-amber-400 hover:bg-amber-300 text-amber-950 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Konfirmasi sekaligus klaim yang menunggu">
                    <i class="fa-light fa-check-double text-xs"></i>
                    <span><span id="bannerPendingCount">{{ $pendingCount }}</span> Menunggu</span>
                </button>
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-600 text-white font-semibold text-xs transition-colors">
                    Dashboard
                </a>
            </div>
        </div>
    @endif

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

            <span id="billSettledBadge" class="{{ $bill->isFullySettled() ? '' : 'hidden ' }}inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-emerald-500 text-white shadow-2xs">
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
                <div class="flex justify-between text-[10px] text-zinc-400">
                    <span id="billRemainingAmountText">Sisa: Rp {{ number_format($bill->remaining_confirmed_amount, 0, ',', '.') }}</span>
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
                $waText = urlencode("Halo kawan-kawan! Ini link patungan \"{$bill->title}\" (Total: Rp " . number_format($bill->grand_total, 0, ',', '.') . ").\nSilakan pilih menu pesananmu dan bayar via QRIS/Transfer di link ini ya:\n{$shareUrl}");
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
    <div class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm space-y-5">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-zinc-900 flex items-center gap-2">
                <i class="fa-light fa-utensils text-emerald-700"></i>
                <span>Pilih Menu Pesanan Kamu</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">
                Tentukan porsi atau makanan yang kamu pesan. Biaya tambahan & diskon dihitung secara proporsional.
            </p>
        </div>

        <!-- Menu Items List with Stepper & Wrap Resilience -->
        <div class="space-y-2.5" id="participantItemsContainer">
            @foreach($bill->items as $item)
                @php
                    $isSoldOut = $item->remaining_qty <= 0;
                @endphp
                <div class="item-selection-card p-3 sm:p-3.5 rounded-xl border transition-all flex items-center justify-between gap-3 {{ $isSoldOut ? 'bg-zinc-50/60 border-zinc-200/60 opacity-60' : 'bg-white border-zinc-200/80 hover:border-emerald-600/70 shadow-2xs' }} cursor-pointer"
                     data-item-id="{{ $item->id }}"
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
                                    sisa {{ $item->remaining_qty }}/{{ $item->qty }} porsi
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

        <!-- Proportional Calculation Summary for Current Participant -->
        <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200/80 space-y-2 text-xs">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Kalkulasi Bagianmu</span>

            <div class="flex justify-between text-zinc-600">
                <span>Subtotal Menu Terpilih:</span>
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

        <!-- Payment Actions: Dynamic QRIS & Claim Button -->
        <div class="pt-2 space-y-3">
            @if($bill->qris_payload)
                <button type="button" id="btnShowDynamicQris" class="touch-target w-full py-3 px-4 rounded-xl btn-primary font-bold text-xs sm:text-sm shadow-md inline-flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <i class="fa-light fa-qrcode text-base"></i>
                    <span>Bayar Sekarang Pakai QRIS Dinamis</span>
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
                    <div class="p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 flex items-center justify-between gap-3">
                        <div class="text-xs leading-tight">
                            <div class="font-bold text-zinc-900">{{ $bank->bank_name }}</div>
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
         RIWAYAT PEMBAYARAN KAWAN (Unified & Clickable to Modal)
         ========================================== -->
    <div id="riwayat-pembayaran" class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm space-y-4">
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-zinc-900 flex items-center gap-2">
                    <i class="fa-light fa-users text-emerald-700"></i>
                    <span>Riwayat Pembayaran (<span id="totalClaimsCount">{{ $bill->claims->count() }}</span>)</span>
                </h2>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Daftar kawan yang sudah konfirmasi bayar &bull; Klik kartu untuk melihat rincian & proporsi pesanan
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($isHost)
                    <button type="button" id="btnOpenBatchConfirmModal" class="{{ $pendingCount > 0 ? '' : 'hidden ' }}touch-target px-3 py-1.5 rounded-xl bg-emerald-800 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all cursor-pointer" title="Buka konfirmasi massal">
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
                            </div>
                            <i class="fa-light fa-chevron-right text-zinc-400 text-xs"></i>
                        </div>
                    </div>

                    <!-- Bottom Row: Clean Summary (Raw items hidden, click to open modal) -->
                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-zinc-100 text-[11px] text-zinc-500">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-light fa-receipt text-zinc-400 text-[10px]"></i>
                            <span>{{ $claim->claimItems->count() }} menu ({{ $claim->claimItems->sum('qty') }} porsi)</span>
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
     MODAL: DYNAMIC QRIS DIALOG
     ========================================== -->
<div id="qrisModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 shadow-xl border border-zinc-200 animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center pb-2 border-b border-zinc-100">
            <span class="text-xs font-bold text-zinc-800">QRIS Dinamis Otomatis</span>
            <button type="button" id="btnCloseQrisModal" class="text-zinc-400 hover:text-zinc-700 text-sm">
                <i class="fa-light fa-xmark"></i>
            </button>
        </div>

        <div class="p-3 rounded-2xl bg-zinc-50 border border-zinc-200 inline-block mx-auto">
            <div id="dynamicQrCanvasContainer" class="w-56 h-56 mx-auto flex items-center justify-center"></div>
        </div>

        <div>
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Nominal Terkunci Otomatis</span>
            <div class="text-2xl font-black text-emerald-900 tabular-nums" id="modalQrisNominal">Rp 0</div>
            <p class="text-[11px] text-zinc-500 mt-1">
                Scan menggunakan BCA, Mandiri, GoPay, Dana, OVO, atau aplikasi mobile banking apa saja.
            </p>
        </div>

        <div class="pt-2 space-y-2">
            <button type="button" id="btnModalConfirmPaid" class="touch-target w-full py-2.5 px-4 rounded-xl btn-primary font-bold text-xs sm:text-sm shadow-xs inline-flex items-center justify-center gap-2">
                <i class="fa-light fa-check text-xs"></i>
                <span>Saya Sudah Selesai Scan & Bayar</span>
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
                    <option value="qris" selected>QRIS Dinamis</option>
                    @foreach($bill->banks as $bank)
                        <option value="{{ $bank->bank_name }}">Transfer {{ $bank->bank_name }}</option>
                    @endforeach
                    <option value="cash">Tunai / Cash</option>
                </select>
            </div>

            <!-- Amount Breakdown Preview -->
            <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200 text-xs space-y-1">
                <div class="flex justify-between text-zinc-500">
                    <span>Nominal Tagihan:</span>
                    <span id="claimAmountPreview" class="font-bold text-emerald-900 tabular-nums">Rp 0</span>
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
     MODAL: RINCIAN PEMBAYARAN KAWAN (Claim Detail Popup)
     ========================================== -->
<div id="claimDetailModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-5 sm:p-7 max-w-md w-full text-left space-y-4 shadow-xl border border-zinc-200 animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto no-scrollbar">
        <!-- Header -->
        <div class="flex justify-between items-center pb-3 border-b border-zinc-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm font-bold">
                    <i class="fa-light fa-receipt text-emerald-800"></i>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-zinc-900">Rincian Pembayaran Kawan</h3>
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
                <span><i class="fa-light fa-utensils text-emerald-700 mr-1"></i> Menu Dipesan (<span id="detailItemCount">0</span>)</span>
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
                <span>Subtotal Menu Pesanan:</span>
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
                    0/0 Porsi
                </span>
            </div>
        </div>

        <!-- Contributors List Container -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Kawan yang Membayar:</span>
                <span class="text-[11px] text-zinc-500 font-medium" id="itemModalTotalClaimedSummary">Total: 0 porsi</span>
            </div>
            
            <div id="itemContributorsList" class="space-y-2 max-h-60 overflow-y-auto no-scrollbar">
                <!-- Dynamically populated via JS -->
            </div>

            <!-- Empty State -->
            <div id="itemContributorsEmptyState" class="hidden p-6 rounded-2xl bg-zinc-50 border border-dashed border-zinc-200 text-center text-xs text-zinc-500 space-y-1">
                <i class="fa-light fa-clock text-zinc-300 text-2xl block mb-1"></i>
                <p class="font-medium text-zinc-700">Belum ada yang mengklaim menu ini</p>
                <p class="text-zinc-400">Pilih porsi kamu menggunakan tombol + di daftar menu di atas.</p>
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
            Centang kawan yang dananya sudah masuk ke mutasi rekeningmu. Kawan yang belum transfer dapat kamu hilangkan centangnya.
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

    const btnShowDynamicQris = document.getElementById('btnShowDynamicQris');
    const btnOpenClaimModal = document.getElementById('btnOpenClaimModal');

    // QRIS Modal
    const qrisModal = document.getElementById('qrisModal');
    const btnCloseQrisModal = document.getElementById('btnCloseQrisModal');
    const dynamicQrCanvasContainer = document.getElementById('dynamicQrCanvasContainer');
    const modalQrisNominal = document.getElementById('modalQrisNominal');
    const btnModalConfirmPaid = document.getElementById('btnModalConfirmPaid');

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

    // Claim Detail Modal
    const claimDetailModal = document.getElementById('claimDetailModal');
    const btnCloseClaimDetailModal = document.getElementById('btnCloseClaimDetailModal');
    const btnCloseClaimDetailModalBottom = document.getElementById('btnCloseClaimDetailModalBottom');
    const btnCopyClaimSummary = document.getElementById('btnCopyClaimSummary');

    let currentCalculatedTotal = 0;
    let currentDynamicPayload = '';
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

    resetSelection();
    window.addEventListener('pageshow', resetSelection);

    // Show Dynamic QRIS Modal
    if (btnShowDynamicQris) {
        btnShowDynamicQris.addEventListener('click', function () {
            const selected = getSelectedItems();
            if (Object.keys(selected).length === 0) {
                if (window.Notiflix) {
                    Notiflix.Notify.warning('Silakan pilih minimal satu menu pesanan kamu terlebih dahulu!');
                } else {
                    alert('Silakan pilih minimal satu menu pesanan kamu terlebih dahulu!');
                }
                return;
            }

            modalQrisNominal.textContent = formatRupiah(currentCalculatedTotal);
            dynamicQrCanvasContainer.innerHTML = '';

            if (currentDynamicPayload) {
                new QRCode(dynamicQrCanvasContainer, {
                    text: currentDynamicPayload,
                    width: 210,
                    height: 210,
                    colorDark: "#064E3B",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            } else {
                dynamicQrCanvasContainer.innerHTML = '<p class="text-xs text-zinc-500">QRIS tidak tersedia.</p>';
            }

            qrisModal.classList.remove('hidden');
        });
    }

    if (btnCloseQrisModal) {
        btnCloseQrisModal.addEventListener('click', () => qrisModal.classList.add('hidden'));
    }

    // Switch from QRIS modal to Claim modal
    if (btnModalConfirmPaid) {
        btnModalConfirmPaid.addEventListener('click', function () {
            qrisModal.classList.add('hidden');
            openClaimModal();
        });
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

        claimAmountPreview.textContent = formatRupiah(currentCalculatedTotal);
        claimModalAlert.className = 'hidden';
        claimModal.classList.remove('hidden');
        claimPayerName.focus();
    }

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

        if (titleEl) titleEl.textContent = 'Rincian Pembayar Menu';
        if (itemNameEl) itemNameEl.textContent = itemName;
        if (itemPriceEl) itemPriceEl.textContent = '@ ' + formatRupiah(price);
        if (stockBadgeEl) stockBadgeEl.textContent = `${claimed}/${total} Porsi Terklaim (Sisa ${remaining})`;
        if (summaryEl) summaryEl.textContent = `Total: ${claimed} porsi`;

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
                        <div class="font-bold text-zinc-900 tabular-nums">${c.qty} porsi <span class="text-zinc-400 font-normal">(${formatRupiah(c.subtotal)})</span></div>
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
                    badgeEl.innerHTML = `sisa ${remaining}/${total} porsi`;
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
                    </div>
                    <i class="fa-light fa-chevron-right text-zinc-400 text-xs"></i>
                </div>
            </div>
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-zinc-100 text-[11px] text-zinc-500">
                <div class="flex items-center gap-1.5">
                    <i class="fa-light fa-receipt text-zinc-400 text-[10px]"></i>
                    <span>${claim.items_count || 1} menu (${claim.items_total_qty || 1} porsi)</span>
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
            const confirmedTotal = claims
                .filter(c => c.status === 'confirmed')
                .reduce((sum, c) => sum + parseFloat(c.amount || 0), 0);
            const grandTotal = {{ (float) $bill->grand_total }};
            const remaining = Math.max(0, grandTotal - confirmedTotal);
            const pct = grandTotal > 0 ? Math.min(100, Math.round((confirmedTotal / grandTotal) * 1000) / 10) : 100;
            summary = {
                total_confirmed_paid: confirmedTotal,
                progress_percentage: pct,
                remaining_confirmed_amount: remaining,
                is_fully_settled: remaining <= 0 && {{ $bill->items->count() > 0 ? 'true' : 'false' }},
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

        const settledBadge = document.getElementById('billSettledBadge');
        if (settledBadge) {
            if (summary.is_fully_settled) {
                settledBadge.classList.remove('hidden');
            } else {
                settledBadge.classList.add('hidden');
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
            } else {
                bannerBtn.classList.add('hidden');
            }
        }

        const batchOpenBtn = document.getElementById('btnOpenBatchConfirmModal');
        const batchBtnCount = document.getElementById('batchBtnPendingCount');
        if (batchOpenBtn && batchBtnCount) {
            batchBtnCount.innerText = pendingCount;
            if (pendingCount > 0) {
                batchOpenBtn.classList.remove('hidden');
            } else {
                batchOpenBtn.classList.add('hidden');
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
                if (window.Notiflix) Notiflix.Notify.warning('Pilih minimal satu kawan untuk dikonfirmasi.');
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

            const confirmMsg = `Konfirmasi sekaligus pembayaran dari ${selectedIds.length} kawan sejumlah ${formatRupiah(totalSelectedAmount)}?`;
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
                `Apakah Anda yakin ingin membatalkan klaim dari ${payerName}? Porsi menu akan kembali tersedia.`,
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
            document.getElementById('detailSurplusText').innerText = `+${formatRupiah(data.surplus)} Tip / Pembulatan`;
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
            text += `*Menu Dipesan:*\n`;
            c.items.forEach(i => {
                text += `- ${i.qty}x ${i.name} (${formatRupiah(i.subtotal)})\n`;
            });
            text += `\n*Rincian Biaya:*\n`;
            text += `Subtotal Menu: ${formatRupiah(c.items_subtotal)}\n`;
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
