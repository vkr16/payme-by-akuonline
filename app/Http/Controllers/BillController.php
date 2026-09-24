<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillBank;
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
     * Display public/host view of the bill.
     */
    public function show(string $slug): View
    {
        $bill = Bill::with(['user', 'items', 'banks'])->where('slug', $slug)->firstOrFail();

        return view('bills.show', [
            'bill' => $bill,
        ]);
    }
}
