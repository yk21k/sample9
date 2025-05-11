<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;
    
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'category_auction', 'auction_id', 'category_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

}
