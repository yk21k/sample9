<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mails extends Model
{
    use HasFactory;

    public function forMailUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function forMailCoupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function forMailCampaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
