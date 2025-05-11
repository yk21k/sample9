<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuctionEndedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $auction;
    public $winner;

    /**
     * Create a new message instance.
     */
    public function __construct($auction, $winner)
    {
        $this->auction = $auction;
        $this->winner = $winner;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'オークション終了のお知らせ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.auction.auction_ended',
            with: [
                'auction' => $this->auction,
                'winner' => $this->winner,
            ]
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
