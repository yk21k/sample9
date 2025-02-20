<?php

namespace App\Http\Controllers\Seller;

use App\Models\Order;
use App\Models\Product;
use App\Models\SubOrder;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Mails;
use App\Models\ShopCoupon;
use App\Models\Shop;
use App\Models\Campaign;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrdersController extends Controller
{
    public function index(SubOrder $suborder)
    {
        
        $orders = SubOrder::where('seller_id', auth()->id())->latest()->paginate(15);

        $coupons = ShopCoupon::where('shop_id', auth()->user()->shop->id)->get()->toArray();

        $campaigns = Campaign::where('shop_id', auth()->user()->shop->id)->get()->toArray();
        // dd(isset($campaigns), isset($coupons), empty($campaigns), empty($coupons), is_null($campaigns), is_null($coupons));
        // dd($campaigns);

        

        return view('sellers.orders.index', compact(['orders', 'coupons', 'campaigns']));

    }

    public function show(SubOrder $order)
    {
        $items = $order->items;
        // dd($items);
        return view('sellers.orders.show', compact('items'));
    }

    public function markAccepted(SubOrder $suborder)
    {
        $suborder->payment_status = '';
        $suborder->payment_status = 'accepted';
        $suborder->save();

        
        $suborder->order()->update(['payment_status'=>'accepted']);
        

        return redirect('/seller/orders')->withMessage('Order marked Accepted　(Pending)');    
    }

    public function markDeliveryCom(SubOrder $suborder, Request $request)
    {
        $shipCom = $request->input('shipping_company');
        $shipInvoice = $request->input('invoice_number');

        $suborder->payment_status ='';
        $suborder->payment_status = "arranging delivery";
        
        $suborder->shipping_company = $shipCom;
        $suborder->invoice_number = $shipInvoice;
        $suborder->save();

        return redirect('/seller/orders')->withMessage('Order marked Arranging Delivery (Pending)');    
    }

    public function markArranged(SubOrder $suborder)
    {
        $suborder->payment_status ='';
        $suborder->status ='';
        $suborder->status = 'processing';
        $suborder->payment_status = "delivery arranged";
        
        $suborder->save();

        $suborder->order()->update(['status'=>'processing']);
        $suborder->order()->update(['payment_status'=>'delivery arranged']);
        

        return redirect('/seller/orders')->withMessage('Order marked Delivery Arranged (Processing)');    
    }

    public function markDelivered(SubOrder $suborder)
    {
        $suborder->payment_status ='';
        $suborder->status ='';
        $suborder->status = 'completed';
        $suborder->save();
        
        $suborder->order()->update(['status'=>'completed']);
        

        return redirect('/seller/orders')->withMessage('Order marked Complete');
    }

    public function sendMail(Request $request)
    {
        $data = $request->all();
        // dd($data);
        // dd(empty($data['coupon_id']));

        $user = User::where('id', $data['user_id'])->first();
        // dd($user->email);

        $mailDisplay = Mails::where('shop_id', auth()->id())->where('user_id', $data['user_id'])->where('template', "template1")->orWhere('template', "template3")->first();

        // $mailDisplay = Mails::where('shop_id', auth()->id())->where('user_id', $data['user_id'])->first();

        // dd($mailDisplay->template, $data['template']);

        // dd(($mailDisplay->template == 'template3') && ($data['template'] === 'template1'));

        if(($mailDisplay->template == 'template1') && ($data['template'] == 'template3')){

            return redirect('/seller/orders')->withMessage('クーポンを送った方には、このサイトからレビューを依頼できません!!'); 

        }else if(($mailDisplay->template == 'template3') && ($data['template'] == 'template1')){

            return redirect('/seller/orders')->withMessage('レビューを依頼した方には、このサイトからクーポンを送ることはできません!!');

        }else{
        
            $mail = new Mails;
            $mail->user_id = $data['user_id'];
            $mail->shop_id = $data['shop_id'];
            $mail->mail = $user->email;
            $mail->template = $data['template'];
            if(empty($data['coupon_id'])){
                $mail->coupon_id = " "; 
            }else{
                $mail->coupon_id = $data['coupon_id'];    
            }
            if(empty($data['campaign_id'])){
                $mail->campaign_id = " "; 
            }else{
                $mail->campaign_id = $data['campaign_id'];    
            }
            
            $mail->save(); 

            return redirect('/seller/orders')->withMessage('Mail sent!!');  
        }

    }

    public function chartPage()
    {
        return view('sellers.charts.index');

    }

}