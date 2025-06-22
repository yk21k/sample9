<?php
namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Illuminate\Support\Facades\Log;

class ProductImport implements ToModel, WithHeadingRow 
{
    protected $sellerId;

    public function __construct($sellerId)
    {
        $this->sellerId = $sellerId;
    }

    public function model(array $row)
    {
        // dd($row);
        return new Product([
            'name'        => $row['name'],
            'description' => $row['description'],
            'status'      => $row['status'],
            'price'       => $row['price'],
            'shipping_fee'=> $row['shipping_fee'],
            'stock'       => $row['stock'],
            'shop_id'     => $this->sellerId,
        ]);
        dd($data);
        return new Product($data);
    }
}