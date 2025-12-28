<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LandingSectionVariant;

class LandingSectionVariantController extends Controller
{
    public function firstSection(Request $request)
    {
        $seller = LandingSectionVariant::active('seller')->first();
        $buyer  = LandingSectionVariant::active('buyer')->first();

        $type = $request->cookie('entrance_type'); // seller / buyer

        return view('top.firstSection', [
            'publicType' => $type,
            'seller' => $seller,
            'buyer' => $buyer,
        ]);
    }



    public function pass(Request $request)
    {
        $type = $request->input('type'); // seller / buyer / null

        return redirect('/')
            ->withCookie(cookie('passed_entrance', true, 60 * 24 * 30))
            ->withCookie(cookie('entrance_type', $type, 60 * 24 * 30));
    }

}
