<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillClaim;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserQris;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the global administrative dashboard overview.
     */
    public function index(Request $request): View
    {
        // User statistics
        $totalUsers = User::count();
        $activeUsersCount = User::where('is_active', true)->count();
        $inactiveUsersCount = $totalUsers - $activeUsersCount;
        $newUsersCount30d = User::where('created_at', '>=', now()->subDays(30))->count();

        // Bill statistics & Financial aggregates
        $allBills = Bill::with(['items.claimItems.claim', 'claims.claimItems'])->get();
        $totalBills = $allBills->count();
        $settledBillsCount = $allBills->filter(fn (Bill $b) => $b->isFullySettled())->count();
        $activeBillsCount = $totalBills - $settledBillsCount;
        $totalTransactionAmount = (float) $allBills->sum('grand_total');
        $averageBillAmount = $totalBills > 0 ? (float) ($totalTransactionAmount / $totalBills) : 0.0;
        $settlementRate = $totalBills > 0 ? round(($settledBillsCount / $totalBills) * 100, 1) : 0.0;

        // Claims & Tip statistics
        $totalClaims = BillClaim::count();
        $confirmedClaimsCount = BillClaim::where('status', 'confirmed')->count();
        $pendingClaimsCount = BillClaim::where('status', 'pending')->count();
        $rejectedClaimsCount = BillClaim::where('status', 'rejected')->count();

        $confirmedClaims = BillClaim::where('status', 'confirmed')->get();
        $totalConfirmedAmount = (float) $confirmedClaims->sum(function (BillClaim $claim): float {
            return (float) ($claim->bill_amount > 0 ? $claim->bill_amount : $claim->amount);
        });
        $totalTips = (float) $confirmedClaims->sum(function (BillClaim $claim): float {
            return (float) ($claim->tip_amount > 0 ? $claim->tip_amount : $claim->surplus);
        });

        // Payment Method Adoptions
        $totalQrisCount = UserQris::count();
        $totalBankCount = UserBank::count();

        // Recent Data Feeds
        $recentBills = Bill::with(['user', 'items', 'claims'])
            ->latest()
            ->take(10)
            ->get();

        $recentClaims = BillClaim::with(['bill.user'])
            ->latest()
            ->take(10)
            ->get();

        $recentUsers = User::withCount(['bills', 'qris', 'banks'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'activeUsersCount' => $activeUsersCount,
            'inactiveUsersCount' => $inactiveUsersCount,
            'newUsersCount30d' => $newUsersCount30d,
            'totalBills' => $totalBills,
            'settledBillsCount' => $settledBillsCount,
            'activeBillsCount' => $activeBillsCount,
            'totalTransactionAmount' => $totalTransactionAmount,
            'averageBillAmount' => $averageBillAmount,
            'settlementRate' => $settlementRate,
            'totalClaims' => $totalClaims,
            'confirmedClaimsCount' => $confirmedClaimsCount,
            'pendingClaimsCount' => $pendingClaimsCount,
            'rejectedClaimsCount' => $rejectedClaimsCount,
            'totalConfirmedAmount' => $totalConfirmedAmount,
            'totalTips' => $totalTips,
            'totalQrisCount' => $totalQrisCount,
            'totalBankCount' => $totalBankCount,
            'recentBills' => $recentBills,
            'recentClaims' => $recentClaims,
            'recentUsers' => $recentUsers,
        ]);
    }
}
