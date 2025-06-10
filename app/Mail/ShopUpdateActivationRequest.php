<?php

namespace App\Mail;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShopUpdateActivationRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function build()
    {
        return $this->subject('【ショップ更新申請】新しい内容が届きました')
                    ->markdown('mail.admin.update_activation_request')
                    ->with([
                        'shop' => $this->shop,
                    ]);
    }
}
