<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubOrder;
use App\Models\Commition;



class SubOrderController extends Controller
{
    public function pay(SubOrder $suborder)
    {
        $commition = Commition::first();
        
        // dd($commition->rate, $commition->fixed);
        $suborder->transactions()->create([
            'transaction_id'=> uniqid('trans-'.$suborder->id),
            'amount_paid'=> $suborder->grand_total,
            'commission'=>  $commition->rate*$suborder->grand_total+$commition->fixed
        ]);

        return redirect()->to('/admin/transactions')->withMessage('Transaction Created');
    }


}
