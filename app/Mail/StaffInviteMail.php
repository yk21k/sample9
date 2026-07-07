<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $member;
    public $url;

    public function __construct($member, $url)
    {
        $this->member = $member;
        $this->url = $url;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'スタッフ招待',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'sellers.shop_members.emails.staff_invite',
            with: [
                'member' => $this->member,
                'url' => $this->url,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
