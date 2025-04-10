<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    
    public function forMails()
    {
        return $this->hasMany(Mails::class, 'coupon_id');
    }
}
