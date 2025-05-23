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

    // store メソッド
    public function store(StoreShopRequest $request)
    {
        // dd($request->input('person_3'));
        // $data = $request->all();
        // dd($data);

        // 本日の日付フォルダ（例：20250420）
        $dateFolder = now()->format('Ymd');
        $userEmail = auth()->user()->email;

        // 保存先パス：storage/app/public/20250420/user@example.com/
        $basePath = "public/{$dateFolder}/{$userEmail}";

        // ファイル名生成関数（rand + email + rand + original name）
        $generateFileName = fn($file) =>
            rand(1111, 9999999) . $userEmail . rand(1111, 9999999) . $file->getClientOriginalName();

        // ファイル保存（共通化するときはループでも可）

        $photo_1_name = 'no_file.txt';
        $photo_2_name = 'no_file.txt';
        $photo_3_name = 'no_file.txt';
        $photo_4_name = 'no_file.txt';
        $photo_5_name = 'no_file.txt';
        $photo_6_name = 'no_file.txt';
        $photo_7_name = 'no_file.txt';
        $file_1_name  = 'no_file.txt';
        $file_2_name  = 'no_file.txt';
        $file_3_name  = 'no_file.txt';
        $file_4_name  = 'no_file.txt';

        if ($request->hasFile('photo_1')) {
            $photo = $request->file('photo_1');
            $photo_1_name = $generateFileName($photo);
            $photo->storeAs($basePath, $photo_1_name);
        }

        if ($request->hasFile('photo_2')) {
            $photo = $request->file('photo_2');
            $photo_2_name = $generateFileName($photo);
            $photo->storeAs($basePath, $photo_2_name);
        }

        if ($request->hasFile('photo_3')) {
            $photo = $request->file('photo_3');
            $photo_3_name = $generateFileName($photo);
            $photo->storeAs($basePath, $photo_3_name);
        }

        if ($request->hasFile('photo_4')) {
            $photo = $request->file('photo_4');
            $photo_4_name = $generateFileName($photo);
            $photo->storeAs($basePath, $photo_4_name);
        }

        if ($request->hasFile('photo_5')) {
            $photo = $request->file('photo_5');
            $photo_5_name = $generateFileName($photo);
            $photo->storeAs($basePath, $photo_5_name);
        }

        if ($request->hasFile('photo_6')) {
            $photo = $request->file('photo_6');
            $photo_6_name = $generateFileName($photo);
            $photo->storeAs($basePath, $photo_6_name);
        }

        if ($request->hasFile('photo_7')) {
            $photo = $request->file('photo_7');
            $photo_7_name = $generateFileName($photo);
            $photo->storeAs($basePath, $photo_7_name);
        }                 

        if ($request->hasFile('file_1')) {
            $file = $request->file('file_1');
            $file_1_name = $generateFileName($file);
            $file->storeAs($basePath, $file_1_name);
        }

        if ($request->hasFile('file_2')) {
            $file = $request->file('file_2');
            $file_2_name = $generateFileName($file);
            $file->storeAs($basePath, $file_2_name);
        }

        if ($request->hasFile('file_3')) {
            $file = $request->file('file_3');
            $file_3_name = $generateFileName($file);
            $file->storeAs($basePath, $file_3_name);
        }

        if ($request->hasFile('file_4')) {
            $file = $request->file('file_4');
            $file_4_name = $generateFileName($file);
            $file->storeAs($basePath, $file_4_name);
        }


        // identification の片方だけを選択
        $registrationType = $request->registration_type;

        // identification_1 関連のデフォルト値
        $identification_1 = null;
        $file_1_name = null;
        $license_expiry = null;

        // 条件: 個人 or 個人事業主 の場合のみ格納
        if (in_array($registrationType, ['個人', '個人事業主'])) {
            $identification_1 = $request->input('identification_1');
            $license_expiry = $request->input('license_expiry');

            if ($request->hasFile('file_1')) {
                $file_1_name = $generateFileName($request->file('file_1'));
                $request->file('file_1')->storeAs($basePath, $file_1_name);
            }
        }

        $identification_2 = null;
        if ($registrationType === '個人事業主') {
            $identification_2 = $request->input('identification_2_1');
        } elseif (in_array($registrationType, ['法人', '業務請負'])) {
            $identification_2 = $request->input('identification_2_2');
        }

        // Shop 作成
        $shop = auth()->user()->shop()->create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'location_1' => $request->input('location_1'),
            'location_2' => $request->input('location_2'),
            'telephone' => $request->input('telephone'),
            'email' => $request->input('email'),
            'person_1' => $request->input('person_1'),
            'person_2' => $request->input('person_2'),
            'person_3' => $request->input('person_3'),
            'license_expiry' => $request->input('license_expiry'),
            
            'representative' => $request->input('representative'),
            'manager' => $request->input('manager'),
            'product_type' => $request->input('product_type'),
            'volume' => $request->input('volume'),
            'note' => $request->input('note'),

            'identification_1' => $request->input('identification_1'),
            'identification_2' => $identification_2, // ここで片方だけ保存

            'photo_1' => $photo_1_name,
            'photo_2' => $photo_2_name,
            'photo_3' => $photo_3_name,
            'photo_4' => $photo_4_name,
            'photo_5' => $photo_5_name,
            'photo_6' => $photo_6_name,
            'photo_7' => $photo_7_name,
            'file_1' => $file_1_name,
            'file_2' => $file_2_name,
            'file_3' => $file_3_name,
            'file_4' => $file_4_name ?? 'no_file.txt',
        ]);

        // 管理者へメール通知
        $admins = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->get();

        Mail::to($admins)->send(new ShopActivationRequest($shop));

        return redirect()->route('home')->withMessage('Create shop request sent');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {   
        $parts = Shop::find($id);
        // dd($parts);
        return view('shops.overview', compact('parts'));
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
