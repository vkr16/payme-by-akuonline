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
     * Calculate items subtotal.
     */
    public function getItemsSubtotalAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return (float) $item->price * (int) $item->qty;
        });
    }

    /**
     * Calculate grand total of the bill.
     */
    public function getGrandTotalAttribute(): float
    {
        $itemsSubtotal = $this->items_subtotal;
        $deliveryFee = (float) $this->delivery_fee;
        $serviceFee = (float) $this->service_fee;
        $discount = (float) $this->discount;

        return max(0, $itemsSubtotal + $deliveryFee + $serviceFee - $discount);
    }
}
