<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionOrder extends Model
{
    use HasFactory;

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }
}
