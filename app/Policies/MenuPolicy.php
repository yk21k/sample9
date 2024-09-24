<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;


class MenuPolicy
{
    use HandlesAuthorization;

    // public function before($user, $ability)
    // {
    //     if ($user->hasRole('admin')) {
    //         return true;
    //     }
    // }

    public function before(User $user, string $ability): bool|null
    {
        // if($user->hasRole('admin')){
        //     return true;
        // }

            if ($user->hasRole('admin')) {
                return true;
            }
         
            return null;

        // if ($user->hasRole('admin')) {
        //     return true;
        // }
    }

    public function browse(User $user, Menu $menu): bool|null
    {
        if ($user->hasRole('seller' || 'admin')) {
                return true;
            }
         
            return null;

        // return $user->hasRole('seller');

    }

    public function read(User $user, Menu $menu)
    {
        return $user->id == $menu->user_id;
    }

    public function edit(User $user, Menu $menu)
    {
        return $user->id == $menu->user_id;
    }

    public function add(User $user, Menu $menu)
    {
        return $user->id == $menu->user_id;

    }

    public function delete(User $user, Menu $menu)
    {
        return $user->id == $menu->user_id;
    }

}
