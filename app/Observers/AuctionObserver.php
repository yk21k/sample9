<?php

namespace App\Observers;

use App\Models\Auction;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuctionUpdateMail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuctionObserver
{
    /**
     * Handle the Auction "created" event.
     */
    public function created(Auction $auction): void
    {
        //
    }

    /**
     * Handle the Auction "updated" event.
     */


    public function updated(Auction $auction): void
    {
        if (
            $auction->isDirty('delivery_status') &&
            $auction->delivery_status == 3 &&
            $auction->getOriginal('delivery_status') != 3
        ) {
            Log::info('📦 配送ステータスが3（配達完了）に変更されました。ID: ' . $auction->id);

            if ($auction->winner && $auction->winner->email) {
                try {
                    Mail::to($auction->winner->email)->send(new AuctionUpdateMail($auction));

                    // メール送信成功 → mail_sent_at を保存
                    $auction->mail_sent_at = Carbon::now();
                    $auction->saveQuietly(); // イベント再発火を防ぐ

                    // 対応する AuctionOrder も更新（assumes hasOne or hasMany relationship）
                    if ($auction->order) {
                        $auction->order->mail_sent_at = Carbon::now();
                        $auction->order->saveQuietly();
                    }

                } catch (\Exception $e) {
                    Log::error('📧 メール送信失敗：' . $e->getMessage());
                }
            } else {
                Log::warning('📧 メール送信スキップ：winner情報またはemailが存在しません。Auction ID: ' . $auction->id);
            }
        }
    }


    /**
     * Handle the Auction "deleted" event.
     */
    public function deleted(Auction $auction): void
    {
        //
    }

    /**
     * Handle the Auction "restored" event.
     */
    public function restored(Auction $auction): void
    {
        //
    }

    /**
     * Handle the Auction "force deleted" event.
     */
    public function forceDeleted(Auction $auction): void
    {
        //
    }
}
