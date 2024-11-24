<?php

namespace App\Policies;

use App\Models\ShopCoupon;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;


class ShopCouponPolicy
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


    public function read(User $user, ShopCoupon $shopCoupon)
    {
        if (empty($shopCoupon->shop)) {
            return false;
        }

        return $user->id == $shopCoupon->shop->user_id;
    }

    /**
     * Determine whether the user can update the ShopCoupon.
     *
     * @param  \App\User  $user
     * @param  \App\ShopCoupon  $shopCoupon
     * @return mixed
     */
    public function edit(User $user, ShopCoupon $shopCoupon)
    {
        if(empty($shopCoupon->shop)) {
            return false;
        }

        return $user->id == $shopCoupon->shop->user_id;
    }


    /**
     * Determine whether the user can create ShopCoupon.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function add(User $user)
    {
    
        return $user->hasRole('seller');

        
    }

    /**
     * Determine whether the user can delete the ShopCoupon.
     *
     * @param  \App\User  $user
     * @param  \App\ShopCoupon  $shopCoupon
     * @return mixed
     */
    public function delete(User $user, ShopCoupon $shopCoupon)
    {
        if (empty($shopCoupon->shop)) {
            return false;
        }

        return $user->id == $shopCoupon->shop->user_id;
    }
}



