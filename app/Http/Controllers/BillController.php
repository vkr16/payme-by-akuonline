<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillBank;
use App\Models\BillClaim;
use App\Models\BillItem;
use App\Models\UserBank;
use App\Models\UserQris;
use App\Services\QrisService;
use App\Services\ReceiptParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BillController extends Controller
{
    /**
     * Show the create bill page for host.
     */
    public function create(Request $request): View
    {
        $user = Auth::user();

        $savedQris = $user->qris()->orderByDesc('is_default')->latest()->get();
        $savedBanks = $user->banks()->orderByDesc('is_default')->latest()->get();

        return view('bills.create', [
            'user' => $user,
            'savedQris' => $savedQris,
            'savedBanks' => $savedBanks,
        ]);
    }

    /**
     * Parse receipt images via AI Vision API.
     */
    public function parseReceipt(Request $request, ReceiptParserService $parser): JsonResponse
    {
        $request->validate([
            'receipt_image' => ['nullable', 'image', 'max:10240'],
            'receipt_images' => ['nullable', 'array'],
            'receipt_images.*' => ['image', 'max:10240'],
            'receipt_price_type' => ['nullable', 'string', 'in:unit_price,total_price'],
        ]);

        $priceType = $request->input('receipt_price_type', 'unit_price');
        $filePaths = [];

        if ($request->hasFile('receipt_images')) {
            foreach ($request->file('receipt_images') as $file) {
                $filePaths[] = $file->getRealPath();
            }
        } elseif ($request->hasFile('receipt_image')) {
            $filePaths[] = $request->file('receipt_image')->getRealPath();
        }

        if (empty($filePaths)) {
            return response()->json([
                'success' => false,
                'error' => 'Tidak ada file gambar struk yang diunggah.',
            ], 422);
        }

        $result = $parser->parseImages($filePaths, $priceType);

        return response()->json($result);
    }

    /**
     * Store a newly created bill in database.
     */
    public function store(Request $request, QrisService $qrisService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'qris_choice' => ['required', 'string', 'in:saved,new,none'],
            'saved_qris_id' => ['nullable', 'integer'],
            'new_qris_payload' => ['nullable', 'string'],
            'new_qris_image' => ['nullable', 'image', 'max:10240'],
            'save_new_qris' => ['nullable', 'boolean'],
            'enable_bank' => ['nullable', 'boolean'],
            'selected_bank_ids' => ['nullable', 'array'],
            'selected_bank_ids.*' => ['integer'],
            'new_banks' => ['nullable', 'array'],
            'new_banks.*.bank_name' => ['nullable', 'string', 'max:100'],
            'new_banks.*.account_number' => ['nullable', 'string', 'max:100'],
            'new_banks.*.account_holder' => ['nullable', 'string', 'max:100'],
            'new_banks.*.save_to_profile' => ['nullable', 'boolean'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'receipt_image' => ['nullable', 'image', 'max:10240'],
            'receipt_images' => ['nullable', 'array'],
            'receipt_images.*' => ['image', 'max:10240'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ], [
            'title.required' => 'Nama acara atau pesanan wajib diisi.',
            'items.required' => 'Daftar item pesanan minimal harus memiliki 1 item.',
            'items.min' => 'Daftar item pesanan minimal harus memiliki 1 item.',
            'items.*.name.required' => 'Nama menu pesanan wajib diisi.',
            'items.*.qty.required' => 'Jumlah item wajib diisi minimal 1.',
            'items.*.price.required' => 'Harga satuan item wajib diisi.',
        ]);

        return DB::transaction(function () use ($request, $validated, $user, $qrisService) {
            // 1. Process QRIS
            $qrisPayload = null;
            $merchantName = null;
            $merchantCity = null;
            $qrisImagePath = null;

            if ($validated['qris_choice'] === 'saved' && ! empty($validated['saved_qris_id'])) {
                $savedQris = $user->qris()->find($validated['saved_qris_id']);
                if ($savedQris) {
                    $qrisPayload = $savedQris->payload;
                    $merchantName = $savedQris->merchant_name;
                    $merchantCity = $savedQris->merchant_city;
                    $qrisImagePath = $savedQris->image_path;
                }
            } elseif ($validated['qris_choice'] === 'new' && ! empty($validated['new_qris_payload'])) {
                $payload = trim($validated['new_qris_payload']);
                if ($qrisService->isValidQris($payload)) {
                    $merchantInfo = $qrisService->extractMerchantInfo($payload);
                    $qrisPayload = $payload;
                    $merchantName = $merchantInfo['merchant_name'];
                    $merchantCity = $merchantInfo['merchant_city'];

                    if ($request->hasFile('new_qris_image')) {
                        $qrisImagePath = $request->file('new_qris_image')->store('bills/qris', 'public');
                    }

                    if ($request->boolean('save_new_qris')) {
                        $hasDefaultQris = $user->qris()->where('is_default', true)->exists();
                        UserQris::create([
                            'user_id' => $user->id,
                            'merchant_name' => $merchantName,
                            'merchant_city' => $merchantCity,
                            'payload' => $qrisPayload,
                            'image_path' => $qrisImagePath,
                            'is_default' => ! $hasDefaultQris,
                        ]);
                    }
                }
            }

            // 2. Process Receipt Image Upload
            $receiptImagePath = null;
            if ($request->hasFile('receipt_images') && count($request->file('receipt_images')) > 0) {
                $receiptImagePath = $request->file('receipt_images')[0]->store('bills/receipts', 'public');
            } elseif ($request->hasFile('receipt_image')) {
                $receiptImagePath = $request->file('receipt_image')->store('bills/receipts', 'public');
            }

            // 3. Generate unique random slug
            $slug = Str::lower(Str::random(10));
            while (Bill::where('slug', $slug)->exists()) {
                $slug = Str::lower(Str::random(10));
            }

            // 4. Create Bill record
            $bill = Bill::create([
                'user_id' => $user->id,
                'slug' => $slug,
                'title' => $validated['title'],
                'qris_payload' => $qrisPayload,
                'qris_merchant_name' => $merchantName,
                'qris_merchant_city' => $merchantCity,
                'qris_image_path' => $qrisImagePath,
                'delivery_fee' => (float) ($validated['delivery_fee'] ?? 0),
                'service_fee' => (float) ($validated['service_fee'] ?? 0),
                'discount' => (float) ($validated['discount'] ?? 0),
                'receipt_image_path' => $receiptImagePath,
                'status' => 'active',
            ]);

            // 5. Create Bill Items
            foreach ($validated['items'] as $item) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'name' => $item['name'],
                    'qty' => (int) $item['qty'],
                    'price' => (float) $item['price'],
                ]);
            }

            // 6. Create Bill Banks (Snapshot)
            if ($request->boolean('enable_bank')) {
                // Attach selected saved banks
                if (! empty($validated['selected_bank_ids'])) {
                    $selectedBanks = $user->banks()->whereIn('id', $validated['selected_bank_ids'])->get();
                    foreach ($selectedBanks as $bank) {
                        BillBank::create([
                            'bill_id' => $bill->id,
                            'bank_name' => $bank->bank_name,
                            'account_number' => $bank->account_number,
                            'account_holder' => $bank->account_holder,
                        ]);
                    }
                }

                // Attach and optionally save new banks
                if (! empty($validated['new_banks'])) {
                    $hasDefaultBank = $user->banks()->where('is_default', true)->exists();

                    foreach ($validated['new_banks'] as $newBank) {
                        if (! empty($newBank['bank_name']) && ! empty($newBank['account_number'])) {
                            $holder = ! empty($newBank['account_holder']) ? $newBank['account_holder'] : $user->name;

                            BillBank::create([
                                'bill_id' => $bill->id,
                                'bank_name' => $newBank['bank_name'],
                                'account_number' => $newBank['account_number'],
                                'account_holder' => $holder,
                            ]);

                            if (! empty($newBank['save_to_profile'])) {
                                UserBank::create([
                                    'user_id' => $user->id,
                                    'bank_name' => $newBank['bank_name'],
                                    'account_number' => $newBank['account_number'],
                                    'account_holder' => $holder,
                                    'is_default' => ! $hasDefaultBank,
                                ]);
                                $hasDefaultBank = true;
                            }
                        }
                    }
                }
            }

            return redirect()->route('bills.show', ['slug' => $bill->slug])->with('success', 'Tagihan patungan berhasil dibuat! Bagikan link ini ke teman-temanmu.');
        });
    }

    /**
     * Display bill page for participants and host.
     */
    public function show(string $slug): View
    {
        $bill = Bill::with([
            'user',
            'items.claimItems.claim',
            'banks',
            'claims.claimItems.item',
        ])->where('slug', $slug)->firstOrFail();

        $isHost = Auth::check() && Auth::id() === $bill->user_id;

        $claimsDetailData = [];
        foreach ($bill->claims as $claim) {
            $claimsDetailData[$claim->id] = $claim->toDetailArray();
        }

        return view('bills.show', [
            'bill' => $bill,
            'isHost' => $isHost,
            'claimsDetailData' => $claimsDetailData,
        ]);
    }

    /**
     * Calculate nominal and dynamic QRIS payload for selected items.
     */
    public function calculateSelection(Request $request, string $slug, QrisService $qrisService): JsonResponse
    {
        $bill = Bill::with(['items.claimItems.claim'])->where('slug', $slug)->firstOrFail();

        $selectedItems = $request->input('items', []); // [item_id => qty]
        $roundUp = $request->boolean('round_up', false);

        $itemsSubtotal = 0.0;
        $itemsSelectedCount = 0;

        foreach ($bill->items as $item) {
            $claimedQty = (int) ($selectedItems[$item->id] ?? 0);
            if ($claimedQty > 0) {
                $validQty = min($claimedQty, $item->remaining_qty);
                $itemsSubtotal += ($validQty * (float) $item->price);
                $itemsSelectedCount += $validQty;
            }
        }

        $totalBillSubtotal = $bill->items_subtotal;
        $deliveryFeeShare = 0.0;
        $serviceFeeShare = 0.0;
        $discountShare = 0.0;
        $feeShare = 0.0;

        if ($totalBillSubtotal > 0 && $itemsSubtotal > 0) {
            $proportion = $itemsSubtotal / $totalBillSubtotal;
            $deliveryFeeShare = (float) round($proportion * (float) $bill->delivery_fee);
            $serviceFeeShare = (float) round($proportion * (float) $bill->service_fee);
            $discountShare = (float) round($proportion * (float) $bill->discount);
            $feeShare = ($deliveryFeeShare + $serviceFeeShare) - $discountShare;
        }

        $exactPayable = (float) max(0, round($itemsSubtotal + $feeShare));
        $totalPayable = $exactPayable;
        $roundUpExtra = 0.0;

        if ($roundUp && $exactPayable > 0) {
            $rounded = (float) (ceil($exactPayable / 1000) * 1000);
            $roundUpExtra = max(0, $rounded - $exactPayable);
            $totalPayable = $rounded;
        }

        $dynamicQrisPayload = '';
        if (! empty($bill->qris_payload) && $totalPayable > 0) {
            $dynamicQrisPayload = $qrisService->convertToDynamic($bill->qris_payload, $totalPayable);
        }

        return response()->json([
            'success' => true,
            'items_subtotal' => $itemsSubtotal,
            'items_count' => $itemsSelectedCount,
            'fee_share' => $feeShare,
            'delivery_fee_share' => $deliveryFeeShare,
            'service_fee_share' => $serviceFeeShare,
            'discount_share' => $discountShare,
            'exact_payable' => $exactPayable,
            'round_up_extra' => $roundUpExtra,
            'total_payable' => $totalPayable,
            'proportion_percent' => $totalBillSubtotal > 0 ? round(($itemsSubtotal / $totalBillSubtotal) * 100, 1) : 0,
            'dynamic_qris_payload' => $dynamicQrisPayload,
        ]);
    }

    /**
     * Submit payment claim ("Saya Sudah Bayar").
     */
    public function claimPayment(Request $request, string $slug): JsonResponse
    {
        $bill = Bill::with(['items.claimItems.claim'])->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'payer_name' => ['required', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'actual_amount' => ['nullable', 'numeric', 'min:0'],
            'round_up' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'integer', 'min:1'],
        ], [
            'payer_name.required' => 'Nama kamu wajib diisi agar host dapat mengenali transfermu.',
            'items.required' => 'Pilih minimal satu menu pesanan yang ingin kamu bayar.',
        ]);

        $payerName = trim($validated['payer_name']);
        $paymentMethod = $validated['payment_method'] ?? 'qris';
        $selectedItems = $validated['items'];
        $roundUp = (bool) ($validated['round_up'] ?? false);

        // Enforce unique payer name per bill (case-insensitive & trimmed)
        $existingClaim = BillClaim::where('bill_id', $bill->id)
            ->whereRaw('LOWER(TRIM(payer_name)) = ?', [mb_strtolower($payerName)])
            ->first();

        if ($existingClaim) {
            return response()->json([
                'success' => false,
                'message' => "Nama \"{$payerName}\" sudah terdaftar dalam klaim tagihan ini. Gunakan nama lain atau tambahkan pembeda (contoh: {$payerName} 2).",
            ], 422);
        }

        $itemsSubtotal = 0.0;
        $validItemsToClaim = [];

        foreach ($bill->items as $item) {
            $requestedQty = (int) ($selectedItems[$item->id] ?? 0);
            if ($requestedQty > 0) {
                $claimableQty = min($requestedQty, $item->remaining_qty);
                if ($claimableQty > 0) {
                    $validItemsToClaim[$item->id] = $claimableQty;
                    $itemsSubtotal += ($claimableQty * (float) $item->price);
                }
            }
        }

        if (empty($validItemsToClaim)) {
            return response()->json([
                'success' => false,
                'message' => 'Menu yang kamu pilih sudah habis terbayar oleh kawan lain.',
            ], 422);
        }

        $totalBillSubtotal = $bill->items_subtotal;
        $deliveryFeeShare = 0.0;
        $serviceFeeShare = 0.0;
        $discountShare = 0.0;
        $feeShare = 0.0;

        if ($totalBillSubtotal > 0 && $itemsSubtotal > 0) {
            $proportion = $itemsSubtotal / $totalBillSubtotal;
            $deliveryFeeShare = (float) round($proportion * (float) $bill->delivery_fee);
            $serviceFeeShare = (float) round($proportion * (float) $bill->service_fee);
            $discountShare = (float) round($proportion * (float) $bill->discount);
            $feeShare = ($deliveryFeeShare + $serviceFeeShare) - $discountShare;
        }

        $exactPaid = (float) max(0, round($itemsSubtotal + $feeShare));
        $totalPaid = $exactPaid;

        if ($request->filled('actual_amount')) {
            $customActual = (float) $request->input('actual_amount');
            if ($customActual < $exactPaid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal yang dibayarkan tidak boleh lebih kecil dari total tagihan (Rp '.number_format($exactPaid, 0, ',', '.').').',
                ], 422);
            }
            $totalPaid = $customActual;
        } elseif ($roundUp && $exactPaid > 0) {
            $totalPaid = (float) (ceil($exactPaid / 1000) * 1000);
        }

        $billAmount = min($totalPaid, $exactPaid);
        $tipAmount = max(0, $totalPaid - $billAmount);

        return DB::transaction(function () use ($bill, $payerName, $totalPaid, $billAmount, $tipAmount, $paymentMethod, $validItemsToClaim) {
            $claim = BillClaim::create([
                'bill_id' => $bill->id,
                'payer_name' => $payerName,
                'amount' => $totalPaid,
                'bill_amount' => $billAmount,
                'tip_amount' => $tipAmount,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
            ]);

            foreach ($validItemsToClaim as $itemId => $qty) {
                $claim->claimItems()->create([
                    'bill_item_id' => $itemId,
                    'qty' => $qty,
                ]);
            }

            $bill->refresh();
            $bill->load(['items.claimItems.claim']);
            $itemsRemaining = [];
            foreach ($bill->items as $item) {
                $itemsRemaining[$item->id] = [
                    'remaining' => $item->remaining_qty,
                    'total' => $item->qty,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => "Terima kasih {$payerName}! Klaim pembayaranmu telah dicatat dan menunggu konfirmasi dari Host.",
                'amount' => $totalPaid,
                'claim_id' => $claim->id,
                'claim_data' => $claim->toDetailArray(),
                'items_remaining' => $itemsRemaining,
                'bill_summary' => $bill->getSummaryArray(),
            ]);
        });
    }

    /**
     * Host confirms multiple payment claims simultaneously.
     */
    public function batchConfirmClaims(Request $request, string $slug): JsonResponse
    {
        $bill = Bill::where('slug', $slug)->where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'claim_ids' => 'required|array|min:1',
            'claim_ids.*' => 'integer',
        ]);

        $claims = $bill->claims()
            ->whereIn('id', $validated['claim_ids'])
            ->where('status', 'pending')
            ->get();

        if ($claims->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada klaim berstatus menunggu yang dapat dikonfirmasi.',
            ], 422);
        }

        $confirmedIds = [];
        $totalAmount = 0.0;

        DB::transaction(function () use ($claims, &$confirmedIds, &$totalAmount) {
            foreach ($claims as $claim) {
                $claim->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
                $confirmedIds[] = $claim->id;
                $totalAmount += (float) $claim->amount;
            }
        });

        $bill->refresh();
        $bill->load(['items.claimItems.claim', 'claims']);
        $count = count($confirmedIds);
        $formattedTotal = 'Rp '.number_format($totalAmount, 0, ',', '.');
        $message = "Berhasil mengonfirmasi {$count} klaim pembayaran ({$formattedTotal})!";

        return response()->json([
            'success' => true,
            'message' => $message,
            'confirmed_ids' => $confirmedIds,
            'confirmed_count' => $count,
            'total_amount' => $totalAmount,
            'total_amount_formatted' => $formattedTotal,
            'bill_summary' => $bill->getSummaryArray(),
        ]);
    }

    /**
     * Host confirms a payment claim (Anti-Fake Claim approval).
     */
    public function confirmClaim(Request $request, string $slug, int $claimId): RedirectResponse|JsonResponse
    {
        $bill = Bill::where('slug', $slug)->where('user_id', Auth::id())->firstOrFail();
        $claim = $bill->claims()->where('id', $claimId)->firstOrFail();

        $claim->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $bill->refresh();
        $bill->load(['items.claimItems.claim', 'claims']);
        $message = "Pembayaran dari {$claim->payer_name} (Rp ".number_format($claim->amount, 0, ',', '.').') telah dikonfirmasi!';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'claim_id' => $claim->id,
                'status' => 'confirmed',
                'bill_summary' => $bill->getSummaryArray(),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Host rejects a payment claim.
     */
    public function rejectClaim(Request $request, string $slug, int $claimId): RedirectResponse|JsonResponse
    {
        $bill = Bill::where('slug', $slug)->where('user_id', Auth::id())->firstOrFail();
        $claim = $bill->claims()->where('id', $claimId)->firstOrFail();

        $payerName = $claim->payer_name;
        $claimIdDeleted = $claim->id;
        $claim->delete();

        $bill->refresh();
        $bill->load(['items.claimItems.claim']);
        $itemsRemaining = [];
        foreach ($bill->items as $item) {
            $itemsRemaining[$item->id] = [
                'remaining' => $item->remaining_qty,
                'total' => $item->qty,
            ];
        }

        $message = "Klaim pembayaran dari {$payerName} telah dibatalkan / ditolak.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'claim_id' => $claimIdDeleted,
                'items_remaining' => $itemsRemaining,
                'bill_summary' => $bill->getSummaryArray(),
            ]);
        }

        return back()->with('success', $message);
    }
}
