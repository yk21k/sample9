<?php

namespace App\Observers;

use App\Models\Desplay;

class DesplayObserver
{
    /**
     * Handle the Desplay "created" event.
     */
    public function created(Desplay $desplay): void
    {
        //
        $desplay->generate_static();
    }

    /**
     * Handle the Desplay "updated" event.
     */
    public function updated(Desplay $desplay): void
    {
        //
        
       
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
