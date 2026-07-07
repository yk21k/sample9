<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use App\Models\ShopMember;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    private function getMember(User $user, Shop $shop)
    {
        return ShopMember::where('shop_id', $shop->id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * 一覧
     */
    public function browse(User $user)
    {
        return $user->hasRole('seller');

    }

    /**
     * 閲覧（所属していればOK）
     */
    public function read(User $user, Shop $shop)
    {
        return $this->getMember($user, $shop) !== null;
    }

    /**
     * 編集（owner / managerのみ）
     */


    public function edit(User $user, Shop $shop)
    {
        $member = $this->getMember($user, $shop);

        return $member && in_array($member->role, ['owner', 'manager']);
    }

    /**
     * 作成
     */
    public function add(User $user)
    {
        return ShopMember::where('user_id', $user->id)
            ->whereIn('role', ['owner', 'manager'])
            ->exists();
    }

    /**
     * 削除（ownerのみ）
     */
    public function delete(User $user, Shop $shop)
    {
        $member = $this->getMember($user, $shop);

        // return $member && $member->isOwner();
        return $member && $member->isOwner();
    }

    /**
     * =========================
     * 共通処理
     * =========================
     */
    public function manageStaff(User $user, Shop $shop)
    {
        $member = $this->getMember($user, $shop);

        return $member && $member->canManageStaff();
    }

}
