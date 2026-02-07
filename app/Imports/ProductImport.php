<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Validator;

class ProductImport implements
    ToModel,
    WithHeadingRow,
    WithValidation
{
    protected $sellerId;

    const MAX_SHIPPING_FEE = 6500;

    public function __construct($sellerId)
    {
        $this->sellerId = $sellerId;
    }

    /**
     * ★ Validation
     */
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'description' => ['required'],
            'status' => ['in:0'], // status=1 NG
            'price' => ['required','numeric','min:1'],
            'shipping_fee' => ['required','numeric'],
            'stock' => ['required','integer','min:0'],
            'cover_img' => [
                function ($attr, $value, $fail) {
                    if (!empty($value)) {
                        $fail('cover_img は CSV インポート不可です');
                    }
                }
            ],
        ];
    }

    /**
     * ★ 行単位で price × shipping_fee をチェック
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->getData() as $row) {
                $price = $row['price'] ?? null;
                $shippingFee = $row['shipping_fee'] ?? null;

                if (!$price || !$shippingFee) {
                    continue;
                }

                $maxByRate  = floor($price * 0.25);
                $maxAllowed = min($maxByRate, self::MAX_SHIPPING_FEE);

                if ($shippingFee > $maxAllowed) {
                    $validator->errors()->add(
                        'shipping_fee',
                        "配送料は {$maxAllowed}円以内にしてください（価格の25%まで）"
                    );
                }
            }
        });
    }

    /**
     * ★ Validation をすべて通過した後だけ保存される
     */
    public function model(array $row)
    {
        return new Product([
            'name'         => $row['name'],
            'description'  => $row['description'],
            'status'       => $row['status'],
            'price'        => $row['price'],
            'shipping_fee' => $row['shipping_fee'],
            'stock'        => $row['stock'],
            'shop_id'      => $this->sellerId,
        ]);
    }
}
