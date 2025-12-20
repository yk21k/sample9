<?php

namespace App\Mail;

use App\Models\PickupOrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class StorePickupCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public PickupOrderItem $item;

    /**
     * Create a new message instance.
     */
    public function __construct(PickupOrderItem $item)
    {
        $this->item = $item;
    }

    /**
     * 件名
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【受け渡し完了】商品の受け渡しが完了しました（店舗からの通知）',
        );
    }

    /**
     * 本文（Markdown）
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.pickup.store_completed',
            with: [
                'item' => $this->item,
                'order' => $this->item->order,
                'user' => $this->item->order->user,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
