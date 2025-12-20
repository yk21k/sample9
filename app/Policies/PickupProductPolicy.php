<?php

namespace App\Policies;

use App\Models\PickupProduct;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class PickupProductPolicy
{
use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function browse(User $user)
    {
        return $user->hasRole('seller');
    }


    public function read(User $user, PickupProduct $pickupProduct)
    {
        if (empty($pickupProduct->shop)) {
            return false;
        }

        return $user->id == $pickupProduct->shop->user_id;
    }

    /**
     * Determine whether the user can update the PickupProduct.
     *
     * @param  \App\User  $user
     * @param  \App\PickupProduct  $pickupProduct
     * @return mixed
     */
    public function edit(User $user, PickupProduct $pickupProduct)
    {
        if(empty($pickupProduct->shop)) {
            return false;
        }

        return $user->id == $pickupProduct->shop->user_id;
    }


    /**
     * Determine whether the user can create PickupProducts.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function add(User $user)
    {
        return $user->hasRole('seller');
    }

    /**
     * Determine whether the user can delete the PickupProduct.
     *
     * @param  \App\User  $user
     * @param  \App\PickupProduct  $pickupProduct
     * @return mixed
     */
    public function delete(User $user, PickupProduct $pickupProduct)
    {
        if (empty($pickupProduct->shop)) {
            return false;
        }

        return $user->id == $pickupProduct->shop->user_id;
    }
}
