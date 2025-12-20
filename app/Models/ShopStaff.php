<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ShopStaff extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'shop_id',
        'active_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'active_until' => 'datetime',
    ];

    // 有効期限チェック
    public function isActive(): bool
    {
        return $this->active_until && $this->active_until->isFuture();
    }
}
