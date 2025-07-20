<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    // ユーザーとのリレーションを定義
    public function user()
    {
        return $this->belongsTo(User::class); // Userモデルと1対多の関係
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
    
}
