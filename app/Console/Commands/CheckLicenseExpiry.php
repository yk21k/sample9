<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Shop;
use App\Events\LicenseExpiringSoon;
use Carbon\Carbon;

class CheckLicenseExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-license-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check license expirations and notify shop';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = Carbon::now()->addWeek()->startOfDay();
        $shops =Shop::whereDate('license_expiry', $targetDate)->get();
        foreach ($shops as $shop) 
        {   
            // イベントが発火する前にログを出力
            $this->info('Triggering LicenseExpiringSoon event for shop: ' . $shop->name);
            \Log::info("Triggering LicenseExpiringSoon event for shop: {$shop->name}");
            // イベント発火
            event(new LicenseExpiringSoon($shop));
        }
        $this->info("Checked and notified Shops with licenses expiring on {$targetDate->toDateString()}");
    }
}
