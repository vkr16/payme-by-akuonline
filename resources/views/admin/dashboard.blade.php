@extends('layouts.app')

@section('title', 'Dashboard Administrator - PayMe')
@section('meta_description', 'Panel pemantauan metrik dan data transaksi global platform PayMe.')

@section('content')
<div class="space-y-8 pb-16">

    <!-- Admin Welcome Greeting & Header -->
    <div class="card-solid rounded-2xl p-6 sm:p-8 bg-white border border-zinc-200/90 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center font-bold text-xl shadow-2xs">
                <i class="fa-solid fa-shield-halved text-2xl text-amber-600"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black text-zinc-900 tracking-tight">Admin Control Center</h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                        <i class="fa-solid fa-crown text-[10px]"></i>
                        <span>Super Administrator</span>
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                    Ringkasan performa menyeluruh, transaksi global, dan metrik operasional PayMe.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full md:w-auto">
            <a href="{{ route('dashboard') }}" class="touch-target flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-zinc-100 hover:bg-zinc-200/80 text-zinc-800 border border-zinc-200 font-semibold text-xs sm:text-sm transition-all">
                <i class="fa-light fa-arrow-left text-xs"></i>
                <span class="whitespace-nowrap">Dashboard Host</span>
            </a>
            <a href="{{ route('bills.create') }}" class="touch-target flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl btn-primary font-semibold text-xs sm:text-sm shadow-xs transition-all">
                <i class="fa-solid fa-plus text-xs shrink-0"></i>
                <span class="whitespace-nowrap">Buat Patungan</span>
            </a>
        </div>
    </div>

    <!-- Executive KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: GTV (Gross Transaction Volume) -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-zinc-400 mb-2">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Gross Transaksi (GTV)</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center">
                        <i class="fa-light fa-vault text-base"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-zinc-900 tabular-nums">
                    Rp {{ number_format($totalTransactionAmount, 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[11px] text-zinc-500 mt-2 flex items-center justify-between">
                <span>Rata-rata / tagihan:</span>
                <span class="font-bold text-zinc-700 tabular-nums">Rp {{ number_format($averageBillAmount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Card 2: Total Dana Terlunasi -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-zinc-400 mb-2">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Terlunasi</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center">
                        <i class="fa-light fa-badge-check text-base"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-800 tabular-nums">
                    Rp {{ number_format($totalConfirmedAmount, 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[11px] text-zinc-500 mt-2 flex items-center justify-between">
                <span>Tingkat Pelunasan:</span>
                <span class="font-bold text-emerald-700 tabular-nums">{{ $settlementRate }}%</span>
            </div>
        </div>

        <!-- Card 3: Total Pengguna -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-zinc-400 mb-2">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Pengguna</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center">
                        <i class="fa-light fa-users text-base"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-zinc-900 tabular-nums">{{ $totalUsers }}</div>
            </div>
            <div class="text-[11px] text-zinc-500 mt-2 flex items-center justify-between">
                <span>{{ $activeUsersCount }} Aktif &bull; {{ $inactiveUsersCount }} Nonaktif</span>
                <span class="font-bold text-indigo-600">+{{ $newUsersCount30d }} baru</span>
            </div>
        </div>

        <!-- Card 4: Total Tagihan (Bills) -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between text-zinc-400 mb-2">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Tagihan</span>
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-700 flex items-center justify-center">
                        <i class="fa-light fa-receipt text-base"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-zinc-900 tabular-nums">{{ $totalBills }}</div>
            </div>
            <div class="text-[11px] text-zinc-500 mt-2 flex items-center justify-between">
                <span class="text-emerald-700 font-medium">{{ $settledBillsCount }} Lunas</span>
                <span class="text-zinc-400">&bull;</span>
                <span class="text-sky-700 font-medium">{{ $activeBillsCount }} Berjalan</span>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics Row: Tips, Claims, Payment Methods -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 5: Tips Sukarela Diperoleh -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider block">Total Tip Pengguna</span>
                <div class="text-xl font-bold text-zinc-900 mt-1 tabular-nums">
                    Rp {{ number_format($totalTips, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-zinc-400 mt-1 block">Apresiasi partisipan untuk host</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i class="fa-light fa-gift text-lg"></i>
            </div>
        </div>

        <!-- Card 6: Total Klaim Pembayaran -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider block">Total Klaim Terdata</span>
                <div class="text-xl font-bold text-zinc-900 mt-1 tabular-nums">{{ $totalClaims }} Transaksi</div>
                <div class="text-[11px] text-zinc-400 mt-1 flex items-center gap-1.5">
                    <span class="text-emerald-700 font-semibold">{{ $confirmedClaimsCount }} Sukses</span>
                    <span>&bull;</span>
                    <span class="text-amber-700 font-semibold">{{ $pendingClaimsCount }} Pending</span>
                    <span>&bull;</span>
                    <span class="text-rose-700 font-semibold">{{ $rejectedClaimsCount }} Ditolak</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                <i class="fa-light fa-file-invoice-dollar text-lg"></i>
            </div>
        </div>

        <!-- Card 7: Adopsi Rekening & QRIS -->
        <div class="p-5 rounded-2xl bg-white border border-zinc-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider block">Metode Pembayaran</span>
                <div class="text-xl font-bold text-zinc-900 mt-1 tabular-nums">{{ $totalQrisCount + $totalBankCount }} Rekening</div>
                <span class="text-[11px] text-zinc-400 mt-1 block">
                    {{ $totalQrisCount }} Merchant QRIS &bull; {{ $totalBankCount }} Akun Bank
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-700 flex items-center justify-center shrink-0">
                <i class="fa-light fa-qrcode text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Table 1: Recent Split Bills (10 Tagihan Terbaru) -->
    <div class="bg-white rounded-2xl border border-zinc-200/90 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center">
                    <i class="fa-light fa-receipt text-sm"></i>
                </div>
                <div>
                    <h2 class="font-bold text-zinc-900 text-sm sm:text-base">10 Tagihan Terbaru</h2>
                    <p class="text-xs text-zinc-400">Tagihan patungan terkini yang dibuat oleh seluruh pengguna</p>
                </div>
            </div>
            <span class="text-xs text-zinc-400 font-medium hidden sm:inline">Global Feed</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50 text-zinc-500 uppercase tracking-wider font-semibold border-b border-zinc-100">
                    <tr>
                        <th class="py-3 px-4">Judul Tagihan</th>
                        <th class="py-3 px-4">Host (Pembuat)</th>
                        <th class="py-3 px-4">Total Tagihan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tanggal Dibuat</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($recentBills as $bill)
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-zinc-900 text-sm">{{ $bill->title }}</div>
                                <div class="text-[11px] text-zinc-400 font-mono mt-0.5">#{{ $bill->slug }} &bull; {{ $bill->items->count() }} item</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-zinc-800">{{ $bill->user->name ?? 'User #'.$bill->user_id }}</div>
                                <div class="text-[11px] text-zinc-400">{{ $bill->user->email ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 tabular-nums font-bold text-zinc-900">
                                Rp {{ number_format($bill->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($bill->isFullySettled())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <i class="fa-light fa-check text-[10px]"></i>
                                        <span>Lunas</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                        <i class="fa-light fa-clock text-[10px]"></i>
                                        <span>Aktif</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-zinc-500 whitespace-nowrap">
                                {{ $bill->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('bills.show', $bill->slug) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-emerald-800 bg-emerald-50 hover:bg-emerald-100 font-semibold transition-colors">
                                    <span>Buka</span>
                                    <i class="fa-light fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-zinc-400">
                                <i class="fa-light fa-folder-open text-3xl mb-2 block"></i>
                                Belum ada tagihan patungan yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table 2: Recent Payment Claims (10 Klaim Pembayaran Terbaru) -->
    <div class="bg-white rounded-2xl border border-zinc-200/90 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-800 flex items-center justify-center">
                    <i class="fa-light fa-file-invoice-dollar text-sm"></i>
                </div>
                <div>
                    <h2 class="font-bold text-zinc-900 text-sm sm:text-base">10 Klaim Pembayaran Terbaru</h2>
                    <p class="text-xs text-zinc-400">Transaksi partisipan yang masuk untuk verifikasi host</p>
                </div>
            </div>
            <span class="text-xs text-zinc-400 font-medium hidden sm:inline">Aktivitas Real-Time</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50 text-zinc-500 uppercase tracking-wider font-semibold border-b border-zinc-100">
                    <tr>
                        <th class="py-3 px-4">Partisipan</th>
                        <th class="py-3 px-4">Tagihan Terkait</th>
                        <th class="py-3 px-4">Host Penerima</th>
                        <th class="py-3 px-4">Nominal Bayar</th>
                        <th class="py-3 px-4">Status Klaim</th>
                        <th class="py-3 px-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($recentClaims as $claim)
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-zinc-900">
                                {{ $claim->payer_name }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($claim->bill)
                                    <a href="{{ route('bills.show', $claim->bill->slug) }}" target="_blank" class="font-medium text-emerald-800 hover:underline">
                                        {{ $claim->bill->title }}
                                    </a>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700">
                                {{ $claim->bill?->user?->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 tabular-nums">
                                <span class="font-bold text-zinc-900">Rp {{ number_format($claim->amount, 0, ',', '.') }}</span>
                                @if((float)$claim->tip_amount > 0)
                                    <span class="block text-[10px] text-emerald-700 font-medium">+ Tip: Rp {{ number_format($claim->tip_amount, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($claim->status === 'confirmed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <i class="fa-light fa-check text-[10px]"></i>
                                        <span>Confirmed</span>
                                    </span>
                                @elseif($claim->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="fa-light fa-hourglass-start text-[10px]"></i>
                                        <span>Pending</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                        <i class="fa-light fa-xmark text-[10px]"></i>
                                        <span>Rejected</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-zinc-500 whitespace-nowrap">
                                {{ $claim->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-zinc-400">
                                <i class="fa-light fa-folder-open text-3xl mb-2 block"></i>
                                Belum ada klaim pembayaran yang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table 3: Recent Registered Users (10 Pengguna Terbaru) -->
    <div class="bg-white rounded-2xl border border-zinc-200/90 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-zinc-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-800 flex items-center justify-center">
                    <i class="fa-light fa-users text-sm"></i>
                </div>
                <div>
                    <h2 class="font-bold text-zinc-900 text-sm sm:text-base">10 Pengguna Terdaftar Terbaru</h2>
                    <p class="text-xs text-zinc-400">Daftar pengguna yang baru mendaftar di sistem</p>
                </div>
            </div>
            <span class="text-xs text-zinc-400 font-medium hidden sm:inline">Database User</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50 text-zinc-500 uppercase tracking-wider font-semibold border-b border-zinc-100">
                    <tr>
                        <th class="py-3 px-4">Nama Pengguna</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Tagihan Dibuat</th>
                        <th class="py-3 px-4">Terdaftar Pada</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($recentUsers as $userItem)
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-zinc-900">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center text-xs font-bold text-zinc-700">
                                        {{ strtoupper(substr($userItem->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $userItem->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-zinc-600 font-mono text-[11px]">
                                {{ $userItem->email }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($userItem->isAdmin())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="fa-solid fa-crown text-[9px]"></i>
                                        <span>Admin</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-700 border border-zinc-200">
                                        <span>User</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 tabular-nums text-zinc-700 font-semibold">
                                {{ $userItem->bills_count }} bill
                            </td>
                            <td class="py-3.5 px-4 text-zinc-500 whitespace-nowrap">
                                {{ $userItem->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($userItem->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-zinc-400">
                                <i class="fa-light fa-users text-3xl mb-2 block"></i>
                                Belum ada pengguna terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
