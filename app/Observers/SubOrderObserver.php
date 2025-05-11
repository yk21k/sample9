<?php

namespace App\Observers;

use App\Models\SubOrder;
use App\Models\SubOrdersArrivalReport;

use App\Mail\SubOrderCompletedMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class SubOrderObserver
{
    /**
     * Handle the SubOrder "created" event.
     */
    public function created(SubOrder $subOrder): void
    {
        //
    }

    /**
     * Handle the SubOrder "updated" event.
     */
    public function updated(SubOrder $subOrder): void
    {

        // status が 'completed' に変更されたかどうか確認
        if ($subOrder->isDirty('status') && $subOrder->status === 'completed') {
            $user = $subOrder->arrivalUser; // SubOrder モデルに リレーションがある

            if ($user && $user->email) {
                Mail::to($user->email)->send(new SubOrderCompletedMail($subOrder));

                Log::debug('Saving confirmation_deadline', [
                    'sub_order_id' => $subOrder->id,
                    'deadline' => Carbon::now()->addDays(7)->toDateTimeString()
                ]);

                try {
                    SubOrdersArrivalReport::updateOrCreate(
                        ['sub_order_id' => $subOrder->id],
                        ['confirmation_deadline' => Carbon::now()->addDays(7)]
                    );
                } catch (\Exception $e) {
                    Log::error('SubOrdersArrivalReport 保存エラー', [
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Handle the SubOrder "deleted" event.
     */
    public function deleted(SubOrder $subOrder): void
    {
        //
    }

    /**
     * Handle the SubOrder "restored" event.
     */
    public function restored(SubOrder $subOrder): void
    {
        //
    }

    /**
     * Handle the SubOrder "force deleted" event.
     */
    public function forceDeleted(SubOrder $subOrder): void
    {
        //
    }
}
