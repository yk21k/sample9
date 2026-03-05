<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;

use App\Models\User;
use App\Models\Shop;
use App\Models\ChecklistItem;

use App\Mail\ShopActivationRequest;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;

use Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shops = Shop::approved()->get();

        return view('shops.index', compact('shops'));
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
    // public function update(UpdateShopRequest $request, Shop $shop)
    // {
    //     //
    // }


    public function store(StoreShopRequest $request)
    {
        return $this->saveShop($request);
    }

    public function update(StoreShopRequest $request, Shop $shop)
    {
        return $this->saveShop($request, $shop);
    }

    private function saveShop($request, Shop $shop = null)
    {
        $isNew   = is_null($shop);
        $isDraft = $request->input('save_type') === 'draft';

        DB::beginTransaction();

        $savedFiles = [];

        try {
            if ($isNew) {
                $shop = new Shop();
                $shop->user_id = auth()->id();
            }

            // ==============================
            // 基本データ
            // ==============================

            // 🔥 photo を fill から除外（超重要）
            $shop->fill($request->except([
                'file_1','file_2','file_3','file_4',
                'identification_3',
                'photo_1','photo_2','photo_3','photo_4'
            ]));

            $shop->is_draft = $isDraft ? 1 : 0;

            if (!$isDraft) {
                $shop->status = Shop::STATUS_PENDING;
            }

            // ==============================
            // 先に保存（ID確定）
            // ==============================

            $shop->save();

            $basePath = "shops/{$shop->id}";

            // ==============================
            // ファイル保存処理
            // ==============================

            $fileColumns = [
                'file_1',
                'file_2',
                'file_3',
                'file_4',
                'photo_1',
                'photo_2',
                'photo_3',
                'photo_4',
                'photo_5',
                'photo_6',
                'photo_7',
                'photo_8',
            ];

            foreach ($fileColumns as $column) {

                if ($request->hasFile($column)) {

                    // 更新時：旧ファイル削除
                    if (!$isNew && $shop->{$column}) {
                        Storage::delete("{$basePath}/" . $shop->{$column});
                    }

                    $file = $request->file($column);
                    $fileName = \Str::uuid() . '.' . $file->getClientOriginalExtension();

                    Storage::putFileAs($basePath, $file, $fileName);

                    // モデルに保存（file と photo の紐付けは不要）
                    $shop->{$column} = $fileName;

                    $savedFiles[] = "{$basePath}/{$fileName}";
                }
            }
            $shop->identification_3 = $request->registration_type;
            $shop->save();

            // ==============================
            // 更新時：商品を非公開
            // ==============================

            if (!$isNew && !$isDraft) {
                $shop->products()->update(['status' => 0]);
            }

            DB::commit();

            return redirect()->route('shops.show', $shop->id)
                ->with('success', '保存しました');

        } catch (\Throwable $e) {

            DB::rollBack();

            // 保存済みファイル削除
            foreach ($savedFiles as $path) {
                Storage::delete($path);
            }

            throw $e;
        }
    }


    // ======================================
    // 承認時の商品復帰処理（管理画面用）
    // ======================================

    public function approve(Shop $shop)
    {
        DB::transaction(function () use ($shop) {

            $shop->status = Shop::STATUS_APPROVED;
            $shop->is_draft = 0;
            $shop->save();

            $shop->products()->update(['status' => 1]);
        });

        return back()->with('success', '承認しました');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop)
    {
        //
    }
}
