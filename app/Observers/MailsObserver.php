<?php

namespace App\Observers;

use App\Models\Mails;
use App\Mail\SendMail;
use App\Mail\SendMailCampaign;
use App\Mail\SendMailGreeting;
use Illuminate\Support\Facades\Mail;


class MailsObserver
{
    /**
     * Handle the Mails "created" event.
     */
    public function created(Mails $mails): void
    {
        // dd($mails->template);
        if($mails->template == "template1"){

            Mail::to($mails->mail)->send(new SendMail($mails));

        }elseif($mails->template == "template2"){

            Mail::to($mails->mail)->send(new SendMailCampaign($mails));

        }else{

            Mail::to($mails->mail)->send(new SendMailGreeting($mails));

        }
    }

    /**
     * Handle the Mails "updated" event.
     */
    public function updated(Mails $mails): void
    {
        // echo print_r($mails->id);die;
        
        if($mails->getOriginal('id') == " " && isset($mails->id) == true){
            dd('mail active');
            // send mail to customer 

            // Mail::to($shop->owner)->send(new ShopActivated($shop));

            // change role from customer to seller
            // $shop->owner->setRole('seller');
        }else{
            dd('mail inactive');
        }
    }

    /**
     * Handle the Mails "deleted" event.
     */
    public function deleted(Mails $mails): void
    {
        //
    }

    /**
     * Handle the Mails "restored" event.
     */
    public function restored(Mails $mails): void
    {
        //
    }

    /**
     * Handle the Mails "force deleted" event.
     */
    public function forceDeleted(Mails $mails): void
    {
        //
    }
}
