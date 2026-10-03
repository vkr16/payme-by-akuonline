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
    'bill_amount',
    'tip_amount',
    'payment_method',
    'status',
    'confirmed_at',
])]
class BillClaim extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (BillClaim $claim) {
            if ($claim->bill_amount === null || (float) $claim->bill_amount <= 0) {
                $total = (float) $claim->amount;
                $claim->bill_amount = $total;
                $claim->tip_amount = 0;
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bill_amount' => 'decimal:2',
            'tip_amount' => 'decimal:2',
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

        $totalBillSubtotal = (float) $bill->items_subtotal;

        $feeShare = 0.0;
        if ($totalBillSubtotal > 0 && $itemsSubtotal > 0) {
            $proportion = $itemsSubtotal / $totalBillSubtotal;
            $delivShare = (float) round($proportion * (float) $bill->delivery_fee, 2);
            $servShare = (float) round($proportion * (float) $bill->service_fee, 2);
            $discShare = (float) round($proportion * (float) $bill->discount, 2);
            $feeShare = (float) round($delivShare + $servShare - $discShare, 2);
        }

        return (float) max(0, round($itemsSubtotal + $feeShare, 2));
    }

    /**
     * Calculate tip/surplus.
     */
    public function getSurplusAttribute(): float
    {
        if ((float) $this->tip_amount > 0) {
            return (float) $this->tip_amount;
        }

        return (float) max(0, round((float) $this->amount - $this->exact_payable, 2));
    }

    /**
     * Get human-formatted tip amount with decimals only if applicable.
     */
    public function getFormattedTipAmountAttribute(): string
    {
        $val = (float) $this->tip_amount;
        if (floor($val) == $val) {
            return 'Rp '.number_format($val, 0, ',', '.');
        }

        $formatted = number_format($val, 2, ',', '.');
        $formatted = rtrim(rtrim($formatted, '0'), ',');

        return 'Rp '.$formatted;
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

        $shareDeliv = (float) round($prop * (float) ($bill?->delivery_fee ?? 0), 2);
        $shareServ = (float) round($prop * (float) ($bill?->service_fee ?? 0), 2);
        $shareDisc = (float) round($prop * (float) ($bill?->discount ?? 0), 2);
        $feeShare = (float) round($shareDeliv + $shareServ - $shareDisc, 2);

        $amountPaid = (float) $this->amount;
        $exactPayable = (float) $this->exact_payable;
        $billAmount = (float) ($this->bill_amount > 0 ? $this->bill_amount : min($amountPaid, $exactPayable));
        $tipAmount = (float) ($this->tip_amount > 0 ? $this->tip_amount : max(0, round($amountPaid - $billAmount, 2)));

        return [
            'id' => $this->id,
            'payer_name' => $this->payer_name,
            'amount' => $amountPaid,
            'bill_amount' => $billAmount,
            'tip_amount' => $tipAmount,
            'has_tip' => $tipAmount > 0,
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
            'fee_share' => $feeShare,
            'exact_payable' => $exactPayable,
            'surplus' => $tipAmount,
        ];
    }
}
