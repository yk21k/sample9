<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiries extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'shop_id', 'inq_subject', 'inquiry_details', 'ans_subject', 'answers'
    ];

    public function inqUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shopAd()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}
