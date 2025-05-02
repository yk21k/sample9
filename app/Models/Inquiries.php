<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiries extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'shop_id', 'inq_subject', 'inquiry_details', 'ans_subject', 'answers'
    ];

    public function inqUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shopAd()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    // Inquiries.php モデル
    public static function getInqSubjectLabels()
    {
        return [
            '1' => '1: このお問い合わせの回答や問題解決と購入とは別のものです。',
            '2' => '2: このお問い合わせの回答や問題解決が解決されなくても購入します。',
            '3' => '3: このお問い合わせの回答や問題解決が解決したら購入します。',
            '4' => '4: この問題が解決されれば、購入を積極的に検討します。',
            '5' => '5: ご購入・お受け取りいただいた商品についてです。',
            '6' => '6: キャンセルしたい',
        ];
    }
}
