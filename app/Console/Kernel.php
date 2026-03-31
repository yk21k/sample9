<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// ★ コマンド追加
use App\Console\Commands\ReviewReset;

class Kernel extends ConsoleKernel
{
    /**
     * Artisanコマンドの手動登録（←重要）
     */
    protected $commands = [
        ReviewReset::class,
    ];

    /**
     * スケジュール定義
     */
    protected function schedule(Schedule $schedule): void
    {
        // ライセンス期限チェック（毎日1時）
        $schedule->command('licenses:check-expiry')
            ->dailyAt('01:00');

        // QRコード生成（毎日0時）
        $schedule->job(new \App\Jobs\GenerateDailyQrCodeJob)
            ->dailyAt('00:00');

        // 🔥 審査ロック解除（5分ごと）
        $schedule->command('review:reset')
            ->everyFiveMinutes()
            ->withoutOverlapping() // 多重起動防止
            ->runInBackground();   // 他処理と並列実行
    }

    /**
     * コマンド自動読み込み
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}