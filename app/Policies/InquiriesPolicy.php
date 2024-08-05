<?php

namespace App\Policies;

use App\Models\Inquiries;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;


class InquiriesPolicy
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

    public function read(User $user, Inquiries $inquiries)
    {
        return $user->shop->id == $inquiries->shop_id;
    }

    public function edit(User $user, Inquiries $inquiries)
    {
        return $user->shop->id == $inquiries->shop_id;
    }

    public function add(User $user)
    {
        //
    }

    public function delete(User $user, Inquiries $inquiries)
    {
        return $user->shop->id == $inquiries->shop_id;
    }


}
