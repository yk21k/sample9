<?php

namespace App\Policies;

use App\Models\Categories;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;


class CategoriesPolicy
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


    public function read(User $user, Categories $categories)
    {
        if (empty($categories->shop)) {
            return false;
        }

        return $user->id == $categories->shop->user_id;
    }

    /**
     * Determine whether the user can update the Categories.
     *
     * @param  \App\User  $user
     * @param  \App\Categories  $categories
     * @return mixed
     */
    public function edit(User $user, Categories $categories)
    {
        if(empty($categories->shop)) {
            return false;
        }

        return $user->id == $categories->shop->user_id;
    }


    /**
     * Determine whether the user can create Categoriess.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function add(User $user)
    {
        return $user->hasRole('seller');
    }

    /**
     * Determine whether the user can delete the Categories.
     *
     * @param  \App\User  $user
     * @param  \App\Categories  $categories
     * @return mixed
     */
    public function delete(User $user, Categories $categories)
    {
        if (empty($categories->shop)) {
            return false;
        }

        return $user->id == $categories->shop->user_id;
    }
}
