<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SubOrder;
use App\Models\SubOrderItem;
use App\Models\Fovorite;
use App\Models\DeliveryAddress;
use App\Models\Product;
use App\Models\SubOrdersArrivalReport;
use App\Models\Auction;
use App\Models\AuctionOrder;
use App\Models\PickupOrder;


use Auth;


class AccountController extends Controller
{
    public function index()
    {
        $profiles = User::where('id', Auth::user()->id)->first();

        $order_histories = SubOrder::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        // dd($order_histories->subOrders);

        $subOrderIds = $order_histories->pluck('id');

        $itemsGrouped = \App\Models\SubOrderItem::whereIn('sub_order_id', $subOrderIds)->get()->groupBy('sub_order_id');


        $shipping_names = Order::where('user_id', Auth::user()->id)->get();

        // SubOrder 一覧を取得
        $sub_order = SubOrder::where('user_id', Auth::id())->get();

        // SubOrder の ID 一覧を取得
        $sub_orderIds = $sub_order->pluck('id');

        // SubOrderItem を一括取得
        $subOrder_items = SubOrderItem::whereIn('sub_order_id', $sub_orderIds)->first();
            
        
        $firstDelis = Order::where('user_id', Auth::user()->id)->latest()->first();

        $savedDelis = DeliveryAddress::where('user_id', Auth::user()->id)->get();
        // dd($savedDelis);

        $userId = Auth::user();

        // $favaoriteItems = Product::with('user_favo')->find($userId);
        $favaoriteItems = Product::with('user_favo')->get();

        $auction_order = Auction::where('winner_user_id', auth()->user()->id)->get();
        // dd($auction_order);
        // dd($favaoriteItems);
        
        // $favaoriteItems = Fovorite::where('user_id', Auth::user()->id)->get();
        // dd($favaoriteItems);

        // $arrival = SubOrdersArrivalReport::where('sub_order_id', )->first();



        return view('account.account', compact('profiles', 'order_histories', 'firstDelis', 'savedDelis', 'shipping_names', 'favaoriteItems', 'subOrder_items', 'itemsGrouped', 'auction_order'));
    }

    public function updateProf(Request $request, $id)
    {

        if($request->isMethod('post')){

            $data = $request->all();

            $rules = [
                'name' => 'required|between:1, 30|unique:users,name,'. $id,
                'email' => 'required|string|email|max:255|unique:users,email,'. $id,
                // 'email' => 'required|string|email:strict,dns,spoof|max:255',When user register
                'password' => 'required|between:1, 30'
            ];

            $customMessages = [
                'name.required' => 'Name is required',
                'name.unique' => 'This Name is currently unavailable',
                'email.required' => 'Email is required',
                'email.unique' => 'This Email is currently unavailable',
                'password.required' => 'Password is required',
            ];

            $this->validate($request, $rules, $customMessages);
            User::where('id', $id)->update(['name'=>$data['name'], 'email'=>$data['email'], 'password'=>Hash::make($data['password'])]);
        }
        return redirect()->route('account.account', ['id', $id])->withMessage('Update Profile!!');

    }

    public function saveDeliveryAddress(Request $request, $id)
    {
        if($request->isMethod('post')){
            $data = $request->all();

            $rules = [
                'shipping_fullname' => 'required|between:2, 30',
                'shipping_zipcode' => 'required|numeric',
                'shipping_phone' =>  'required|numeric|digits:11'  
            ];

            $customMessages = [
                'shipping_fullname' => 'Name is required.',
                'shipping_zipcode' => 'Zipcode is required. Please enter your postal code within Japan',
                'shipping_address' => 'Address is required. Please check from the postal code',
                'shipping_phone' => 'Mobile is required'
            ];

            $this->validate($request, $rules, $customMessages);

            $deliveryAddress = new DeliveryAddress;
            $deliveryAddress->user_id = Auth::user()->id;
            $deliveryAddress->shipping_fullname = $data['shipping_fullname'];
            $deliveryAddress->shipping_address = $data['shipping_address'];
            $deliveryAddress->shipping_city = $data['shipping_city'];
            $deliveryAddress->shipping_state = $data['shipping_state'];
            $deliveryAddress->shipping_zipcode = $data['shipping_zipcode'];
            $deliveryAddress->shipping_phone = $data['shipping_phone'];
            $deliveryAddress->status = 0;


            $deliveryAddress->save();
        }

        return redirect()->route('account.account', ['id', $id])->withMessage('registered a new address');

    }

    public function update(Request $request, $id)
    {   
        // dd($id);
        $validated = $request->validate([
            'shipping_fullname' => 'required|string|max:255',
            'shipping_zipcode' => 'required|digits:7',
            'shipping_state' => 'required|string',
            'shipping_city' => 'required|string',
            'shipping_address' => 'required|string',
            'shipping_phone' => 'required|string',
        ]);

        $deliUser = DeliveryAddress::where('user_id', Auth::user()->id)->latest()->first();
        // dd($deliUser);
        if ($deliUser) {
            $deliUser->update($validated);
        }    
        
        return redirect()->route('account.account', ['id', Auth::user()->id])->withMessage('配送先を更新しました。');

    }

    public function arrival(Request $request, $id)
    {
        if($request->isMethod('post'))
        {
            $data = $request->all();
            // dd($data); 
            $rules = [
                'comments'  =>  'max:100' 
            ]; 

            $customMessages = [
                'comments' => '100文字以内で入力ください。'    
            ];

            $this->validate($request, $rules, $customMessages);

                // 押した日時をサーバー時間でセット
            $data['confirmed_at'] = now();
            
            SubOrdersArrivalReport::updateOrCreate(
                ['sub_order_id' => $data['sub_order_id']], // 検索条件
                [
                    'arrival_reported' => $data['arrival_reported'],
                    'comments' => $data['comments'],
                    'confirmed_at' => $data['confirmed_at'],
                ] // 更新または新規作成する値
            );
        }
        return redirect()->route('account.account', ['id', $id])->withMessage('到着したと確認しました。');

    }


      
}        
