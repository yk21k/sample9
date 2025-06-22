<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class ImportCsvProducts extends AbstractAction
{
    public function getTitle()
    {
        return 'CSV一括登録';
    }

    public function getIcon()
    {
        return 'voyager-upload';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-primary',
            'style' => 'margin-right:10px;',
        ];
    }

    public function getDefaultRoute()
    {
        // CSVアップロード画面へのルート
        return route('products.import.form');
    }

    public function shouldActionDisplayOnDataType()
    {
        // 商品データタイプだけに表示
        return $this->dataType->slug === 'products';
    }

    public function shouldActionDisplayOnRow($row)
    {
        return false; // 行ごとには表示しない（一覧の右上に表示させたい場合）
    }
}
