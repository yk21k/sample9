<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Shop;

class LicenseExpiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $shop;

    /**
     * Create a new message instance.
     */
    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // メールの送信設定
        return $this->to($this->shop->email) // 受信者のメールアドレス
                    ->subject('【重要】代表者の運転免許証の期限が近づいています') // 件名
                    ->markdown('mail.customer.expiry') // メールテンプレートビュー
                    ->with([
                        'shop' => $this->shop, // ビューに渡すデータ（shop）
                    ]);
    }
}

