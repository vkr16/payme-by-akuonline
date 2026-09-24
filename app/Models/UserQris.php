<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'merchant_name', 'merchant_city', 'payload', 'image_path', 'is_default'])]
class UserQris extends Model
{
    use HasFactory;

    protected $table = 'user_qris';

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
     * Get the user that owns this QRIS.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
