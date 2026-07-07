<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'shop_id',

        'product_id',

        'draft_id',

        'action',

        'target_type',

        'target_id',

        'changes',

        'role',

        'ip',

        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
