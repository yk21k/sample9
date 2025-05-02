<?php
// app/Listeners/SendLicenseExpiryNotification.php
namespace App\Listeners;

use App\Events\LicenseExpiringSoon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LicenseExpiryNotification;

class SendLicenseExpiryNotification
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\LicenseExpiringSoon  $event
     * @return void
     */
    public function handle(LicenseExpiringSoon $event)
    {
        \Log::info("Sending email to shop: {$event->shop->name}");

        // メールを同期的に送信
        Mail::to($event->shop->email)->send(new LicenseExpiryNotification($event->shop));

        \Log::info("Email sent successfully to: {$event->shop->email}");
    }
}


