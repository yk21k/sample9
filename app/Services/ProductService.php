<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ActivityLog;
use App\Models\ShopMember;

class ProductService
{
    /**
     * 商品更新＋ログ保存
     */
    public function updateWithLog(Product $product, array $data, $user, $action = 'updated')
    {

        // dd($data);

        // 🔥 変更前
        $before = $product->getOriginal();

        // 🔥 更新
        $product->update($data);

        $product->refresh();

        // dd([
        //     'status' => $product->status,
        //     'review_status' => $product->review_status,
        // ]);

        // 🔥 更新後
        $after = $product->fresh()->toArray();

        // 🔥 差分取得
        $changes = $this->getDirtyDiff($before, $after);

        // 🔥 ログ保存
        if (!empty($changes)) {

            $member = ShopMember::where('user_id', $user->id)
                ->where('shop_id', $product->shop_id)
                ->first();

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'target_type' => 'Product',
                'target_id' => $product->id,
                'changes' => $changes,
                'role' => $member->role ?? 'admin',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $product;
    }

    /**
     * 差分だけ取得
     */
    private function getDirtyDiff(array $before, array $after): array
    {
        $diff = [];

        foreach ($after as $key => $value) {

            // beforeにないものは無視
            if (!array_key_exists($key, $before)) {
                continue;
            }

            // 値が違うものだけ
            if ($before[$key] != $value) {
                $diff[$key] = [
                    'before' => $before[$key],
                    'after'  => $value,
                ];
            }
        }

        return $diff;
    }

    public function approveByManager(Product $product, $user, $comment = null)
    {
        // 状態チェック
        if ($product->review_status !== Product::STATUS_MANAGER_PENDING) {
            abort(400, '状態が不正です');
        }

        $product->update([
            'review_status' => Product::STATUS_PENDING,
            'approved_by'   => $user->id,
            'approved_at'   => now(),
            'review_comment'=> $comment,
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'manager_approved',
            'target_type' => 'Product',
            'target_id' => $product->id,
            'changes' => null,
            'role' => optional($user->shopMember)->role,
        ]);
    }

    public function rejectByManager(Product $product, $user, $comment)
    {
        if ($product->review_status !== Product::STATUS_MANAGER_PENDING) {
            abort(400, '状態が不正です');
        }

        $product->update([
            'review_status' => Product::STATUS_REJECTED,
            'review_comment'=> $comment,
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'manager_rejected',
            'target_type' => 'Product',
            'target_id' => $product->id,
            'changes' => [
                'comment' => $comment
            ],
            'role' => optional($user->shopMember)->role,
        ]);
    }
}