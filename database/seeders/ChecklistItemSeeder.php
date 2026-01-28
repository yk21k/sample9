<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChecklistItem;

class ChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            [
                'name' => 'name',
                'label' => 'ショップ名',
                'purpose' => '利用者が事業者を識別するために使用します。',
                'recommendation' => '法人名または屋号を正式名称で記載してください。',
                'search_keywords' => ['ショップ名', '屋号', '事業名'],
                'sort_order' => 1,
            ],

            [
                'name' => 'description',
                'label' => '事業内容',
                'purpose' => '提供される商品・サービスの内容確認に使用します。',
                'recommendation' => '具体的かつ第三者にも分かる表現で記載してください。',
                'search_keywords' => ['事業内容', 'サービス説明'],
                'sort_order' => 2,
            ],

            [
                'name' => 'invoice_number',
                'label' => 'インボイス登録番号',
                'purpose' => '適格請求書発行事業者かどうかの確認に使用します。',
                'recommendation' => '国税庁の公表サイトで確認できる番号を入力してください。',
                'search_keywords' => ['インボイス', '適格請求書', '国税庁'],
                'sort_order' => 3,
            ],

            [
                'name' => 'location',
                'label' => '所在地',
                'purpose' => '事業者の実在性および連絡先確認に使用します。',
                'recommendation' => '登記情報や公式サイトと一致する住所を入力してください。',
                'search_keywords' => ['所在地', '住所', '本店所在地'],
                'sort_order' => 4,
            ],

            [
                'name' => 'telephone',
                'label' => '電話番号',
                'purpose' => '緊急時や確認時の連絡手段として使用します。',
                'recommendation' => '常時連絡可能な番号を記載してください。',
                'search_keywords' => ['電話', 'TEL', '連絡先'],
                'sort_order' => 5,
            ],

            [
                'name' => 'email',
                'label' => 'メールアドレス',
                'purpose' => '審査結果や重要連絡の送付に使用します。',
                'recommendation' => '日常的に確認できるメールアドレスを使用してください。',
                'search_keywords' => ['メール', 'email', '連絡'],
                'sort_order' => 6,
            ],

            [
                'name' => 'license_expiry',
                'label' => '本人確認書類の有効期限',
                'purpose' => '本人確認書類が有効であるかの確認に使用します。',
                'recommendation' => '期限切れでないことを必ず確認してください。',
                'search_keywords' => ['本人確認', '有効期限'],
                'sort_order' => 7,
            ],

            [
                'name' => 'corporate_number',
                'label' => '法人番号',
                'purpose' => '法人の実在確認および公的情報照合に使用します。',
                'recommendation' => '国税庁法人番号公表サイトで確認した13桁の番号を入力してください。',
                'search_keywords' => ['法人番号', '国税庁', '登記'],
                'sort_order' => 8,
            ],

            [
                'name' => 'person_1',
                'label' => '担当者①',
                'purpose' => '主担当者としての連絡先確認に使用します。',
                'recommendation' => '実務を担当する方の氏名を記載してください。',
                'search_keywords' => ['担当者', '責任者'],
                'sort_order' => 9,
            ],

            [
                'name' => 'person_2',
                'label' => '担当者②',
                'purpose' => '副担当者または補助連絡先として使用します。',
                'recommendation' => '不在時に対応可能な方を設定してください。',
                'search_keywords' => ['副担当', '連絡先'],
                'sort_order' => 10,
            ],

            [
                'name' => 'person_3',
                'label' => '担当者③',
                'purpose' => '追加連絡先として使用します。',
                'recommendation' => '必要に応じて入力してください。',
                'search_keywords' => ['担当者', '予備'],
                'sort_order' => 11,
            ],

            [
                'name' => 'representative',
                'label' => '代表者名',
                'purpose' => '事業の最終責任者確認に使用します。',
                'recommendation' => '登記情報と一致する氏名を記載してください。',
                'search_keywords' => ['代表者', '代表'],
                'sort_order' => 12,
            ],

            [
                'name' => 'manager',
                'label' => '運営責任者',
                'purpose' => '実際の運営責任者の確認に使用します。',
                'recommendation' => '日常業務を管理する方を指定してください。',
                'search_keywords' => ['責任者', '管理者'],
                'sort_order' => 13,
            ],

            [
                'name' => 'product_type',
                'label' => '取扱商品・サービス',
                'purpose' => '販売内容の適正性確認に使用します。',
                'recommendation' => '具体的な商品カテゴリを記載してください。',
                'search_keywords' => ['商品', 'サービス', '販売内容'],
                'sort_order' => 14,
            ],

            [
                'name' => 'volume',
                'label' => '取扱数量・規模',
                'purpose' => '事業規模の把握およびリスク評価に使用します。',
                'recommendation' => '月間・年間の目安数量を入力してください。',
                'search_keywords' => ['数量', '規模', '取引量'],
                'sort_order' => 15,
            ],

            [
                'name' => 'note',
                'label' => '備考',
                'purpose' => '補足情報や特記事項の確認に使用します。',
                'recommendation' => '審査上必要な補足があれば記載してください。',
                'search_keywords' => ['備考', '補足'],
                'sort_order' => 16,
            ],

        ];

        foreach ($items as $item) {
            ChecklistItem::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
