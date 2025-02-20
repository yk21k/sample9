<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SubOrder;
use App\Models\Fovorite;
use App\Models\DeliveryAddress;
use App\Models\Product;
use Auth;


class AccountController extends Controller
{
    public function index()
    {
        $profiles = User::where('id', Auth::user()->id)->first();

        $order_histories = SubOrder::where('user_id', Auth::user()->id)->get();
        // dd($order_histories->subOrders);
        $shipping_names = Order::where('user_id', Auth::user()->id)->get();
        
        $firstDelis = Order::where('user_id', Auth::user()->id)->latest()->first();

        $savedDelis = DeliveryAddress::where('user_id', Auth::user()->id)->get();
        // dd($savedDelis);

        $userId = Auth::user();

        // $favaoriteItems = Product::with('user_favo')->find($userId);
        $favaoriteItems = Product::with('user_favo')->get();

        // dd($favaoriteItems);
        
        // $favaoriteItems = Fovorite::where('user_id', Auth::user()->id)->get();
        // dd($favaoriteItems);


        return view('account.account', compact('profiles', 'order_histories', 'firstDelis', 'savedDelis', 'shipping_names', 'favaoriteItems'));
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



       
}        
