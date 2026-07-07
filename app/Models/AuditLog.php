<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [

        'user_id',

        'shop_id',

        'role',

        'product_id',

        'draft_id',

        'action',

        'target_type',

        'target_id',

        'before_data',

        'after_data',

        'description',

        'ip',

        'user_agent',

        'product_id',

        'draft_id',

        'order_id',

        'event_group',

    ];

    protected $casts = [

        'before_data' => 'array',

        'after_data' => 'array',

        'diff_data' => 'array',

    ];
}
