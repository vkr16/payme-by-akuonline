<?php

namespace App\Http\Controllers;

use App\Services\QrisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    /**
     * Display list of saved QRIS and bank accounts.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $qrisList = $user->qris()->orderByDesc('is_default')->latest()->get();
        $bankList = $user->banks()->orderByDesc('is_default')->latest()->get();

        return view('payment-methods.index', [
            'user' => $user,
            'qrisList' => $qrisList,
            'bankList' => $bankList,
        ]);
    }

    /**
     * Store a new bank or e-wallet account.
     */
    public function storeBank(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:100'],
            'account_holder' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ], [
            'bank_name.required' => 'Nama bank atau e-wallet wajib diisi.',
            'account_number.required' => 'Nomor rekening atau nomor HP e-wallet wajib diisi.',
            'account_holder.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        $isDefault = (bool) ($validated['is_default'] ?? false);
        $hasAnyBank = $user->banks()->exists();

        // If this is the user's first bank or marked as default, set is_default = true
        if (! $hasAnyBank || $isDefault) {
            $user->banks()->update(['is_default' => false]);
            $isDefault = true;
        }

        $bank = $user->banks()->create([
            'bank_name' => trim($validated['bank_name']),
            'account_number' => trim($validated['account_number']),
            'account_holder' => trim($validated['account_holder']),
            'is_default' => $isDefault,
        ]);

        $message = "Rekening {$bank->bank_name} ({$bank->account_number}) berhasil ditambahkan!";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'bank' => $bank,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update an existing bank account.
     */
    public function updateBank(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $bank = $user->banks()->where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:100'],
            'account_holder' => ['required', 'string', 'max:100'],
        ], [
            'bank_name.required' => 'Nama bank atau e-wallet wajib diisi.',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_holder.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        $bank->update([
            'bank_name' => trim($validated['bank_name']),
            'account_number' => trim($validated['account_number']),
            'account_holder' => trim($validated['account_holder']),
        ]);

        $message = "Rekening {$bank->bank_name} berhasil diperbarui!";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'bank' => $bank,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Delete a saved bank account.
     */
    public function destroyBank(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $bank = $user->banks()->where('id', $id)->firstOrFail();
        $bankName = $bank->bank_name;
        $wasDefault = $bank->is_default;

        $bank->delete();

        // If the deleted bank was default, promote the latest remaining bank to default
        if ($wasDefault) {
            $latestBank = $user->banks()->latest()->first();
            if ($latestBank) {
                $latestBank->update(['is_default' => true]);
            }
        }

        $message = "Rekening {$bankName} berhasil dihapus.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Set a bank account as default.
     */
    public function setDefaultBank(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $bank = $user->banks()->where('id', $id)->firstOrFail();

        $user->banks()->update(['is_default' => false]);
        $bank->update(['is_default' => true]);

        $message = "Rekening {$bank->bank_name} ({$bank->account_number}) dijadikan rekening utama.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'bank_id' => $bank->id,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Store a new static QRIS.
     */
    public function storeQris(Request $request, QrisService $qrisService): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'payload' => ['required', 'string', 'min:30'],
            'merchant_name' => ['nullable', 'string', 'max:150'],
            'merchant_city' => ['nullable', 'string', 'max:100'],
            'qris_image' => ['nullable', 'image', 'max:10240'],
            'is_default' => ['nullable', 'boolean'],
        ], [
            'payload.required' => 'Kode QRIS statis wajib diisi.',
            'payload.min' => 'Format kode QRIS tidak valid.',
        ]);

        $payload = trim($validated['payload']);

        if (! $qrisService->isValidQris($payload)) {
            $errorMsg = 'Format QRIS tidak sesuai standar EMVCo Indonesia (000201...). Pastikan Anda mengunggah QRIS statis yang valid.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }

            return back()->withErrors(['payload' => $errorMsg])->withInput();
        }

        $merchantInfo = $qrisService->extractMerchantInfo($payload);
        $merchantName = ! empty($validated['merchant_name']) ? trim($validated['merchant_name']) : $merchantInfo['merchant_name'];
        $merchantCity = ! empty($validated['merchant_city']) ? trim($validated['merchant_city']) : $merchantInfo['merchant_city'];

        $imagePath = null;
        if ($request->hasFile('qris_image')) {
            $imagePath = $request->file('qris_image')->store('qris', 'public');
        }

        $isDefault = (bool) ($validated['is_default'] ?? false);
        $hasAnyQris = $user->qris()->exists();

        if (! $hasAnyQris || $isDefault) {
            $user->qris()->update(['is_default' => false]);
            $isDefault = true;
        }

        $qris = $user->qris()->create([
            'merchant_name' => $merchantName,
            'merchant_city' => $merchantCity,
            'payload' => $payload,
            'image_path' => $imagePath,
            'is_default' => $isDefault,
        ]);

        $message = "QRIS \"{$qris->merchant_name}\" berhasil disimpan!";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'qris' => $qris,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update merchant name/city of a saved QRIS.
     */
    public function updateQris(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $qris = $user->qris()->where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'merchant_name' => ['required', 'string', 'max:150'],
            'merchant_city' => ['nullable', 'string', 'max:100'],
        ], [
            'merchant_name.required' => 'Nama merchant QRIS wajib diisi.',
        ]);

        $qris->update([
            'merchant_name' => trim($validated['merchant_name']),
            'merchant_city' => trim($validated['merchant_city'] ?? $qris->merchant_city),
        ]);

        $message = "QRIS \"{$qris->merchant_name}\" berhasil diperbarui!";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'qris' => $qris,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Delete a saved QRIS.
     */
    public function destroyQris(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $qris = $user->qris()->where('id', $id)->firstOrFail();
        $merchantName = $qris->merchant_name;
        $wasDefault = $qris->is_default;

        if ($qris->image_path && Storage::disk('public')->exists($qris->image_path)) {
            Storage::disk('public')->delete($qris->image_path);
        }

        $qris->delete();

        if ($wasDefault) {
            $latestQris = $user->qris()->latest()->first();
            if ($latestQris) {
                $latestQris->update(['is_default' => true]);
            }
        }

        $message = "QRIS \"{$merchantName}\" berhasil dihapus.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Set a QRIS as default.
     */
    public function setDefaultQris(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $qris = $user->qris()->where('id', $id)->firstOrFail();

        $user->qris()->update(['is_default' => false]);
        $qris->update(['is_default' => true]);

        $message = "QRIS \"{$qris->merchant_name}\" dijadikan QRIS utama.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'qris_id' => $qris->id,
            ]);
        }

        return back()->with('success', $message);
    }
}
