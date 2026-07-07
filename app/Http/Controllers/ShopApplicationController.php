<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopApplication;
use App\Models\Shop;
use App\Models\ShopMember;
use App\Models\ShopApplicationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShopApplicationController extends Controller
{
    public function store(Request $request)
    {
        // dd($request->all(), $request->file());
        // -----------------------
        // ① タイプ取得
        // -----------------------
        $typeMap = [
            '個人' => 'individual',
            '個人事業主' => 'sole',
            '法人' => 'company',
            '業務請負' => 'contractor',
        ];

        $rawType = $request->input('registration_type');
        $type = $typeMap[$rawType] ?? null;

        // -----------------------
        // ② バリデーション
        // -----------------------
        $rules = [

            'registration_type' => 'required',

            'name' => 'required|string|max:255',
            'invoice_number' => 'nullable|string',
            'tax_calculation' => 'required|in:0,1',
            'description' => 'required|string',
            'representative' => 'required|string',
            'location_1' => 'required|string',
            'location_2' => 'nullable|string',
            'telephone' => 'required|string',
            'email' => 'required|email',

            'file_1' => 'required|file', // 請求書

            // 担当者
            'person_1' => 'nullable|string',
            'photo_3' => 'nullable|file',
            'photo_5' => 'nullable|file',
            'photo_7' => 'nullable|file',

                // =========================
            // 🔥 担当者（配列）
            // =========================
            'members' => 'nullable|array',

            'members.*.name' => 'nullable|string|max:255',
            'members.*.file1' => 'nullable|file',

        ];

        // -----------------------
        // 🔥 ② 手動チェック（ここ）
        // -----------------------
        foreach ($request->members ?? [] as $i => $member) {

            if (!empty($member['name']) && !$request->hasFile("members.$i.file1")) {

                return back()
                    ->withErrors(["members.$i.file1" => "担当者の証明ファイルは必須です"])
                    ->withInput();
            }
        }

        // タイプ別
        if (in_array($rawType, ['個人','個人事業主'])) {
            $rules['photo_1'] = 'required|file|image';
            $rules['photo_2'] = 'required|file|image';
        }

        if ($rawType === '個人事業主') {
            $rules['file_2'] = 'required|file';
        }

        if (in_array($rawType, ['法人','業務請負'])) {
            $rules['file_3'] = 'required|file';
            $rules['corporate_number'] = 'required|string';
        }

        $data = $request->validate($rules);

        // -----------------------
        // ③ 二重申請防止
        // -----------------------
        $exists = \App\Models\ShopApplication::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('error', '現在審査中の申請があります');
        }

        // -----------------------
        // ④ 保存処理
        // -----------------------
        DB::transaction(function () use ($request, $data, $type) {

            $store = function($file, $dir){
                return $file ? $file->store($dir, 's3') : null;
            };

            // -----------------------
            // 共通
            // -----------------------
            $common = [
                'shop_name' => $data['name'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'tax_type' => $data['tax_calculation'] == 1 ? 'auto' : 'manual',
                'description' => $data['description'],
                'representative' => $data['representative'],
                'address' => $data['location_1'],
                'shipping_address' => $data['location_2'] ?? null,
                'phone' => $data['telephone'],
                'email' => $data['email'],
                'billing_file' => $store($request->file('file_1'), 'billing'),
            ];

            // -----------------------
            // タイプ別
            // -----------------------
            $typeSpecific = [
                'id_card_front' => $store($request->file('photo_1'), 'id'),
                'id_card_back' => $store($request->file('photo_2'), 'id'),
                'business_registration' => $store($request->file('file_2'), 'business'),
                'business_card' => $store($request->file('file_3'), 'business'),
                'corporate_number' => $data['corporate_number'] ?? null,
            ];


            $members = [];

            // 新方式
            if (!empty($request->members)) {

                foreach ($request->members as $i => $member) {

                    if (empty($member['name'])) continue;

                    $members[] = [
                        'name' => $member['name'],
                        'file1' => $store($request->file("members.$i.file1"), 'members'),
                    ];
                }

            } else {
                // 旧方式 fallback
                foreach ([1,2,3] as $i) {
                    $name = $request->input("person_$i");

                    if ($name) {
                        $members[] = [
                            'name' => $name,
                            'file1' => $store($request->file("photo_".(2*$i+1)), 'members'),
                        ];
                    }
                }
            }

            // dd($request->members);
            // -----------------------
            // after_data
            // -----------------------
            $afterData = [
                'type' => $type,
                'common' => $common,
                'type_specific' => $typeSpecific,
                'members' => $members,
            ];

            // -----------------------
            // 保存
            // -----------------------
            \App\Models\ShopApplication::create([
                'user_id' => auth()->id(),
                'type' => 'new',
                'status' => 'pending',
                'after_data' => $afterData,
            ]);

        });

        return back()->with('success', '申請しました（審査中）');
    }

    public function updateRequest(Request $request, $shopId)
    {
        $shop = Shop::findOrFail($shopId);

        // 🔥 権限チェック
        $this->authorize('edit', $shop);

        // -----------------------
        // バリデーション（簡略）
        // -----------------------
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // -----------------------
        // 差分用 before / after
        // -----------------------
        $before = $shop->toArray();

        // 🔥 afterは「変更対象だけ」でもOK
        $after = [
            'common' => [
                'shop_name' => $data['name'],
                'description' => $data['description'],
            ]
        ];

        // -----------------------
        // 申請保存
        // -----------------------
        ShopApplication::create([
            'user_id' => auth()->id(),
            'shop_id' => $shop->id,
            'type' => 'update', // 🔥ここ重要
            'status' => 'pending',
            'before_data' => $before,
            'after_data' => $after,
        ]);

        return back()->with('success', '更新申請しました');
    }


}
