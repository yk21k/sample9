<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\LicenseExpiryNotification;
use App\Events\LicenseExpiringSoon;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendLicenseExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_expiry_notification_email_is_sent()
    {
        Mail::fake();

        $shop = Shop::factory()->create([
            'email' => 'test@example.com',
        ]);

        event(new LicenseExpiringSoon($shop));

        Mail::assertSent(LicenseExpiryNotification::class, function ($mail) use ($shop) {
            return $mail->hasTo($shop->email);
        });
    }
}
