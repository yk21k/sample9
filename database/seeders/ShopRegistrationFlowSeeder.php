<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flow;
use App\Models\FlowStep;

class ShopRegistrationFlowSeeder extends Seeder
{
    public function run(): void
    {
        // Flow 本体
        $flow = Flow::updateOrCreate(
            ['slug' => 'shop_registration_flow'],
            [
                'title'    => 'Shop 開設後の確認フロー',
                'overview' => 'Shop 開設完了後にご確認いただく基本的な流れです。',
            ]
        );

        // 再実行対策（既存 step 削除）
        FlowStep::where('flow_id', $flow->id)->delete();

        $steps = [
            [
                'step_order' => 1,
                'title' => 'Shopの開設が完了しました',
                'description' =>
                    '注意事項：本フローはオークション商品や来店して受け取る商品は対象外です。' .
                    '通常の商品登録ページおよびオーダーマネジメントのページで詳細をご案内します。' .
                    'なお、決済とは連携していないため、商品登録を行う場合は status を「InActive」に設定してください。',
            ],
            [
                'step_order' => 2,
                'title' => 'Nameの確認',
                'description' =>
                    'Shop 名（Name）が正しく入力されているかをご確認ください。' .
                    '購入者や利用者にとって分かりやすい名称になっていることが重要です。',
            ],
            [
                'step_order' => 3,
                'title' => 'Shop情報の確認',
                'description' =>
                    'Shop の説明文、インボイス番号、法人の場合は法人番号が正しく登録されているかをご確認ください。' .
                    '情報に誤りがあると、取引や運営に影響する可能性があります。',
            ],
            [
                'step_order' => 4,
                'title' => 'その他情報の最終確認',
                'description' =>
                    'その他の登録情報をご確認ください。問題がある場合は、この段階で右上の「ホーム」ボタンからホーム画面へ戻り、' .
                    '再度右上の「退会」ボタンから無料で退会することも可能です。',
            ],
            [
                'step_order' => 5,
                'title' => '管理者からの確認について',
                'description' =>
                    '「足りない時や不鮮明時は連絡します」という表示については、管理者側で正しく確認できる状態になっています。' .
                    '不足や不備があった場合のみ、管理者からご連絡いたしますのでご安心ください。',
            ],
            [
                'step_order' => 6,
                'title' => 'View画面について',
                'description' =>
                    'View ボタンから画面遷移は可能ですが、その画面から Shop 情報の変更はできません。' .
                    '情報を修正する場合は、指定された編集画面から行ってください。',
            ],
        ];

        foreach ($steps as $step) {
            FlowStep::create([
                'flow_id'     => $flow->id,
                'step_order'  => $step['step_order'],
                'title'       => $step['title'],
                'description' => $step['description'],
                'is_required' => true,
            ]);
        }
    }
}

