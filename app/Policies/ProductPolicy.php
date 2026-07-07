<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Models\ShopMember;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * adminは全許可
     */
    public function before($user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    /**
     * 共通：メンバー取得
     */
    private function member(User $user, Product $product)
    {
        return ShopMember::where('shop_id', $product->shop_id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * 一覧表示（Voyager browse）
     */
    public function browse(User $user)
    {
        return ShopMember::where('user_id', $user->id)->exists();
    }

    /**
     * 閲覧（Voyager read / view）
     */
    public function read(User $user, Product $product)
    {
        return $this->member($user, $product) !== null;
    }

    public function view(User $user, Product $product)
    {
        return $this->read($user, $product);
    }

    /**
     * 作成（Voyager add）
     */
    public function add(User $user)
    {
        return ShopMember::where('user_id', $user->id)
            ->whereIn('role', ['owner', 'manager'])
            ->exists();
    }

    /**
     * 編集（Voyager edit）
     */
    public function edit(User $user, Product $product)
    {
        $member = $this->member($user, $product);

        return $member && $member->canEditProduct();
    }

    /**
     * 更新（API / Controller用）
     */
    public function update(User $user, Product $product)
    {
        return $this->edit($user, $product);
    }

    /**
     * 削除（ownerのみ）
     */
    public function delete(User $user, Product $product)
    {
        $member = $this->member($user, $product);

        return $member && $member->role === 'owner';
    }
}
