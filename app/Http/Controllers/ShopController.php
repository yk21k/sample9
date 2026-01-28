<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use Illuminate\Http\Request;

use App\Models\Shop;
use App\Models\User;
use App\Models\ChecklistItem;

use App\Mail\ShopActivationRequest;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;

use Auth;
use Illuminate\Support\Facades\Storage;

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
        $shop_sets = Shop::where('user_id', Auth::user()->id)->first();
        // dd($shop_sets);

        // ショップ未作成時
        $shop_sets = null;

        // Voyager 管理画面と完全同期
        $help = ChecklistItem::get()->keyBy('name'); // ← ★ここが超重要

        return view('shops.create', compact('shop_sets', 'help'));
    }

    /**
     * Store a newly created resource in storage.
     */

    // store メソッド　validatorは、Requestにある。

    public function store(StoreShopRequest $request)
    {

        $isDraft = $request->input('save_type') === 'draft';
        $dateFolder = now()->format('Ymd');
        $userEmail = auth()->user()->email;

        // 保存先を private ストレージに変更
        $basePath = "licenses/{$dateFolder}/{$userEmail}";


        // ディレクトリがなければ作成
        Storage::makeDirectory($basePath);

        // ファイル名生成関数
        $generateFileName = fn($file) =>
            rand(1111, 9999999) . $userEmail . rand(1111, 9999999) . $file->getClientOriginalName();

        // 初期値
        $photoNames = array_fill(1, 7, null); // photo_1〜photo_7
        $fileNames  = array_fill(1, 4, null); // file_1〜file_4

        // 写真アップロード
        for ($i = 1; $i <= 7; $i++) {
            $key = "photo_{$i}";
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $photoNames[$i] = $generateFileName($file);
                Storage::putFileAs($basePath, $file, $photoNames[$i]);
            }
        }

        // その他ファイルアップロード
        for ($i = 1; $i <= 4; $i++) {
            $key = "file_{$i}";
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $fileNames[$i] = $generateFileName($file);
                Storage::putFileAs($basePath, $file, $fileNames[$i]);
            }
        }

        // identification 処理
        $registrationType = $request->registration_type;
        $identification_1 = null;
        $identification_2 = null;
        $identification_3 = null;
        $licenseFileName = null;

        if (in_array($registrationType, ['個人', '個人事業主'])) {
            $identification_1 = $request->input('identification_1');
            $licenseExpiry = $request->input('license_expiry');

            if ($request->hasFile('file_1')) {
                $licenseFile = $request->file('file_1');
                $licenseFileName = $generateFileName($licenseFile);
                Storage::putFileAs($basePath, $licenseFile, $licenseFileName);
                $fileNames[1] = $licenseFileName; // file_1 としても保持
            }
            // identification_3 を file_4 として保存する
            if ($request->hasFile('identification_3')) {
                $file = $request->file('identification_3');
                $fileNames[4] = $generateFileName($file);
                Storage::putFileAs($basePath, $file, $fileNames[4]);
            }

        }

        if ($registrationType === '個人事業主') {
            $identification_2 = $request->input('identification_2_1');
        } elseif (in_array($registrationType, ['法人', '業務請負'])) {
            $identification_2 = $request->input('identification_2_2');

            $file = $request->file('identification_3');
            $fileNames[4] = $generateFileName($file);
            Storage::putFileAs($basePath, $file, $fileNames[4]);
        }



        // Shop 作成
        $shop = auth()->user()->shop()->create([
            'is_draft' => $isDraft,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'invoice_number' => $request->input('invoice_number'),
            'location_1' => $request->input('location_1'),
            'location_2' => $request->input('location_2'),
            'telephone' => $request->input('telephone'),
            'email' => $request->input('email'),
            'person_1' => $request->input('person_1'),
            'person_2' => $request->input('person_2'),
            'person_3' => $request->input('person_3'),
            'license_expiry' => $request->input('license_expiry'),
            'corporate_number' => $request->input('corporate_number'),

            'representative' => $request->input('representative'),
            'manager' => $request->input('manager'),
            'product_type' => $request->input('product_type'),
            'volume' => $request->input('volume'),
            'note' => $request->input('note'),

            'identification_1' => $identification_1,
            'identification_2' => $identification_2,
            'identification_3' => $identification_3,

            'photo_1' => $photoNames[1],
            'photo_2' => $photoNames[2],
            'photo_3' => $photoNames[3],
            'photo_4' => $photoNames[4],
            'photo_5' => $photoNames[5],
            'photo_6' => $photoNames[6],
            'photo_7' => $photoNames[7],

            'file_1' => $fileNames[1] ?? 'no_file.txt',
            'file_2' => $fileNames[2] ?? 'no_file.txt',
            'file_3' => $fileNames[3] ?? 'no_file.txt',
            'file_4' => $fileNames[4] ?? 'no_file.txt',
        ]);

        // 下書きでなければ管理者へ通知
        if (!$isDraft) {
            $admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();
            Mail::to($admins)->send(new ShopActivationRequest($shop));

            return back()->withMessage('Shop開設に伴うオーナー情報を送信しました');
        }

        return back()->withMessage('下書きを保存しました。');
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
