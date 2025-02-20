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


class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mails;
    /**
     * Create a new message instance.
     */
    public function __construct(Mails $mails)
    {
        $this->mails = $mails;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Send Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $formail_coupons = ShopCoupon::where('id', $this->mails->coupon_id)->first();
        $formail_shops = Shop::where('user_id', $this->mails->shop_id)->first();

        return new Content(
            markdown: 'mail.order.shop-coupon',
            with: ['formail_coupons' => $formail_coupons, 'formail_shops' => $formail_shops, ],

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
