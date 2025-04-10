<?php

namespace App\Observers;

use App\Models\Inquiries;
use App\Mail\AnswerInqActivated;
use Illuminate\Support\Facades\Mail;

class InquiryObserver
{
    /**
     * Handle the Inquiries "created" event.
     */
    public function created(Inquiries $inquiries): void
    {
        //
    }

    /**
     * Handle the Inquiries "updated" event.
     */
    public function updated(Inquiries $inquiries): void
    {
        // dd($inquiries->, $inquiries->getOriginal('status'));
            // dd($inquiries->shop_id);

        // Check if active column is changed from inactive to active

        if($inquiries->getOriginal('answers')){
            // dd('A Store representative has Responded');
            // send to mail customer
            // dd($inquiries);
            Mail::to($inquiries->inqUser)->send(new AnswerInqActivated($inquiries));

        }else{
            // dd('The Store representative has Not Responded Yet.');
        }
        

    }

    /**
     * Handle the Inquiries "deleted" event.
     */
    public function deleted(Inquiries $inquiries): void
    {
        //
    }

    /**
     * Handle the Inquiries "restored" event.
     */
    public function restored(Inquiries $inquiries): void
    {
        //
    }

    /**
     * Handle the Inquiries "force deleted" event.
     */
    public function forceDeleted(Inquiries $inquiries): void
    {
        //
    }
}
