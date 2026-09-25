<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the host dashboard overview.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $bills = $user->bills()->with(['items.claimItems.claim', 'banks', 'claims.claimItems'])->latest()->get();
        $totalBills = $bills->count();
        $settledBillsCount = $bills->filter(fn ($b) => $b->isFullySettled())->count();
        $qrisCount = $user->qris()->count();
        $bankCount = $user->banks()->count();

        // Calculate total nominal transaction amount coming through PayMe from all bills created by user
        $totalTransactionAmount = (float) $bills->sum('grand_total');

        // Calculate total tip obtained from confirmed claims across all bills
        $allConfirmedClaims = $bills->flatMap(function ($bill) {
            return $bill->claims->where('status', 'confirmed');
        });
        $totalTips = (float) $allConfirmedClaims->sum(function ($claim) {
            return (float) ($claim->tip_amount > 0 ? $claim->tip_amount : $claim->surplus);
        });

        return view('dashboard', [
            'user' => $user,
            'bills' => $bills,
            'totalBills' => $totalBills,
            'settledBillsCount' => $settledBillsCount,
            'totalTransactionAmount' => $totalTransactionAmount,
            'totalTips' => $totalTips,
            'qrisCount' => $qrisCount,
            'bankCount' => $bankCount,
            'hasQris' => $qrisCount > 0,
            'hasBank' => $bankCount > 0,
        ]);
    }
}
