<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubOrdersArrivalReport extends Model
{
    protected $fillable = [
        'sub_order_id',
        'confirmation_deadline',
        'confirmed_at',
        'arrival_reported',
        'comments',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            $report->confirmation_deadline = now()->addDays(7);
        });
    }

    public function subOrder()
    {
        return $this->belongsTo(SubOrder::class);
    }
}

