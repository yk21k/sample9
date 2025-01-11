<?php

namespace App\Policies;

use App\Models\ShopProf;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopProfPolicy
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


    public function read(User $user, ShopProf $shopProf)
    {
        if (empty($shopProf->shop)) {
            return false;
        }

        return $user->id == $shopProf->shop->user_id;
    }

    /**
     * Determine whether the user can update the Product.
     *
     * @param  \App\User  $user
     * @param  \App\Product  $product
     * @return mixed
     */
    public function edit(User $user, ShopProf $shopProf)
    {
        if(empty($shopProf->shop)) {
            return false;
        }

        return $user->id == $shopProf->shop->user_id;
    }


    /**
     * Determine whether the user can create Products.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function add(User $user)
    {
        return $user->hasRole('seller');
    }

    /**
     * Determine whether the user can delete the Product.
     *
     * @param  \App\User  $user
     * @param  \App\Product  $product
     * @return mixed
     */
    public function delete(User $user, ShopProf $shopProf)
    {
        if (empty($shopProf->shop)) {
            return false;
        }

        return $user->id == $shopProf->shop->user_id;
    }
}
