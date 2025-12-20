<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class SendPickupConfirmation extends AbstractAction
{
    public function getTitle()
    {
        return '受け渡しメール';
    }

    public function getIcon()
    {
        return 'voyager-mail';
    }

    public function getPolicy()
    {
        return 'edit';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-success btn-sm',
        ];
    }

    public function getDefaultRoute()
    {
        return route('shop.sendPickupConfirmation', $this->data->id);
    }

    // ★ このアクションを表示する条件
    public function shouldActionDisplayOnDataType()
    {
        return true; // 一覧画面で表示
    }

    public function shouldActionDisplayOnRow($row)
    {
        // ★ ここで "received" のときだけ表示
        return $row->status === 'received';
    }

    public function getView()
    {
        return 'voyager::actions.send_pickup_confirmation';
    }
}
