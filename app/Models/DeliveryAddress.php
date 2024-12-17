<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class DeliveryAddress extends Model
{
    use HasFactory;
    protected $fillable = ['status'];

    public static function setDeliPlaces(){
        $setDeliPlaces = DeliveryAddress::where('user_id', Auth::user()->id)->where('status', 1)->get();
        return  $setDeliPlaces;
    }
}
