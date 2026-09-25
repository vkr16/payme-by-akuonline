<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['bill_id', 'name', 'qty', 'price'])]
class BillItem extends Model
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
            'qty' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the bill that owns this item.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get all claim items for this bill item.
     */
    public function claimItems(): HasMany
    {
        return $this->hasMany(BillClaimItem::class, 'bill_item_id');
    }

    /**
     * Get claimed quantity (only counting confirmed or pending claims).
     */
    public function getClaimedQtyAttribute(): int
    {
        return (int) $this->claimItems()
            ->whereHas('claim', function ($query) {
                $query->whereIn('status', ['confirmed', 'pending']);
            })
            ->sum('qty');
    }

    /**
     * Get remaining available quantity.
     */
    public function getRemainingQtyAttribute(): int
    {
        return max(0, (int) $this->qty - $this->claimed_qty);
    }

    /**
     * Get line subtotal.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->price * (int) $this->qty;
    }
}
