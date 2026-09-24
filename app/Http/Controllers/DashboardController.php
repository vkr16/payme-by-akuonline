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

        $bills = $user->bills()->with(['items', 'banks'])->latest()->get();
        $totalBills = $bills->count();
        $hasQris = $user->qris()->exists();
        $hasBank = $user->banks()->exists();

        return view('dashboard', [
            'user' => $user,
            'bills' => $bills,
            'totalBills' => $totalBills,
            'hasQris' => $hasQris,
            'hasBank' => $hasBank,
        ]);
    }
}
