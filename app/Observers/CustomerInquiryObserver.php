<?php

namespace App\Observers;

use App\Models\CustomerInquiry;
use App\Mail\AnswerInqActivated;
use Illuminate\Support\Facades\Mail;

class CustomerInquiryObserver
{
    /**
     * Handle the CustomerInquiry "created" event.
     */
    public function created(CustomerInquiry $customerInquiry): void
    {
        //
    }

    /**
     * Handle the CustomerInquiry "updated" event.
     */
    public function updated(CustomerInquiry $customerInquiry): void
    {

        // check if active column is changed from inactive to active

        // dd($customerInquiry->status, $customerInquiry->getOriginal('status'));  ############## ####

        if($customerInquiry->getOriginal('status') == false && $customerInquiry->status == true){
            //dd('shop made active');
            // send mail to customer 
            // dd($customerInquiry->inqUser->email);
            Mail::to($customerInquiry->inqUser->email)->send(new AnswerInqActivated($customerInquiry));

            
        }else{
        //     dd('shop changed to inactive');
        }
        
    }

    /**
     * Handle the CustomerInquiry "deleted" event.
     */
    public function deleted(CustomerInquiry $customerInquiry): void
    {
        //
    }

    /**
     * Handle the CustomerInquiry "restored" event.
     */
    public function restored(CustomerInquiry $customerInquiry): void
    {
        //
    }

    /**
     * Handle the CustomerInquiry "force deleted" event.
     */
    public function forceDeleted(CustomerInquiry $customerInquiry): void
    {
        //
    }
}
