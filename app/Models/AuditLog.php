<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [

        'user_id',
        'shop_id',

        'product_id',
        'draft_id',

        'target_type',
        'target_id',

        'event_group',

        'action',

        'before_data',
        'after_data',
        'diff_data',

        'description',

        'role',

        'ip',
        'user_agent',

    ];

    protected $casts = [

        'before_data' => 'array',

        'after_data' => 'array',

        'diff_data' => 'array',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function draft()
    {
        return $this->belongsTo(
            ProductDraft::class,
            'draft_id'
        );
    }

}