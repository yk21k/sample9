<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuctionEndedMail;

class SendAuctionEndedMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:send-ended-mail {auction_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'オークション終了時に最高入札者にメールを送信する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $auctionId = $this->argument('auction_id');

        $auction = Auction::find($auctionId);

        if (!$auction) {
            $this->error("オークションID {$auctionId} が見つかりません。");
            return;
        }

        if (now()->lessThan($auction->end)) {
            $this->info("オークションはまだ終了していません。");
            return;
        }

        $winner = Bid::where('auction_id', $auction->id)
                     ->orderByDesc('amount')
                     ->first();

        if (!$winner || !$winner->user || !$winner->user->email) {
            $this->error('最高入札者の情報が不足しています。');
            return;
        }

        Mail::to($winner->user->email)->send(new AuctionEndedMail($auction, $winner->user));

        $this->info("最高入札者 {$winner->user->name}（{$winner->user->email}）にメールを送信しました。");
    }
}

