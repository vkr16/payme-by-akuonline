@extends('layouts.app')

@section('title', 'Tagihan ' . $bill->title . ' - PayMe')
@section('meta_description', 'Tagihan patungan ' . $bill->title . ' ditalangi oleh ' . $bill->user->name . '. Total: Rp ' . number_format($bill->grand_total, 0, ',', '.') . ' (' . $bill->items->count() . ' item).')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-20">

    <!-- Flash notification -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-2xs">
            <i class="fa-light fa-circle-check text-emerald-600 text-base mt-0.5 flex-shrink-0"></i>
            <div class="text-xs sm:text-sm font-medium">
                {{ session('success') }}
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

        <!-- Target Grand Total Highlight -->
        <div class="p-4 rounded-2xl bg-zinc-50 border border-zinc-200/80 max-w-sm mx-auto">
            <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block">Total Tagihan Patungan</span>
            <div class="text-2xl sm:text-3xl font-black text-emerald-900 tabular-nums mt-0.5">
                Rp {{ number_format($bill->grand_total, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-zinc-500 mt-0.5 block">
                {{ $bill->items->sum('qty') }} item pesanan
            </span>
        </div>

        <!-- Share Actions -->
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-2.5">
            <button type="button" id="btnCopyLink" class="touch-target w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white hover:bg-zinc-50 text-zinc-800 border border-zinc-300 font-semibold text-xs shadow-2xs inline-flex items-center justify-center gap-2 transition-all cursor-pointer">
                <i class="fa-light fa-copy text-xs text-emerald-800" id="copyIcon"></i>
                <span id="copyText">Salin Tautan Patungan</span>
            </button>

            @php
                $shareUrl = url('/b/' . $bill->slug);
                $waText = urlencode("Halo kawan-kawan! Ini link rincian patungan \"{$bill->title}\" (Total: Rp " . number_format($bill->grand_total, 0, ',', '.') . ").\nSilakan pilih pesananmu dan transfer langsung via QRIS/Bank di link ini ya:\n{$shareUrl}");
            @endphp
            <a href="https://api.whatsapp.com/send?text={{ $waText }}" target="_blank" rel="noopener noreferrer" class="touch-target w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-semibold text-xs shadow-2xs inline-flex items-center justify-center gap-2 transition-all">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Bagikan ke WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- Payment Methods: QRIS & Bank Options -->
    <div class="card-solid rounded-2xl p-6 bg-white border border-zinc-200/90 shadow-sm space-y-4">
        <h2 class="text-sm sm:text-base font-bold text-zinc-900 flex items-center gap-2">
            <i class="fa-light fa-wallet text-emerald-700"></i>
            <span>Metode Penerimaan Pembayaran</span>
        </h2>

        @if($bill->qris_payload)
            <!-- QRIS Static / Dynamic Container -->
            <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200/80 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                        <i class="fa-light fa-qrcode text-emerald-700"></i>
                        <span>QRIS Merchant Terkoneksi</span>
                    </span>
                    <span class="text-[10px] font-semibold text-emerald-800 bg-emerald-100/70 px-2 py-0.5 rounded-md">EMVCo Valid</span>
                </div>
                <p class="text-xs text-zinc-600 leading-relaxed">
                    Merchant: <strong>{{ $bill->qris_merchant_name ?: 'Merchant QRIS' }}</strong> ({{ $bill->qris_merchant_city ?: 'Indonesia' }}). Teman patungan akan mendapatkan QRIS dinamis otomatis dengan nominal terkunci saat memilih pesanannya.
                </p>
            </div>
        @endif

        @if($bill->banks->count() > 0)
            <!-- Bank Accounts Cards -->
            <div class="space-y-2">
                <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider block">Transfer Bank & E-Wallet</span>
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

        @if(!$bill->qris_payload && $bill->banks->count() === 0)
            <div class="p-4 rounded-xl bg-zinc-50 text-center text-xs text-zinc-500">
                Tidak ada metode QRIS / Rekening spesifik yang dilampirkan. Pembayaran dapat dilakukan tunai (Cash).
            </div>
        @endif
    </div>

    <!-- Order Items List Card -->
    <div class="card-solid rounded-2xl p-6 bg-white border border-zinc-200/90 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm sm:text-base font-bold text-zinc-900 flex items-center gap-2">
                <i class="fa-light fa-receipt text-emerald-700"></i>
                <span>Rincian Menu Struk</span>
            </h2>
            <span class="text-xs font-bold text-zinc-500">{{ $bill->items->count() }} menu</span>
        </div>

        <div class="divide-y divide-zinc-100 text-xs">
            @foreach($bill->items as $item)
                <div class="py-2.5 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-zinc-100 text-zinc-700 font-bold text-[11px] flex items-center justify-center tabular-nums">
                            {{ $item->qty }}x
                        </span>
                        <span class="font-semibold text-zinc-800">{{ $item->name }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-zinc-900 tabular-nums">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        @if($item->qty > 1)
                            <span class="text-[10px] text-zinc-400 block tabular-nums">@ Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Breakdown Calculations -->
        <div class="pt-3 border-t border-zinc-100 space-y-1.5 text-xs">
            <div class="flex justify-between text-zinc-600">
                <span>Subtotal Menu:</span>
                <span class="font-bold text-zinc-900 tabular-nums">Rp {{ number_format($bill->items_subtotal, 0, ',', '.') }}</span>
            </div>

            @if($bill->delivery_fee > 0)
                <div class="flex justify-between text-zinc-600">
                    <span>Ongkos Kirim:</span>
                    <span class="font-medium text-zinc-900 tabular-nums">Rp {{ number_format($bill->delivery_fee, 0, ',', '.') }}</span>
                </div>
            @endif

            @if($bill->service_fee > 0)
                <div class="flex justify-between text-zinc-600">
                    <span>Biaya Layanan / Kemasan:</span>
                    <span class="font-medium text-zinc-900 tabular-nums">Rp {{ number_format($bill->service_fee, 0, ',', '.') }}</span>
                </div>
            @endif

            @if($bill->discount > 0)
                <div class="flex justify-between text-emerald-700">
                    <span>Potongan Diskon / Promo:</span>
                    <span class="font-bold tabular-nums">-Rp {{ number_format($bill->discount, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="pt-2 border-t border-zinc-200 flex justify-between items-center text-sm font-bold text-zinc-900">
                <span>Total Struk:</span>
                <span class="text-emerald-800 font-black text-base tabular-nums">Rp {{ number_format($bill->grand_total, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Back to Dashboard -->
    <div class="text-center pt-2">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-emerald-800 transition-colors">
            <i class="fa-light fa-arrow-left text-xs"></i>
            <span>Kembali ke Dashboard Utama</span>
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnCopyLink = document.getElementById('btnCopyLink');
    const copyText = document.getElementById('copyText');
    const copyIcon = document.getElementById('copyIcon');

    if (btnCopyLink) {
        btnCopyLink.addEventListener('click', function () {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function () {
                copyText.textContent = 'Tautan Berhasil Disalin!';
                copyIcon.className = 'fa-light fa-check text-xs text-emerald-600';
                setTimeout(function () {
                    copyText.textContent = 'Salin Tautan Patungan';
                    copyIcon.className = 'fa-light fa-copy text-xs text-emerald-800';
                }, 2500);
            });
        });
    }

    const copyAccBtns = document.querySelectorAll('.btn-copy-acc');
    copyAccBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const acc = this.getAttribute('data-acc');
            navigator.clipboard.writeText(acc).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-light fa-check text-emerald-600"></i>';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 2000);
            });
        });
    });
});
</script>
@endpush
