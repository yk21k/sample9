<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;
use App\Models\AuctionOrder;

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

    public function shouldActionDisplayOnRow($row)
    {
        // auction_orders テーブル限定（適宜変更）
        if ($this->dataType->slug !== 'auction-orders') {
            return false;
        }

        // 配達ステータスが「3」（配達完了など）でなければ非表示
        if ($row->arrival_status != 1) {
            return false;
        }

        // すでに送金済みなら非表示（transferred_atカラムがある前提）
        return is_null($row->transferred_at);
    }
}
