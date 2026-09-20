<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductDraft;

class AuditLogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 監査ログ一覧
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = AuditLog::query();

        /*
        |--------------------------------------------------------------------------
        | user
        |--------------------------------------------------------------------------
        */
        if ($request->filled('user_id')) {
            $query->where(
                'user_id',
                $request->user_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | action
        |--------------------------------------------------------------------------
        */
        if ($request->filled('action')) {
            $query->where(
                'action',
                'like',
                '%' . $request->action . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | target
        |--------------------------------------------------------------------------
        */
        if ($request->filled('target_type')) {
            $query->where(
                'target_type',
                $request->target_type
            );
        }

        if ($request->filled('target_id')) {
            $query->where(
                'target_id',
                $request->target_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */
        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->product_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Draft
        |--------------------------------------------------------------------------
        */
        if ($request->filled('draft_id')) {
            $query->where(
                'draft_id',
                $request->draft_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Event Group
        |--------------------------------------------------------------------------
        */
        if ($request->filled('event_group')) {
            $query->where(
                'event_group',
                $request->event_group
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */
        if ($request->filled('from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );
        }

        if ($request->filled('to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );
        }

        $logs = $query
            ->with(['user', 'shop', 'product', 'draft',])
            ->latest()
            ->paginate(30)
            ->appends($request->all());


        return view(
            'admin.audit_logs.index',
            compact('logs')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 詳細
    |--------------------------------------------------------------------------
    */
    public function show(AuditLog $auditLog)
    {
        /*
        |--------------------------------------------------------------------------
        | 同一イベント取得
        |--------------------------------------------------------------------------
        */
        $relatedLogs = AuditLog::where(
            'event_group',
            $auditLog->event_group
        )
        ->orderBy('created_at')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | 商品取得
        |--------------------------------------------------------------------------
        */
        $productId = $relatedLogs
            ->pluck('product_id')
            ->filter()
            ->first();

        if (!$productId) {

            $draftId = $relatedLogs
                ->pluck('draft_id')
                ->filter()
                ->first();

            if ($draftId) {

                $draft = ProductDraft::find($draftId);

                $productId = $draft?->product_id;

            }
        }

        $product = $productId
            ? Product::find($productId)
            : null;

        return view(
            'admin.audit_logs.show',
            compact(
                'auditLog',
                'relatedLogs',
                'product'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 商品タイムライン
    |--------------------------------------------------------------------------
    */
    public function productTimeline(Product $product)
    {
        $draftIds = ProductDraft::where(
            'product_id',
            $product->id
        )->pluck('id');

        $logs = AuditLog::query()

            ->where(function ($q) use ($product, $draftIds) {

                $q->where('product_id', $product->id)
                  ->orWhereIn('draft_id', $draftIds);

            })

            ->with('user')

            ->orderBy('created_at')

            ->get();

        $eventGroups = $logs
            ->groupBy('event_group');    

        return view(
            'admin.audit_logs.timeline',
            compact(
                'product',
                'logs',
                'eventGroups'
            )
        );
    }




}