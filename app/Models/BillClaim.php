<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'bill_id',
    'payer_name',
    'amount',
    'payment_method',
    'status',
    'confirmed_at',
])]
class BillClaim extends Model
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
            'amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the bill that owns this claim.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the items claimed in this claim.
     */
    public function claimItems(): HasMany
    {
        return $this->hasMany(BillClaimItem::class, 'bill_claim_id');
    }

    /**
     * Calculate exact proportional amount payable for this claim.
     */
    public function getExactPayableAttribute(): float
    {
        $bill = $this->bill;
        if (! $bill) {
            return (float) $this->amount;
        }

        $itemsSubtotal = 0.0;
        foreach ($this->claimItems as $cItem) {
            if ($cItem->item) {
                $itemsSubtotal += ((int) $cItem->qty * (float) $cItem->item->price);
            }
        }

        $totalBillSubtotal = $bill->items_subtotal;
        $netExtraFees = $bill->net_extra_fees;

        $feeShare = 0.0;
        if ($totalBillSubtotal > 0 && $itemsSubtotal > 0) {
            $proportion = $itemsSubtotal / $totalBillSubtotal;
            $feeShare = $proportion * $netExtraFees;
        }

        return (float) max(0, round($itemsSubtotal + $feeShare));
    }

    /**
     * Calculate tip/surplus.
     */
    public function getSurplusAttribute(): float
    {
        return (float) max(0, (float) $this->amount - $this->exact_payable);
    }

    /**
     * Check if claim is confirmed by host.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if claim is pending verification.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get structured detail array for frontend modal popup and WhatsApp summaries.
     *
     * @return array<string, mixed>
     */
    public function toDetailArray(): array
    {
        $bill = $this->bill;
        $items = [];
        $itemsSubtotal = 0.0;

        foreach ($this->claimItems as $cItem) {
            $name = $cItem->item?->name ?? 'Menu';
            $qty = (int) $cItem->qty;
            $price = (float) ($cItem->item?->price ?? 0);
            $subtotal = $qty * $price;
            $itemsSubtotal += $subtotal;
            $items[] = [
                'item_id' => $cItem->bill_item_id,
                'name' => $name,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $subtotal,
            ];
        }

        $totBillSubtotal = (float) ($bill?->items_subtotal ?? 0);
        $prop = $totBillSubtotal > 0 ? ($itemsSubtotal / $totBillSubtotal) : 0;
        $propPercent = round($prop * 100, 1);

        $shareDeliv = round($prop * (float) ($bill?->delivery_fee ?? 0));
        $shareServ = round($prop * (float) ($bill?->service_fee ?? 0));
        $shareDisc = round($prop * (float) ($bill?->discount ?? 0));

        $amountPaid = (float) $this->amount;
        $exactPayable = $this->exact_payable;
        $surplus = $this->surplus;

        return [
            'id' => $this->id,
            'payer_name' => $this->payer_name,
            'amount' => $amountPaid,
            'payment_method' => $this->payment_method ?? 'qris',
            'status' => $this->status,
            'created_at_formatted' => $this->created_at ? $this->created_at->translatedFormat('d M Y, H:i').' WIB' : '-',
            'created_at_relative' => $this->created_at ? $this->created_at->diffForHumans() : '',
            'items' => $items,
            'items_count' => count($items),
            'items_total_qty' => array_sum(array_column($items, 'qty')),
            'items_subtotal' => $itemsSubtotal,
            'bill_subtotal' => $totBillSubtotal,
            'proportion_percent' => $propPercent,
            'share_delivery' => $shareDeliv,
            'share_service' => $shareServ,
            'share_discount' => $shareDisc,
            'exact_payable' => $exactPayable,
            'surplus' => $surplus,
        ];
    }
}
