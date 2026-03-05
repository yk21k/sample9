<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateShopRequest;
use App\Mail\ShopUpdateActivationRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

use App\Models\Shop;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopSettingController extends Controller
{
    public function index()
    {
        $shop_settings = Shop::where('user_id', auth()->id())->first();
        // dd($shop_settings);

        return view('sellers.shop.shop_setting', compact(['shop_settings']));
    }

    public function shopUpdate(UpdateShopRequest $request)
    {
        $user = auth()->user();

        DB::transaction(function () use ($request, $user, &$shop) {

            /*
            |--------------------------------------------------------------------------
            | 保存パス生成（推測不能）
            |--------------------------------------------------------------------------
            */
            $dateFolder = now()->format('Ymd');
            $basePath = "shop_documents/{$dateFolder}/user_{$user->id}/" . Str::uuid();

            /*
            |--------------------------------------------------------------------------
            | ファイル名生成（UUID）
            |--------------------------------------------------------------------------
            */
            $generateFileName = function ($file) {
                return Str::uuid() . '.' . $file->getClientOriginalExtension();
            };

            /*
            |--------------------------------------------------------------------------
            | アップロード対象フィールド
            |--------------------------------------------------------------------------
            */
            $photoFields = [
                'photo_1','photo_2','photo_3','photo_4',
                'photo_5','photo_6','photo_7'
            ];

            $fileFields = ['file_1','file_2','file_3','file_4'];

            $uploadedData = [];

            /*
            |--------------------------------------------------------------------------
            | 写真保存
            |--------------------------------------------------------------------------
            */
            foreach ($photoFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $fileName = $generateFileName($file);
                    $file->storeAs($basePath, $fileName, 'local');
                    $uploadedData[$field] = "{$basePath}/{$fileName}";
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 添付ファイル保存
            |--------------------------------------------------------------------------
            */
            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $fileName = $generateFileName($file);
                    $file->storeAs($basePath, $fileName, 'local');
                    $uploadedData[$field] = "{$basePath}/{$fileName}";
                }
            }

            /*
            |--------------------------------------------------------------------------
            | identification処理
            |--------------------------------------------------------------------------
            */
            $registrationType = $request->registration_type;

            $identification_1 = null;
            $identification_2 = null;

            if (in_array($registrationType, ['個人', '個人事業主'])) {
                $identification_1 = $request->input('identification_1');
            }

            if ($registrationType === '個人事業主') {
                $identification_2 = $request->input('identification_2_1');
            } elseif (in_array($registrationType, ['法人', '業務請負'])) {
                $identification_2 = $request->input('identification_2_2');
            }

            /*
            |--------------------------------------------------------------------------
            | Shop 更新（再申請＝非承認）
            |--------------------------------------------------------------------------
            */
            $shop = tap($user->shop)->update(array_merge([
                'name' => $request->name,
                'is_active' => 0, // 更新＝再審査
                'description' => $request->description,
                'invoice_number' => $request->invoice_number,
                'location_1' => $request->location_1,
                'location_2' => $request->location_2,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'person_1' => $request->person_1,
                'person_2' => $request->person_2,
                'person_3' => $request->person_3,
                'license_expiry' => $request->license_expiry,
                'representative' => $request->representative,
                'manager' => $request->manager,
                'product_type' => $request->product_type,
                'volume' => $request->volume,
                'note' => $request->note,
                'identification_1' => $identification_1,
                'identification_2' => $identification_2,
            ], $uploadedData));
        });
        dd($request->input('save_type'));
        /*
        |--------------------------------------------------------------------------
        | 管理者通知（下書きでない場合）
        |--------------------------------------------------------------------------
        */
        if ($request->input('save_type') !== 'draft') {

            $user->shop->products()->update([
                'status' => 0
            ]);

            $admins = User::whereHas('role', function ($q) {
                $q->where('name', 'admin');
            })->get();

            Mail::to($admins)->send(new ShopUpdateActivationRequest($shop));

            return back()->withMessage('Shopのオーナー情報を変更しました（再審査中）。');
        }

        return back()->withMessage('Shopのオーナー情報を保存しました。');
    } 
    
}
