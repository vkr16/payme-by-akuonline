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

        return view('dashboard', [
            'user' => $user,
            'bills' => $bills,
            'totalBills' => $totalBills,
            'settledBillsCount' => $settledBillsCount,
            'qrisCount' => $qrisCount,
            'bankCount' => $bankCount,
            'hasQris' => $qrisCount > 0,
            'hasBank' => $bankCount > 0,
        ]);
    }
}
