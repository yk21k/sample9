<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeleteShop extends Model
{
    use HasFactory;

    public function deleteShop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function deleteShopp()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
