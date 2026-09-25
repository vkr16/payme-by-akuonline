<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'slug',
    'title',
    'qris_payload',
    'qris_merchant_name',
    'qris_merchant_city',
    'qris_image_path',
    'delivery_fee',
    'service_fee',
    'discount',
    'receipt_image_path',
    'status',
])]
class Bill extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    /**
     * Get the host user that owns this bill.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the line items for this bill.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    /**
     * Get the alternative bank accounts for this bill.
     */
    public function banks(): HasMany
    {
        return $this->hasMany(BillBank::class);
    }

    /**
     * Get all payment claims on this bill.
     */
    public function claims(): HasMany
    {
        return $this->hasMany(BillClaim::class);
    }

    /**
     * Calculate items subtotal.
     */
    public function getItemsSubtotalAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return (float) $item->price * (int) $item->qty;
        });
    }

    /**
     * Net extra fees (delivery + service - discount).
     */
    public function getNetExtraFeesAttribute(): float
    {
        return (float) ($this->delivery_fee + $this->service_fee - $this->discount);
    }

    /**
     * Calculate grand total of the bill.
     */
    public function getGrandTotalAttribute(): float
    {
        return max(0, $this->items_subtotal + $this->net_extra_fees);
    }

    /**
     * Total amount confirmed paid by host.
     */
    public function getTotalConfirmedPaidAttribute(): float
    {
        return (float) $this->claims()->where('status', 'confirmed')->sum('amount');
    }

    /**
     * Total amount claimed (pending + confirmed).
     */
    public function getTotalClaimedAttribute(): float
    {
        return (float) $this->claims()->whereIn('status', ['confirmed', 'pending'])->sum('amount');
    }

    /**
     * Remaining unpaid amount based on confirmed payments.
     */
    public function getRemainingConfirmedAmountAttribute(): float
    {
        return max(0, $this->grand_total - $this->total_confirmed_paid);
    }

    /**
     * Payment progress percentage based on confirmed payments.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->grand_total <= 0) {
            return 100.0;
        }

        return min(100.0, round(($this->total_confirmed_paid / $this->grand_total) * 100, 1));
    }

    /**
     * Check if entire bill is fully settled/confirmed.
     */
    public function isFullySettled(): bool
    {
        $hasItems = $this->relationLoaded('items') ? $this->items->count() > 0 : $this->items()->exists();

        return $hasItems && $this->remaining_confirmed_amount <= 0;
    }

    /**
     * Get summarized progress and confirmed payment status array.
     *
     * @return array{
     *     grand_total: float,
     *     grand_total_formatted: string,
     *     total_confirmed_paid: float,
     *     total_confirmed_paid_formatted: string,
     *     remaining_confirmed_amount: float,
     *     remaining_confirmed_amount_formatted: string,
     *     progress_percentage: float,
     *     is_fully_settled: bool,
     *     total_claims_count: int,
     *     pending_claims_count: int
     * }
     */
    public function getSummaryArray(): array
    {
        return [
            'grand_total' => (float) $this->grand_total,
            'grand_total_formatted' => 'Rp '.number_format($this->grand_total, 0, ',', '.'),
            'total_confirmed_paid' => (float) $this->total_confirmed_paid,
            'total_confirmed_paid_formatted' => 'Rp '.number_format($this->total_confirmed_paid, 0, ',', '.'),
            'remaining_confirmed_amount' => (float) $this->remaining_confirmed_amount,
            'remaining_confirmed_amount_formatted' => 'Rp '.number_format($this->remaining_confirmed_amount, 0, ',', '.'),
            'progress_percentage' => (float) $this->progress_percentage,
            'is_fully_settled' => (bool) $this->isFullySettled(),
            'total_claims_count' => (int) $this->claims()->count(),
            'pending_claims_count' => (int) $this->claims()->where('status', 'pending')->count(),
        ];
    }
}
