<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use App\Models\Order;  // Order モデルをインポート

class PaymentController extends Controller
{


    // 支払い成功ページの表示
    public function success()
    {
        return view('stripe.success');  // success.blade.phpというビューを作成
    }

    
}

