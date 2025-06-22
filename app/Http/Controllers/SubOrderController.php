<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubOrder;
use App\Models\Commition;
use App\Models\SubOrdersArrivalReport;



class SubOrderController extends Controller
{
    public function pay(SubOrder $suborder)
    {

        $commition = Commition::first();
        
        // dd($commition->rate, $commition->fixed);
        $suborder->transactions()->create([
            'transaction_id'=> uniqid('trans-'.$suborder->id),
            'amount_paid'=> $suborder->grand_total,
            'commission'=>  (int) floor($commition->fixed+$commition->rate*$suborder->grand_total),

        ]);

        $report = SubOrdersArrivalReport::where('sub_order_id', $suborder->id)->first();

        if ($report && !$report->payment_clicked_at) {
            $report->update(['payment_clicked_at' => now()]);
        }

        return redirect()->to('/admin/transactions')->withMessage('Transaction Created');
    }


}
