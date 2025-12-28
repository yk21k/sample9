<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LandingSectionVariant;

class LandingSectionVariantSeeder extends Seeder
{
    public function run(): void
    {
        /*
         |--------------------------------------------------------------------------
         | 出店者向け（seller）
         |--------------------------------------------------------------------------
         */
        $sellerVariants = [
            [
                'variant_key' => 'A',
                'title' => '出店を検討している方へ',
                'description' => '商品を登録するだけで、販売を始められるマーケットです。',
                'features' => [
                    '初期費用なしで出店',
                    '管理画面から簡単操作',
                    '購入者へ直接アプローチ',
                ],
                'cta_text' => '出店について詳しく見る',
                'cta_url' => '/seller/about',
                'bg_color' => '#0f172a',
                'btn_color' => '#2563eb',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'variant_key' => 'B',
                'title' => 'あなたの商品を、もっと自由に',
                'description' => '初期費用なしで、あなたの世界観をそのまま届けられます。',
                'features' => [
                    '自由に商品登録',
                    '独自の販売スタイル',
                    '柔軟な価格設定',
                ],
                'cta_text' => '出店を始める',
                'cta_url' => '/seller/apply',
                'bg_color' => '#111827',
                'btn_color' => '#1d4ed8',
                'is_active' => false,
                'sort_order' => 2,
            ],
        ];

        foreach ($sellerVariants as $variant) {
            LandingSectionVariant::create(array_merge($variant, [
                'section_type' => 'seller',
            ]));
        }

        /*
         |--------------------------------------------------------------------------
         | 購入者向け（buyer）
         |--------------------------------------------------------------------------
         */
        $buyerVariants = [
            [
                'variant_key' => 'A',
                'title' => '商品を探している方へ',
                'description' => '個性ある出店者の商品をまとめて探せます。',
                'features' => [
                    'カテゴリ別に探しやすい',
                    '安心できる取引',
                    '新しい商品との出会い',
                ],
                'cta_text' => '商品を探す',
                'cta_url' => '/products',
                'bg_color' => '#1e1b2e',
                'btn_color' => '#6d28d9',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'variant_key' => 'B',
                'title' => 'まだ知らない商品に出会う',
                'description' => 'ここでしか出会えない商品が見つかります。',
                'features' => [
                    '新商品をチェック',
                    'ランキングで人気商品確認',
                    'おすすめ特集を閲覧',
                ],
                'cta_text' => '人気商品を見る',
                'cta_url' => '/products/popular',
                'bg_color' => '#1c1a28',
                'btn_color' => '#9333ea',
                'is_active' => false,
                'sort_order' => 2,
            ],
        ];

        foreach ($buyerVariants as $variant) {
            LandingSectionVariant::create(array_merge($variant, [
                'section_type' => 'buyer',
            ]));
        }
    }
}
