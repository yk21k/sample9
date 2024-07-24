<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInquiry extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'inq_subject', 'inquiry_details', 'ans_subject', 'answers'
    ];

    public function user_id(){
        return $this->belongsTo('App\Models\User');
    }

    public function inqUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
