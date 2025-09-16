<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Mails;
use App\Models\Shop;

use App\Models\ShopCoupon;


class SendMailReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $mails;
    public $subOrder;
    public $shop;
    public $coupon;
    public $campaign;
    public $productIds;
    public $items;
    public $items_cal;

        /**
     * Create a new message instance.
     */
    public function __construct(Mails $mails, $subOrder, $shop, $coupon = null, $campaign = null, $productIds, $items, $items_cal)
    {
        $this->mails = $mails;
        $this->subOrder = $subOrder;
        $this->shop = $shop;
        $this->coupon = $coupon;
        $this->campaign = $campaign;
        $this->productIds = $productIds;
        $this->items = $items;
        $this->items_cal = $items_cal;

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ご購入レシートのお知らせ',
        );
    }

    /**
     * Get the message content definition.
     */

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order.shop-receipt',
            with: [
                'mails'    => $this->mails,
                'subOrder' => $this->subOrder,
                'shop'     => $this->shop,
                'coupon'   => $this->coupon,
                'campaign' => $this->campaign,
                'productIds' => $this->productIds,
                'items' => $this->items,
                'items_with_pricing' => $this->items_cal, // ← ここを Blade 側変数名に合わせる,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
