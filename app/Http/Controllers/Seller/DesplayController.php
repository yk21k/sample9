<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Desplay;
use Auth;
use Carbon\Carbon;

class DesplayController extends Controller
{
    //
    public function index()
    {
        

        $user = auth()->user();
        if ($user && $user->shop) {
            // shop_idが存在する場合に処理を進める
            $shopId = $user->shop->id;
            $check_desplay = Desplay::where('shop_id', $shopId)->latest()->first();
            $check_desplay_count = Desplay::where('shop_id', auth()->user()->shop->id)->get()->count();
            // dd($check_desplay->is_active);
            

            
            if(is_null($check_desplay)){
                // dd('Hello');
                return view('sellers.desplay.select_display', ["check_desplay_count" => 0, 'check_desplay' => $check_desplay]);    
            } 
            
            
            if($check_desplay_count == 0){
                return view('sellers.desplay.select_display', ["check_desplay_count" => 0, 'check_desplay' => $check_desplay]);
            }
            else{
                $activeDate = $check_desplay->updated_at; 
                // Carbonを使って2週間後の日付を取得
                $twoWeeksLater = Carbon::parse($activeDate)->addWeeks(2);

                return view('sellers.desplay.select_display', ["check_desplay_count" => $check_desplay_count, 'check_desplay' => $check_desplay, 'twoWeeksLater' => $twoWeeksLater]);
            }
        }
        else {
        // shopが存在しない場合の処理（例えばリダイレクトやエラーメッセージを表示）
        return redirect()->route('home')->with('error', 'Shop not found for this user.');
        }
 
    }

    public function saveSelect(Request $request)
    {
        $data = $request->all();
        // dd($data);

        $rules = [

            'desplay_id' => 'required',
            'shop_name' => 'required|alpha|between:1, 30',
            'display_shop_name' => 'required|between:1, 30',
        
            'desplay_area' => 'string|max:255',

            'desplay_real_store1' => 'required|string|max:100',
            'desplay_real_address1' => 'required|string|max:255',

            'desplay_real_store2' => 'required|string|max:100',
            'desplay_real_address2' => 'required|string|max:255',

            'desplay_real_store3' => 'required|string|max:100',
            'desplay_real_address3' => 'required|string|max:255',

            'name1' => 'required|string|max:100',
            'photo1' => 'required|mimes:jpg,jpeg,png',
            'description1' => 'required|string|max:255',
            'name2' => 'required|string|max:100',
            'photo2' => 'required|mimes:jpg,jpeg,png',
            'description2' => 'required|string|max:255',
            'name3' => 'required|string|max:100',
            'photo3' => 'required|mimes:jpg,jpeg,png',
            'description3' => 'required|string|max:255',
            'desplay_pr' => 'required|string|max:500',

        ];

        $customMessages = [
            'desplay_id' => 'Please Select Template',
            'shop_name.required' => 'Shop Name is required',
            'display_shop_name.required' => 'Display Shop Name is required',

            'desplay_real_store1.required' => 'Store name of store 1　is required',
            'desplay_real_address1' => 'Store 1 location　is required',

            'desplay_real_store2.required' => 'Store name of store 2　is required',
            'desplay_real_address2' => 'Store 2 location　is required',

            'desplay_real_store3.required' => 'Store name of store 3　is required',
            'desplay_real_address3' => 'Store 3 location　is required',

        ];

        $this->validate($request, $rules, $customMessages);


        $selectDesplay = new Desplay;

        // name属性が'photo1'のinputタグをファイル形式に、画像をpublic/imgに保存→別の時にimgフォルダ名変更
        $image_path1 = $request->file('photo1')->store('public/img/'.$request->shop_name.'/');
        $image_path2 = $request->file('photo2')->store('public/img/'.$request->shop_name.'/');
        $image_path3 = $request->file('photo3')->store('public/img/'.$request->shop_name.'/');

        

        $selectDesplay->shop_id = auth()->user()->shop->id;
        $selectDesplay->desplay_id = $request->desplay_id;
        $selectDesplay->shop_name = $request->shop_name;
        $selectDesplay->display_shop_name = $request->display_shop_name;
        $selectDesplay->shop_address = $request->shop_address;

        $selectDesplay->desplay_area = $request->desplay_area;
        $selectDesplay->desplay_real_store1 = $request->desplay_real_store1;
        $selectDesplay->desplay_real_address1 = $request->desplay_real_address1;
        $selectDesplay->desplay_real_store2 = $request->desplay_real_store2;
        $selectDesplay->desplay_real_address2 = $request->desplay_real_address2;
        $selectDesplay->desplay_real_store3 = $request->desplay_real_store3;
        $selectDesplay->desplay_real_address3 = $request->desplay_real_address3;

        $selectDesplay->name1 = $request->name1;

        // 上記処理にて保存した画像に名前を付け、desplaysテーブルのphoto1カラムに、格納
        $selectDesplay->photo1 = basename($image_path1);

        $selectDesplay->description1 = $request->description1;

        $selectDesplay->name2 = $request->name2;

        // 上記処理にて保存した画像に名前を付け、desplaysテーブルのphoto2カラムに、格納
        $selectDesplay->photo2 = basename($image_path2);

        $selectDesplay->description2 = $request->description2;

        $selectDesplay->name3 = $request->name3;

        // 上記処理にて保存した画像に名前を付け、desplaysテーブルのphoto3カラムに、格納
        $selectDesplay->photo3 = basename($image_path3);
        $selectDesplay->description3 = $request->description3;

        $selectDesplay->desplay_pr = $request->desplay_pr;
        $selectDesplay->seller_mail = $request->seller_mail;
        // dd($selectDesplay);
        $selectDesplay->save();

        return back()->with('Thank you!!');

    }

    public function deleteSelect()
    {

        $delpath = Desplay::where('shop_id', auth()->user()->shop->id)->first();
        
        $delpath_name = $delpath->shop_name;

        $path = public_path();
        // dd($path);
        // dd($delpath_name);
        
        @unlink($path. '/' . 'statics/shop_desplay_page'. $delpath_name .'.html');
        Desplay::where('shop_id', auth()->user()->shop->id)->delete();
        

        return back()->with('Shop page has been deselected.');
    }

}
