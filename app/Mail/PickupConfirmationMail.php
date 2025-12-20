<?php

namespace App\Mail;

use App\Models\PickupOrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class PickupConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public PickupOrderItem $item;
    public string $url;

    /**
     * Create a new message instance.
     */
    public function __construct(PickupOrderItem $item)
    {
        $this->item = $item;
        // 購入者が受取確認を行うためのURLを生成
        $this->url = route('pickup.confirm.form', ['token' => $item->confirmation_token]);
    }

    /**
     * 件名など
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【受取確認】商品を受け取られましたか？',
        );
    }

    /**
     * メール本文
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.pickup_confirmation',
            with: [
                'item' => $this->item,
                'url'  => $this->url,
            ],
        );
    }

    /**
     * 添付ファイルなし
     */
    public function attachments(): array
    {
        return [];
    }
}
