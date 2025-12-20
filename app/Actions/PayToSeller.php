<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;
use App\Models\AuctionOrder;
use App\Models\Auction;
use Carbon\Carbon;

class PayToSeller extends AbstractAction
{
    public function getTitle()
    {
        return 'PAY(オークション）';
    }

    public function getIcon()
    {
        return 'voyager-credit-card';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-success',

        ];
    }

    public function getDefaultRoute()
    {
        return route('admin.pay.to.seller', ['id' => $this->data->id]);
    }

    // public function shouldActionDisplayOnRow($row)
    // {
    //     // auction_orders テーブル限定（適宜変更）
    //     if ($this->dataType->slug !== 'auction-orders') {
    //         return false;
    //     }

    //     // dd($this->data);
    //     $auction_parts = Auction::where('id', $this->data->auction_id)->first();
    //     // dd($auction_parts->end);

    //     $now = Carbon::now();
    //     $endDate = $auction_parts->end;
    //     $oneWeekLater = $endDate->addWeek();

    //     // 配達ステータスが「3」（配達完了など）でなければ非表示
    //     if ($row->arrival_status != 1) {
    //         return false;
    //     }
        


    //     // すでに送金済みなら非表示（transferred_atカラムがある前提）
    //     return is_null($row->transferred_at);
    // }

    public function shouldActionDisplayOnRow($row)
    {
        // auction_orders テーブル限定（適宜変更）
        if ($this->dataType->slug !== 'auction-orders') {
            return false;
        }

        // 関連するオークション情報を取得
        $auction_parts = Auction::where('id', $row->auction_id)->first();
        if (!$auction_parts || !$auction_parts->end) {
            return false; // 終了日が取得できない場合は表示しない
        }

        $now = Carbon::now();
        $endDate = Carbon::parse($auction_parts->end); // 終了日
        $oneWeekLater = $endDate->copy()->addWeek();  // 終了日＋1週間

        // 条件：
        // 配達ステータスが3 かつ 終了日＋1週間よりも現在日時が後　わざと遅らせた場合の対応が抜けている　手配日を格納する
        if ((int) $row->arrival_status === 3 || $now->greaterThanOrEqualTo($oneWeekLater)) {
            // 送金済みなら非表示
            return is_null($row->transferred_at);
        }

        return false;
    }
}
