<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone_number', 'password', 'avatar', 'is_active', 'last_login_at', 'roles'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 1;

    public const ROLE_USER = 2;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'integer',
        ];
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return (int) $this->roles === self::ROLE_ADMIN;
    }

    /**
     * Check if the user is a regular user.
     */
    public function isUser(): bool
    {
        return (int) $this->roles === self::ROLE_USER;
    }

    /**
     * Get all QRIS records owned by the user.
     */
    public function qris(): HasMany
    {
        return $this->hasMany(UserQris::class);
    }

    /**
     * Get the default QRIS record.
     */
    public function defaultQris(): HasOne
    {
        return $this->hasOne(UserQris::class)->where('is_default', true);
    }

    /**
     * Get all Bank accounts owned by the user.
     */
    public function banks(): HasMany
    {
        return $this->hasMany(UserBank::class);
    }

    /**
     * Get the default Bank account.
     */
    public function defaultBank(): HasOne
    {
        return $this->hasOne(UserBank::class)->where('is_default', true);
    }

    /**
     * Get all Bills created by the user.
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }
}
