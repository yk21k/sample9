<?php

// database/seeders/ProductRegistrationFlowSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flow;

class ProductRegistrationFlowSeeder extends Seeder
{
    public function run(): void
    {
        $flow = Flow::create([
            'slug' => 'product_registration_flow',
            'title' => '商品登録の流れ',
            'overview' => '商品を販売開始するまでの一連の手順を案内します。',
        ]);

        $steps = [
            [
                'order' => 1,
                'title' => '商品登録',
                'required' => true,
                'description' =>
                    '注意事項として、現在は決済と連携していないため、商品ステータスは InActive に設定してください。',
            ],
            [
                'order' => 2,
                'title' => '商品の名前',
                'required' => true,
                'description' =>
                    '購入者が商品を識別しやすいよう、内容が分かる具体的な商品名を設定してください。',
            ],
            [
                'order' => 3,
                'title' => '商品の説明',
                'required' => true,
                'description' =>
                    '商品の特徴や用途、メリットを分かりやすく記載してください。',
            ],
            [
                'order' => 4,
                'title' => '商品の価格',
                'required' => true,
                'description' =>
                    '税込・税抜や原価とのバランスを考慮して設定してください。',
            ],
            [
                'order' => 5,
                'title' => '商品の配送料',
                'required' => false,
                'description' =>
                    '配送地域やサイズを考慮し、実際のコストに沿って設定してください。',
            ],
            [
                'order' => 6,
                'title' => '商品の在庫数',
                'required' => false,
                'description' =>
                    '販売可能な数量を正確に入力し、在庫切れを防ぎましょう。',
            ],
            [
                'order' => 7,
                'title' => '商品の写真',
                'required' => false,
                'description' =>
                    '商品が分かりやすい写真を複数登録すると購入率が向上します。',
            ],
            [
                'order' => 8,
                'title' => '商品の動画',
                'required' => false,
                'description' =>
                    '使用シーンやサイズ感が伝わる動画があると理解が深まります。',
            ],
            [
                'order' => 9,
                'title' => '商品のAttributes',
                'required' => false,
                'description' =>
                    '色・素材・型番など、商品を特徴づける属性を設定してください。',
            ],
            [
                'order' => 10,
                'title' => '商品のカテゴリー',
                'required' => false,
                'description' =>
                    '適切なカテゴリーに設定することで検索性が向上します。',
            ],
            [
                'order' => 11,
                'title' => '商品のサイズ',
                'required' => false,
                'description' =>
                    '寸法やサイズ展開を明記することで購入後のトラブルを防げます。',
            ],
        ];

        foreach ($steps as $step) {
            $flow->steps()->create([
                'step_order' => $step['order'],
                'title' => $step['title'],
                'description' => $step['description'],
                'is_required' => $step['required'],
            ]);
        }
    }
}

