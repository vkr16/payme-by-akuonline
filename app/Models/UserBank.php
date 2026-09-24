<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'bank_name', 'account_number', 'account_holder', 'is_default'])]
class UserBank extends Model
{
    use HasFactory;

    protected $table = 'user_banks';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the user that owns this bank account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
