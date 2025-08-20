<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoiceExport implements FromCollection, WithHeadings
{
    protected $sales;

    public function __construct($sales)
    {
        $this->sales = $sales;
    }

    public function collection()
    {
        return collect($this->sales)->map(function ($row) {
            return [
                optional($row->confirmed_at)->format('Y-m-d'),
                optional($row->pay_transfer)->format('Y-m-d'),
                $row->order_number,
                $row->seller_name,
                $row->seller_registration_number,
                $row->product_name,
                $row->quantity,
                $row->unit_price,
                $row->tax_rate,
                $row->tax_amount,
                $row->total_amount,
                $row->tax_category,
            ];
        });
    }

    public function headings(): array
    {
        return [
            '取引日', '振込日', '注文番号', '出品者名称', '出品者登録番号',
            '商品名', '数量', '単価（税抜）', '税率', '消費税額', '税込金額', '税区分'
        ];
    }
}

