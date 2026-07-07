<?php

namespace App\Helpers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductDraft;
use App\Models\ProductEditDraft;
use Illuminate\Support\Str;


class Audit
{

    public static function log(
        string $action,
        $target = null,
        ?array $before = null,
        ?array $after = null,
        ?string $description = null,
        array $extra = []
    ): void {

        $user = auth()->user();

        $shopMember = $user?->shopMember;

        /*
        |--------------------------------------------------------------------------
        | 対象情報
        |--------------------------------------------------------------------------
        */
        $productId = null;
        $draftId = null;
        $shopId = null;

        if ($target instanceof Product) {

            $productId = $target->id;
            $shopId = $target->shop_id;

        } elseif ($target instanceof ProductDraft) {

            $draftId = $target->id;
            $productId = $target->product_id;
            $shopId = $target->shop_id;

        } elseif ($target instanceof \App\Models\ProductImageReview) {

            $draftId = $target->draft_id;

            $productId = optional($target->draft)->product_id;

            $shopId = optional($target->draft)->shop_id;

        }

        /*
        |--------------------------------------------------------------------------
        | Event Group
        |--------------------------------------------------------------------------
        */
        $eventGroup = $extra['event_group'] ?? (string) Str::uuid();

        unset($extra['event_group']);

        /*
        |--------------------------------------------------------------------------
        | 差分
        |--------------------------------------------------------------------------
        */
        $diff = self::diff($before, $after);

        /*
        |--------------------------------------------------------------------------
        | 保存
        |--------------------------------------------------------------------------
        */
        AuditLog::create(array_merge([

            /*
            |--------------------------------------------------------------------------
            | 実行者
            |--------------------------------------------------------------------------
            */
            'user_id' => $user?->id,

            'shop_id' => $shopId
                ?? $shopMember?->shop_id,

            'role' => $shopMember?->role
                ?? 'admin',

            /*
            |--------------------------------------------------------------------------
            | 対象
            |--------------------------------------------------------------------------
            */
            'product_id' => $productId,

            'draft_id' => $draftId,

            'event_group' => $eventGroup,

            'target_type' => $target
                ? class_basename($target)
                : null,

            'target_id' => $target?->id,

            /*
            |--------------------------------------------------------------------------
            | 操作
            |--------------------------------------------------------------------------
            */
            'action' => $action,

            'description' => $description,

            /*
            |--------------------------------------------------------------------------
            | データ
            |--------------------------------------------------------------------------
            */
            'before_data' => $before,

            'after_data' => $after,

            'diff_data' => $diff,

            /*
            |--------------------------------------------------------------------------
            | 接続情報
            |--------------------------------------------------------------------------
            */
            'ip' => request()->ip(),

            'user_agent' => request()->userAgent(),

        ], $extra));

        /*
        |--------------------------------------------------------------------------
        | Laravel Log
        |--------------------------------------------------------------------------
        */
        \Log::channel('daily')->info('[AUDIT]', [

            'action' => $action,

            'target' => $target
                ? class_basename($target)
                : null,

            'target_id' => $target?->id,

            'user_id' => $user?->id,

            'event_group' => $eventGroup,

        ]);
    }

    public static function diff(
        ?array $before,
        ?array $after
    ): array {

        $before = $before ?? [];
        $after = $after ?? [];

        $diff = [];

        $keys = array_unique(array_merge(

            array_keys($before),

            array_keys($after)

        ));

        foreach ($keys as $key) {

            $old = $before[$key] ?? null;

            $new = $after[$key] ?? null;

            if ($old !== $new) {

                $diff[$key] = [

                    'before' => $old,

                    'after' => $new,

                ];
            }
        }

        return $diff;
    }
}