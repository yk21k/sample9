<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class DeliveryAddress extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'shipping_fullname',
        'shipping_address',
        'shipping_city',
        'shipping_city',
        'shipping_state',
        'shipping_zipcode',
        'shipping_phone',
        'status'
    ];

    public static function setDeliPlaces(){
        // $setDeliPlaces = DeliveryAddress::where('user_id', Auth::user()->id)->where('status', 1)->get();
        $setDeliPlaces = DeliveryAddress::where('user_id', Auth::user()->id)->get();
        return  $setDeliPlaces;
    }
}
