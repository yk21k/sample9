<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;
use App\Models\SubOrdersArrivalReport;

class SendStripeTransfer extends AbstractAction
{
    public function getTitle()
    {
        return 'Stripe送金';
    }

    public function getIcon()
    {
        return 'voyager-dollar';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-success',
        ];
    }

    public function getDefaultRoute()
    {
        return route('admin.stripe.transfer', ['id' => $this->data->id]);
    }

    public function shouldActionDisplayOnRow($row)
    {
        // モデルが "sub-orders" であることを確認
        if ($this->dataType->slug !== 'sub-orders') {
            return false;
        }

        // すでに送金済みの場合は非表示
        if (!is_null($row->transferred_at)) {
            return false;
        }

        // 到着レポートを取得
        $report = SubOrdersArrivalReport::where('sub_order_id', $row->id)->first();

        // payment_clicked_at が存在する場合のみボタンを表示
        return $report && !is_null($report->payment_clicked_at);
    }
}
