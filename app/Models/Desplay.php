<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Observers\DesplayObserver;
use Illuminate\Support\Facades\Mail;
use App\Mail\ShopTemplateActivationRequest;

class Desplay extends Model
{
    use HasFactory;

    protected $fillable=['photo1', 'photo2', 'photo2'];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    // Accessor
    public function getStaticUriAttribute() {

        // 静的HTMLファイルのURI
        return '/statics/shop_desplay_page'. $this->shop_name .'.html';

    }

    // Others
    public function generate_static() {

        if($this->desplay_id == 1)
        {
            // 静的HTMLのデータを作成
            $path = public_path($this->uri);
            // dd($path);
            $html = view('shop_site.shop_site_1')
                ->with('desplay', $this)
                ->render(); 
        }
        elseif($this->desplay_id == 2){
            // 静的HTMLのデータを作成
            $path = public_path($this->uri);
            // dd($path);
            $html = view('shop_site.mini_auction_test')
                ->with('desplay', $this)
                ->render(); 
        }

        // 静的HTMLのデータを作成
        // $path = public_path($this->uri);
        // // dd($path);
        // $html = view('shop_site.shop_site_1')
        //     ->with('desplay', $this)
        //     ->render();

        return file_put_contents($path. '/' . 'statics/shop_desplay_page'. $this->shop_name .'.html', $html);

    }

    public function generate_static2() {
        
        //send mail to admin
        $admins = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->get();
        // dd($admins);
        Mail::to($admins)->send(new ShopTemplateActivationRequest($this));
    }

    // public function generate_static3() {
    //     // 静的HTMLのデータを作成
    //     $path = public_path($this->uri);
    //     // dd($path);
    //     $html = view('shop_site.mini_auction_test')
    //         ->with('desplay', $this)
    //         ->render();

    //     if($shop->getOriginal('is_active') == false && $shop->is_active == true){
    //         //dd('shop made active');
    //         // send mail to customer 

    //         Mail::to($shop->owner)->send(new ShopActivated($shop));

    //         // change role from customer to seller
    //         $shop->owner->setRole('seller');
    //     }else{
    //     //     dd('shop changed to inactive');
    //     }       
             
    //     return file_put_contents($path. '/' . 'statics/shop_desplay_page'. $this->shop_name .'.html', $html);    

    // }



}
