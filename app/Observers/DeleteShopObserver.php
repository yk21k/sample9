<?php

namespace App\Observers;

use App\Models\DeleteShop;
use App\Mail\DeleteShopActivated;
use Illuminate\Support\Facades\Mail;

class DeleteShopObserver
{
    /**
     * Handle the DeleteShop "created" event.
     */
    public function created(DeleteShop $deleteShop): void
    {
        //
    }

    /**
     * Handle the DeleteShop "updated" event.
     */
    public function updated(DeleteShop $deleteShop): void
    {
        // dd($deleteShop->is_active, $deleteShop->getOriginal('is_active'));
        // dd($deleteShop);

        if($deleteShop->getOriginal('is_active') == false && $deleteShop->is_active == true){
            // dd($deleteShop->deleteShopp->email);
            // dd($deleteShop->deleteShopp);

            Mail::to($deleteShop->deleteShopp->email)->send(new DeleteShopActivated($deleteShop));

            $deleteShop->deleteShopp->setRole('user');
        }else{
            // dd('shop changed to active');
        }
    }

    /**
     * Handle the DeleteShop "deleted" event.
     */
    public function deleted(DeleteShop $deleteShop): void
    {
        //
    }

    /**
     * Handle the DeleteShop "restored" event.
     */
    public function restored(DeleteShop $deleteShop): void
    {
        //
    }

    /**
     * Handle the DeleteShop "force deleted" event.
     */
    public function forceDeleted(DeleteShop $deleteShop): void
    {
        //
    }
}
