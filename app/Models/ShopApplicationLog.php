<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopApplicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_application_id',
        'user_id',
        'action',
        'comment',
    ];

    public function application()
    {
        return $this->belongsTo(ShopApplication::class, 'shop_application_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
