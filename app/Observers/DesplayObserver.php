<?php

namespace App\Observers;

use App\Models\Desplay;
use App\Mail\ShopTemplateActivated;
use Illuminate\Support\Facades\Mail;

class DesplayObserver
{
    /**
     * Handle the Desplay "created" event.
     */
    public function created(Desplay $desplay): void
    {
            
        $desplay->generate_static2();

    }

    /**
     * Handle the Desplay "updated" event.
     */
    public function updated(Desplay $desplay): void
    {
        if($desplay->getOriginal('is_active') == false && $desplay->is_active == true){
            //dd('shop made active');
            // send mail to customer 
            // dd($desplay->shop->email);

            Mail::to($desplay->shop->email)->send(new ShopTemplateActivated($desplay));
            $desplay->generate_static();

        }else{
        //     dd('shop changed to inactive');
        }        
       
    }

    /**
     * Handle the Desplay "deleted" event.
     */
    public function deleted(Desplay $desplay): void
    {

    }

    public function deleting(Desplay $desplay): void
    {
        //
        
    }

    /**
     * Handle the Desplay "restored" event.
     */
    public function restored(Desplay $desplay): void
    {
        //

    }

    /**
     * Handle the Desplay "force deleted" event.
     */
    public function forceDeleted(Desplay $desplay): void
    {
        //

    }
}
