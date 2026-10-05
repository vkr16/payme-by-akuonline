<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\QrisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstantQrController extends Controller
{
    /**
     * Display the Instant QR generator page for the authenticated host.
     */
    public function index(Request $request, QrisService $qrisService): View
    {
        /** @var User $user */
        $user = Auth::user();

        $qrisList = $user->qris()->orderByDesc('is_default')->latest()->get();

        foreach ($qrisList as $qris) {
            if (empty($qris->merchant_name) || empty($qris->merchant_city) || $qris->merchant_name === 'Merchant QRIS') {
                $info = $qrisService->extractMerchantInfo($qris->payload);
                if ((empty($qris->merchant_name) || $qris->merchant_name === 'Merchant QRIS') && ! empty($info['merchant_name'])) {
                    $qris->merchant_name = $info['merchant_name'];
                }
                if (empty($qris->merchant_city) && ! empty($info['merchant_city'])) {
                    $qris->merchant_city = $info['merchant_city'];
                }
            }
            if (empty($qris->merchant_name)) {
                $qris->merchant_name = $user->name;
            }
            if (empty($qris->merchant_city)) {
                $qris->merchant_city = 'Indonesia';
            }
        }

        $defaultQris = $qrisList->firstWhere('is_default', true) ?? $qrisList->first();

        return view('instant-qr', [
            'user' => $user,
            'qrisList' => $qrisList,
            'defaultQris' => $defaultQris,
        ]);
    }

    /**
     * Generate dynamic QRIS payload for a specified saved QRIS and nominal amount.
     */
    public function generate(Request $request, QrisService $qrisService): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'qris_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:100'],
        ], [
            'qris_id.required' => 'Pilih QRIS yang ingin digunakan.',
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.min' => 'Nominal pembayaran minimal Rp 1.',
        ]);

        $qris = $user->qris()->where('id', $validated['qris_id'])->first();

        if (! $qris) {
            return response()->json([
                'success' => false,
                'message' => 'QRIS tidak ditemukan atau bukan milik akun Anda.',
            ], 404);
        }

        $amount = (int) round((float) $validated['amount']);
        $dynamicPayload = $qrisService->convertToDynamic($qris->payload, $amount);

        $merchantInfo = $qrisService->extractMerchantInfo($qris->payload);
        $merchantName = ! empty($qris->merchant_name) && $qris->merchant_name !== 'Merchant QRIS'
            ? $qris->merchant_name
            : (! empty($merchantInfo['merchant_name']) ? $merchantInfo['merchant_name'] : $user->name);
        $merchantCity = ! empty($qris->merchant_city) && $qris->merchant_city !== 'Indonesia'
            ? $qris->merchant_city
            : (! empty($merchantInfo['merchant_city']) ? $merchantInfo['merchant_city'] : 'Indonesia');

        return response()->json([
            'success' => true,
            'dynamic_payload' => $dynamicPayload,
            'merchant_name' => $merchantName,
            'merchant_city' => $merchantCity,
            'amount' => $amount,
            'formatted_amount' => 'Rp '.number_format($amount, 0, ',', '.'),
            'note' => ! empty($validated['note']) ? trim($validated['note']) : null,
            'qris_id' => $qris->id,
        ]);
    }
}
