<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShopMember;

class ExpireShopMemberInvites extends Command
{
    /**
     * command名
     */
    protected $signature =
        'shop-members:expire-invites';

    /**
     * 説明
     */
    protected $description =
        '期限切れ招待を失効';

    /**
     * 実行
     */
    public function handle()
    {
        // ========================================
        // 🔥 期限切れ取得
        // ========================================
        $members = ShopMember::whereNotNull(
                'invite_token'
            )
            ->whereNotNull(
                'invite_expires_at'
            )
            ->where(
                'invite_expires_at',
                '<',
                now()
            )
            ->get();

        $count = 0;

        foreach ($members as $member) {

            // ========================================
            // 🔥 token削除
            // ========================================
            $member->update([

                'invite_token' => null,

            ]);

            $count++;

            // ========================================
            // 🔥 log
            // ========================================
            \Log::info(
                '招待失効',
                [
                    'member_id' => $member->id,
                    'email' => $member->email,
                ]
            );
        }

        $this->info(
            "{$count}件失効しました"
        );

        return Command::SUCCESS;
    }
}