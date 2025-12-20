<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PickupOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otpCode;
    public $expiresAt;

    public function __construct($otpCode, $expiresAt)
    {
        $this->otpCode = $otpCode;
        $this->expiresAt = $expiresAt;
    }

    public function build()
    {
        return $this->subject('【受取画面遷移用のワンタイムパスワード】')
                    ->markdown('mail.pickup.pickup_otp');
    }
}
