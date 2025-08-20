<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Carbon\Carbon;
use App\Models\Mails;
use App\Models\Campaign;
use App\Models\Shop;
use App\Models\User;

class SendMailCampaign extends Mailable
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
            subject: 'Send Mail Campaign',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        
        $formail_shops = Shop::where('user_id', $this->mails->shop_id)->first();
        
        // dd($formail_shops);

        $formail_campaigns = Campaign::where('shop_id', $formail_shops->id)
        ->whereDate('start_date', '<=', Carbon::today())
        ->whereDate('end_date', '>=', Carbon::today())
        ->get();

        
        // dd($formail_campaigns);
        
        return new Content(
            markdown: 'mail.order.shop-campaign',
            with: ['formail_campaigns' => $formail_campaigns, 'formail_shops' => $formail_shops, ],

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
