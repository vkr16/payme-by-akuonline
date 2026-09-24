<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bill_id', 'bank_name', 'account_number', 'account_holder'])]
class BillBank extends Model
{
    use HasFactory;

    /**
     * Get the bill that owns this bank snapshot.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
