<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'type',
        'status',
        'before_data',
        'after_data',
        'reviewer_id',
        'reviewed_at',
        'reject_reason',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function logs()
    {
        return $this->hasMany(ShopApplicationLog::class);
    }

}
