<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteLog extends Model
{
    protected $fillable = [
        'shop_member_id',
        'email',
        'token',
        'status',
        'sent_at',
    ];
}
