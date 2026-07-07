<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Shop;
use App\Models\ShopApplication;
use App\Models\ShopApplicationLog;
use App\Models\ShopMember;

use Illuminate\Support\Facades\Log;

use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class ShopApplicationReviewController extends VoyagerBaseController
{

    public function index(Request $request)
    {
        $applications = ShopApplication::with('user','shop')
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->paginate(20);


        return view('admin.shop_review.index', compact('applications'));
    }

    public function dashboard()
    {


        return view('admin.shop_review.dashboard');
    }

    public function show(Request $request, $id)
    {
        $application = ShopApplication::with('user','shop')->findOrFail($id);

        $before = $application->before_data ?? [];
        $after  = $application->after_data ?? [];

        return view('admin.shop_review.show', compact(
            'application',
            'before',
            'after'
        ));
    }

    // /**
    //  * 承認
    //  */
    public function approve(Request $request, $id)
    {
        // dd('Hello');
        DB::transaction(function () use ($id) {

            $application = ShopApplication::lockForUpdate()->findOrFail($id);

            // dd($application->user_id);

            if ($application->status !== 'pending') {
                abort(400, '既に処理済みです');
            }

            // $data = $application->after_data;
            $data = $application->after_data ?? [];

            // dd($data);

            // =========================
            // 🔥 NEW or UPDATE 分岐
            // =========================
            if ($application->type === 'new') {

                // =========================
                // 🟢 新規作成
                // =========================
                $shop = Shop::create([
                    'user_id' => $application->user_id,

                    'name' => $data['common']['shop_name'],
                    'invoice_number' => $data['common']['invoice_number'],
                    'tax_type' => $data['common']['tax_type'],
                    'description' => $data['common']['description'],
                    'representative' => $data['common']['representative'],
                    'location_1' => $data['common']['address'],
                    'location_2' => $data['common']['shipping_address'],
                    'telephone' => $data['common']['phone'],
                    'email' => $data['common']['email'],

                    'billing_file' => $data['common']['billing_file'],

                    'type' => $data['type'],

                    'id_card_front' => $data['type_specific']['id_card_front'] ?? null,
                    'id_card_back' => $data['type_specific']['id_card_back'] ?? null,
                    'business_registration' => $data['type_specific']['business_registration'] ?? null,
                    'business_card' => $data['type_specific']['business_card'] ?? null,
                    'corporate_number' => $data['type_specific']['corporate_number'] ?? null,

                    'review_status' => 'approved',
                ]);

                // owner
                ShopMember::create([
                    'shop_id' => $shop->id,
                    'user_id' => $application->user_id,
                    'name' => $data['common']['representative'],
                    'role' => 'owner',
                ]);

            } else {

                // =========================
                // 🔵 更新処理
                // =========================
                $shop = Shop::findOrFail($application->shop_id);

                $shop->update([
                    'name' => $data['common']['shop_name'],
                    'invoice_number' => $data['common']['invoice_number'],
                    'tax_type' => $data['common']['tax_type'],
                    'description' => $data['common']['description'],
                    'representative' => $data['common']['representative'],
                    'location_1' => $data['common']['address'],
                    'location_2' => $data['common']['shipping_address'],
                    'telephone' => $data['common']['phone'],
                    'email' => $data['common']['email'],

                    'billing_file' => $data['common']['billing_file'],

                    'type' => $data['type'],

                    'id_card_front' => $data['type_specific']['id_card_front'] ?? null,
                    'id_card_back' => $data['type_specific']['id_card_back'] ?? null,
                    'business_registration' => $data['type_specific']['business_registration'] ?? null,
                    'business_card' => $data['type_specific']['business_card'] ?? null,
                    'corporate_number' => $data['type_specific']['corporate_number'] ?? null,

                    'review_status' => 'approved',
                ]);

                // 🔥 メンバー更新（重要）
                ShopMember::where('shop_id', $shop->id)->delete();
            }
            // dd($data['members']);
            // =========================
            // 👥 担当者登録（共通）
            // =========================
            if (!empty($data['members'])) {
                foreach ($data['members'] as $member) {

                    // dd($member);

                    // 🔥 ここを強化
                    if (!isset($member['name']) || trim($member['name']) === '') {
                        continue;
                    }

                    ShopMember::create([
                        'shop_id' => $shop->id,
                        // 'user_id' => null,
                        // 'user_id' => array_key_exists('user_id', $member) ? $member['user_id'] : null,
                        'user_id' => $member['user_id'] ?? null,
                        'name' => $member['name'],
                        'file1' => $member['file1'] ?? null,
                        'file2' => $member['file2'] ?? null,
                        'role' => 'manager',


                    ]);

                    
                }
            }

            // =========================
            // 📌 ステータス更新
            // =========================
            $application->update([
                'status' => 'approved'
            ]);

            // =========================
            // 🧾 ログ
            // =========================
            ShopApplicationLog::create([
                'shop_application_id' => $application->id,
                'action' => 'approved',
                'user_id' => auth()->id(), // 🔥 修正
            ]);

        });

        return redirect()
            ->route('admin.shop.review')
            ->with('success', '承認しました');
    }

    // /**
    //  * 却下
    //  */
    public function reject($id)
    {
        $application = ShopApplication::findOrFail($id);

        if ($application->status !== 'pending') {
            abort(400, '既に処理済み');
        }

        $application->update([
            'status' => 'rejected'
        ]);

        ShopApplicationLog::create([
            'shop_application_id' => $application->id,
            'action' => 'rejected',
            'admin_id' => auth()->id(),
        ]);

        return back()->with('success', '却下しました');
    }
}
