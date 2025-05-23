<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;


class CampaignPolicy
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


    public function read(User $user, Campaign $campaign)
    {
        if (empty($campaign->shop)) {
            return false;
        }

        return $user->id == $campaign->shop->user_id;
    }

    /**
     * Determine whether the user can update the Campaign.
     *
     * @param  \App\User  $user
     * @param  \App\Campaign  $campaign
     * @return mixed
     */
    public function edit(User $user, Campaign $campaign)
    {
        if(empty($campaign->shop)) {
            return false;
        }

        return $user->id == $campaign->shop->user_id;
    }


    /**
     * Determine whether the user can create Campaign.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    // public function add(User $user)
    // {
    
    //     return $user->hasRole('seller');

        
    // }

    public function add(User $user)
    {
        // sellerでなければNG
        if (!$user->hasRole('seller')) {
            return false;
        }

        // SubOrderにshop_idが1件もない場合はNG
        $hasShopId = \App\Models\SubOrder::where('seller_id', $user->id)
            ->whereNotNull('seller_id') // shop_idがnullでないことを確認
            ->exists();

        return $hasShopId;
    }


    /**
     * Determine whether the user can delete the Campaign.
     *
     * @param  \App\User  $user
     * @param  \App\Campaign  $campaign
     * @return mixed
     */
    public function delete(User $user, Campaign $campaign)
    {
        if (empty($campaign->shop)) {
            return false;
        }

        return $user->id == $campaign->shop->user_id;
    }
}



