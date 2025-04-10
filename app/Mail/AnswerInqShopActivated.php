<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\CustomerInquiry;


// use App\Models\User;

class AnswerInqShopActivated extends Mailable
{
    use Queueable, SerializesModels;

    // public $inquiries;
    // /**
    //  * Create a new message instance.
    //  */
    // public function __construct(Inquiries $inquiries)
    // {
    //     // $this->forInq = $inquiries;
    //     // $this->inqForShop = $inquiries;
    //     // $this->shop = $inquiries;
    //     // $this->shopAd = $inquiries;
    //     // $this->inqUser = $inquiries;
    //     // $this = $inquiries;
    // }
    public function __construct(
        public CustomerInquiry $customerInquiry,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Answer Inq Shop Activated',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.customer.answer-inquiry-shop',
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