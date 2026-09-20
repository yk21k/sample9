<?php

namespace App\Services\Video\Usage;

use App\Models\Shop;
use App\Models\ShopVideoDraft;
use App\Models\ShopVideoUsageLog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShopVideoUsageService
{
    /*
    |--------------------------------------------------------------------------
    | 1日の動画加工上限
    |--------------------------------------------------------------------------
    */

    public function getDailyLimit(
        Shop $shop
    ): int {

        return 5;
    }

    /*
    |--------------------------------------------------------------------------
    | 今日の加工回数
    |--------------------------------------------------------------------------
    */

    public function getTodayUsage(
        Shop $shop
    ): int {

        return ShopVideoUsageLog::query()
            ->where(
                'shop_id',
                $shop->id
            )
            ->whereDate(
                'usage_date',
                today()
            )
            ->where(
                'usage_type',
                ShopVideoUsageLog::TYPE_VIDEO_PROCESSING
            )
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | 残り加工回数
    |--------------------------------------------------------------------------
    */

    public function getRemaining(
        Shop $shop
    ): int {

        return max(
            0,
            $this->getDailyLimit($shop)
                - $this->getTodayUsage($shop)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 加工可能か　表示用に使う予定です
    |--------------------------------------------------------------------------
    */

    public function canStartProcessing(
        Shop $shop
    ): bool {

        return $this->getRemaining($shop) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | 加工開始
    |--------------------------------------------------------------------------
    |
    */

    public function startProcessing(
        Shop $shop,
        ShopVideoDraft $video
    ): void {

        DB::transaction(function () use (
            $shop,
            $video
        ) {

            /*
            |--------------------------------------------------------------------------
            | Shopロック
            |--------------------------------------------------------------------------
            */

            Shop::query()
                ->whereKey($shop->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Videoロック
            |--------------------------------------------------------------------------
            */

            $video = ShopVideoDraft::query()
                ->whereKey($video->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | 店舗チェック
            |--------------------------------------------------------------------------
            */

            if ($video->shop_id !== $shop->id) {
                throw new RuntimeException(
                    'この動画は対象店舗の動画ではありません。'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | すでに加工中
            |--------------------------------------------------------------------------
            */

            if (
                $video->process_status
                === ShopVideoDraft::PROCESS_RUNNING
            ) {
                throw new RuntimeException(
                    '現在、動画を加工中です。'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 今日の加工回数
            |--------------------------------------------------------------------------
            */

            $used = ShopVideoUsageLog::query()
                ->where(
                    'shop_id',
                    $shop->id
                )
                ->whereDate(
                    'usage_date',
                    today()
                )
                ->where(
                    'usage_type',
                    ShopVideoUsageLog::TYPE_VIDEO_PROCESSING
                )
                ->count();

            /*
            |--------------------------------------------------------------------------
            | 上限
            |--------------------------------------------------------------------------
            */

            $limit = $this->getDailyLimit($shop);

            if ($used >= $limit) {
                throw new RuntimeException(
                    '本日の動画加工利用回数の上限に達しています。'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 利用回数消費
            |--------------------------------------------------------------------------
            */

            ShopVideoUsageLog::create([

                'shop_id'
                    => $shop->id,

                'video_id'
                    => $video->id,

                'usage_type'
                    => ShopVideoUsageLog::TYPE_VIDEO_PROCESSING,

                'usage_date'
                    => today(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | 加工中へ変更
            |--------------------------------------------------------------------------
            */

            $video->update([

                'process_status'
                    => ShopVideoDraft::PROCESS_RUNNING,

            ]);
        });
    }


}