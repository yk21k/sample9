<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use Illuminate\Http\Request;

use App\Models\Shop;
use App\Models\User;

use App\Mail\ShopActivationRequest;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shops.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopRequest $request)
    {   
        //add validation
        $request->validate([
            'name' => 'required|between:1, 30|unique:shops,name',
            // 'email' => 'required|string|email:strict,dns,spoof|max:255',When user register
            'email' => 'required|string|email|max:255',
            'telephone' => 'required|regex:/^0[0-9]{1,4}-[0-9]{1,4}-[0-9]{3,4}\z/',
            'description' => 'required|string|max:2000',
            'manager' => 'required|between:1, 100|unique:shops,manager',
            'representative' => 'required|between:1, 100',
            'product_type' => 'required',
            'volume' => 'required|numeric|min:1',
            'note' => 'max:2000',

        ]);

        // $shop = new Shop; 
         // dd($shop);

        $imgName = auth()->user()->email;


        if($request->hasFile('photo_1')){
            $images = $request->file('photo_1');

                $file_name = rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('photo_1')->getClientOriginalName();
                $request->file('photo_1')->storeAs('public', $file_name);
            
            
        }else{
            $request->photo_1 = "Not uploaded";
        }
        if($request->hasFile('photo_2')){

                $file_name = rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('photo_2')->getClientOriginalName();
                $request->file('photo_2')->storeAs('public', $file_name);
                

        }else{
            $request->photo_2 = "Not uploaded";
        }
        if($request->hasFile('photo_3')){

                $file_name = rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('photo_3')->getClientOriginalName();
                $request->file('photo_3')->storeAs('public', $file_name);
               

        }else{
            $request->photo_3 = "Not uploaded";
        }
        if($request->hasFile('file_1')){

                $file_name = rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('file_1')->getClientOriginalName();
                $request->file('file_1')->storeAs('public', $file_name);
            

        }else{
            $request->file_1 = "Not uploaded";
        }
        if($request->hasFile('file_2')){
            $images = $request->file('file_2');

                $file_name = rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('file_2')->getClientOriginalName();
                $request->file('file_2')->storeAs('public', $file_name);
            

        }else{
            $request->file_2 = "Not uploaded";
        }
        if($request->hasFile('file_3')){

                $file_name = rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('file_3')->getClientOriginalName();
                $request->file('file_3')->storeAs('public', $file_name);
            
             

        }else{
            $request->file_3 = "Not uploaded";
        }
        if($request->hasFile('file_4')){

                $file_name = rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('file_4')->getClientOriginalName();
                $request->file('file_4')->storeAs('public', $file_name);

        }else{
            $request->file_4 = "Not uploaded";
        }

         // dd($request->photo_2);
         // dd($shop);
         // dd($file_name2);

        //save db
        $shop = auth()->user()->shop()->create([
            'name'        => $request->input('name'),
            'description' => $request->input('description'),

            'location_1' => $request->input('location_1'),
            'location_2' => $request->input('location_2'),

            'telephone' => $request->input('telephone'),
            'email' => $request->input('email'),

            'identification_1' => $request->input('identification_1'),
            'identification_2' => $request->input('identification_2'),
            'identification_3' => $request->input('identification_3'),

            'photo_1' => rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('photo_1')->getClientOriginalName(),
            'photo_2' => rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('photo_2')->getClientOriginalName(),
            'photo_3' => rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('photo_3')->getClientOriginalName(),
            'file_1' => rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('file_1')->getClientOriginalName(),
            'file_2' => rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('file_2')->getClientOriginalName(),
            'file_3' => rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('file_3')->getClientOriginalName(),
            'file_4' => rand(1111,9999999).auth()->user()->email.rand(1111,9999999).$request->file('file_4')->getClientOriginalName(),

            'representative' => $request->input('representative'),
            'manager' => $request->input('manager'),
            'product_type' => $request->input('product_type'),
            'volume' => $request->input('volume'),
            'note' => $request->input('note'),
        ]);

        // dd($shop);

        // dd($request->all());
        // dd($shop);
        // dd($shop->representative);
        // dd(auth()->user()->email);

        $shop->save();
        // dd($shop);

        //send mail to admin
        $admins = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->get();

        Mail::to($admins)->send(new ShopActivationRequest($shop));

        return redirect()->route('home')->withMessage('Create shop request sent');
            
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop)
    {
        dd($shop->owner->name. ' welcome to your shop named', $shop->name);
        // Precautions　Terms of use again　Links to contracts　Others　Page
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopRequest $request, Shop $shop)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop)
    {
        //
    }
}
