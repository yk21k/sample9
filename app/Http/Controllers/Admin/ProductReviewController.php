<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\SubOrder;
use App\Models\Fovorite;
use App\Models\ProductViolation;
use App\Models\ProductReviewQueue;
use App\Models\ProductReviewLog;

use App\Jobs\AnalyzeProductImageJob;

use Illuminate\Support\Facades\Log;

use App\Notifications\ProductFixRequestNotification;

use Illuminate\Support\Facades\DB;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class ProductReviewController extends VoyagerBaseController
{

    public function index(Request $request)
    {
        $queues = ProductReviewQueue::with('product')
            ->whereIn('status',['pending','reviewing'])
            ->latest()
            ->paginate(20);

        return view('admin.product_review.index',compact('queues'));
    }

    public function review(Request $request,$id)
    {
        $product = Product::findOrFail($id);

        $status = null;

        DB::transaction(function() use ($request,$product, &$status){

            $queue = ProductReviewQueue::where('product_id',$product->id)
                ->lockForUpdate()
                ->first();

            if(!$queue || $queue->reviewer_id !== auth()->id()){
                abort(403,'審査担当ではありません');
            }

            $action = $request->action;

            $status = match($action){
                'approve' => 'approved',
                'reject'  => 'rejected',
                'fix'     => 'need_fix',
            };

            $fixFields = json_decode($request->fix_fields, true);

            ProductReviewLog::create([
                'product_id'  => $product->id,
                'reviewer_id' => auth()->id(),
                'action'      => $action,
                'comment'     => $request->review_comment,
                'fix_fields'  => $fixFields,
            ]);

            $product->update([
                'review_status'  => $status,
                'status'         => $status === 'approved' ? 1 : 0,
                'review_comment' => $request->review_comment,
                'reviewed_by'    => auth()->id(),
                'reviewed_at'    => now()
            ]);

            $queue->update([
                'status'      => $status,
                'reviewed_at' => now(),
                'fix_fields'  => $fixFields
            ]);

        });

        // =========================
        // 🔥 通知（ここ！！）
        // =========================
        if($status === 'need_fix'){

            // 念のためnullチェック
            if($product->user){
                $product->user->notify(
                    new ProductFixRequestNotification($product)
                );
            }
        }

        return back()->with('success','審査完了');
    }

    public function show(Request $request, $id)
    {
        $product = Product::with('shop')->findOrFail($id);

        $product_movies = json_decode($product->movie, true) ?? [];

        $shop   = $product->shop;
        $seller = $product->shop;

        $salesCount = SubOrder::where('seller_id', $seller->id)
            ->where('status','completed')
            ->count();

        $rating = Fovorite::where('shop_id',$seller->id)->avg('wants');

        $violations = ProductViolation::where('user_id', $seller->id)
            ->latest()->take(10)->get();

        $logs = ProductReviewLog::where('product_id',$product->id)
            ->latest()->get(); 

        // =========================
        // 🔥 審査ロック処理（重要）
        // =========================
        $queue = DB::transaction(function() use ($product){

            $queue = ProductReviewQueue::where('product_id',$product->id)
                ->lockForUpdate()
                ->first();

            if(!$queue){
                abort(404);
            }

            // =========================
            // 🔥 他人ロック
            // =========================
            if(
                $queue->status === 'reviewing'
                && $queue->reviewer_id
                && $queue->reviewer_id !== auth()->id()
            ){
                abort(403, '他の管理者が審査中です');
            }

            // =========================
            // 🔥 ロック取得（重要）
            // =========================

            // ★ reviewerが空 → 自分にする
            if(!$queue->reviewer_id){

                $queue->update([
                    'status' => 'reviewing',
                    'reviewer_id' => auth()->id(),
                    'review_started_at' => now()
                ]);
            }

            // ★ reviewerが自分なら時間更新（任意）
            elseif($queue->reviewer_id === auth()->id()){

                $queue->update([
                    'review_started_at' => now()
                ]);
            }

            return $queue;
        });

        // =========================
        // 🔥 AI審査ディスパッチ（ここだけ追加）
        // =========================
        if($queue->status === 'reviewing' && $queue->ai_status !== 'done'){
            AnalyzeProductImageJob::dispatch($product);
        }

        // =========================
        // 差分ロジック（完全版）
        // =========================

        $lastVersion = $product->versions()->latest()->first();

        $before = $lastVersion?->before_data ?? [];
        $after  = $lastVersion?->after_data ?? [];

        $before = is_string($before) ? json_decode($before, true) : $before;
        $after  = is_string($after) ? json_decode($after, true) : $after;

        $reviewTimeoutMinutes = 5;
        $remainingSeconds = null;

        // ★ 新規対応
        if(empty($after)){
            $after = $product->toArray();
        }

        $diff = [];

        foreach(array_keys($after) as $field){

            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;

            // ★ 差分 or 新規は全部表示
            if($old != $new || empty($before)){
                $diff[$field] = [
                    'before' => $old,
                    'after'  => $new
                ];
            }
        }

        if($queue->status === 'reviewing' && $queue->review_started_at){

            $elapsed = now()->diffInSeconds($queue->review_started_at);

            $remainingSeconds = max(
                0,
                ($reviewTimeoutMinutes * 60) - $elapsed
            );
        }

        $reviewer = null;

        if($queue->reviewer_id){
            $reviewer = User::find($queue->reviewer_id);
        }

        $latestFix = ProductReviewLog::where('product_id',$product->id)
            ->where('action','fix')
            ->latest()
            ->first();

        $fixFields = [];

        if($latestFix && $latestFix->fix_fields){
            $fixFields = json_decode($latestFix->fix_fields, true);
        }


        return view('admin.product_review.show2',[
            'product' => $product,
            'seller' => $seller,
            'shop' => $shop,
            'salesCount' => $salesCount,
            'rating' => round($rating,1),
            'violations' => $violations,
            'logs' => $logs,
            'queue' => $queue,
            'diff' => $diff,
            'product_movies' => $product_movies,
            'remainingSeconds' => $remainingSeconds,
            'reviewer' => $reviewer,
            'fixFields' => $fixFields
        ]);
    }

    public function approve(Product $product)
    {
        DB::transaction(function() use ($product){

            $queue = ProductReviewQueue::where('product_id',$product->id)
            ->lockForUpdate()
            ->first();
            // dd($queue->reviewer_id, auth()->id());
            if(!$queue || $queue->reviewer_id !== auth()->id()){
                abort(403);
            }

            $product->update([
                'status' => 1,
                'review_status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now()
            ]);

            $queue->update([
                'status' => 'approved',
                'reviewed_at' => now()
            ]);

            ProductReviewLog::create([
                'product_id'=>$product->id,
                'reviewer_id'=>auth()->id(),
                'action'=>'approve',
                'comment'=>'商品を承認'
            ]);

        });
        return back()->with('success', '承認しました');

    }

    public function reject(Request $request, Product $product)
    {
        DB::transaction(function() use ($request,$product){

            $queue = ProductReviewQueue::where('product_id',$product->id)
            ->lockForUpdate()
            ->first();

            if(!$queue || $queue->reviewer_id !== auth()->id()){
                abort(403);
            }

            $product->update([
                'status'=>0,
                'review_status'=>'rejected',
                'review_comment'=>$request->review_comment,
                'reviewed_by'=>auth()->id(),
                'reviewed_at'=>now()
            ]);

            $queue->update([
                'status'=>'rejected',
                'reviewed_at'=>now()
            ]);

            ProductReviewLog::create([
                'product_id'=>$product->id,
                'reviewer_id'=>auth()->id(),
                'action'=>'reject',
                'comment'=>'商品を却下しました'
            ]);

        });
        return back()->with('error', '却下しました');
    }

    public function fix(Request $request, Product $product)
    {
        // dd($request->all());
        // dd($product->id);

        $decoded = json_decode($request->fix_fields, true);

        $request->merge([
            'fix_fields' => $decoded
        ]);

        $request->validate([
            'review_comment' => 'required|string|max:2000',
            'fix_fields' => 'required|array|min:1'
        ]);

        \Log::info('fix start', [

            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'fix_fields' => $request->fix_fields
        ]);

        DB::transaction(function() use ($request, $product){

            $product->update([
                'status'=>0,
                'review_status'=>'need_fix',
                'review_comment'=>$request->review_comment,
                'reviewed_by'=>auth()->id(),
                'reviewed_at'=>now()
            ]);

            ProductReviewQueue::where('product_id',$product->id)
            ->update([
                'status'=>'need_fix',
                'reviewer_id'=>null,
                'review_started_at'=>null,
                'reviewed_at'=>now(),
                'fix_fields' => json_encode($request->fix_fields),
                'comment' => $request->review_comment,
            ]);

            ProductReviewLog::create([
                'product_id'=>$product->id,
                'reviewer_id'=>auth()->id(),
                'action'=>'fix',
                'comment'=>$request->review_comment,
                'fix_fields'=>json_encode($request->fix_fields) // 🔥 追加
            ]);

        });

        return back()->with('warning','修正依頼を送りました');
    }

    public function next()
    {

        // ① まず自分の審査中を確認
        $myQueue = ProductReviewQueue::where('reviewer_id',auth()->id())
            ->where('status','reviewing')
            ->first();

        if($myQueue){
            return redirect()->route(
                'product.review.show',
                $myQueue->product_id
            );
        }

        // ② 新しい審査を取得
        $queue = DB::transaction(function(){

            $queue = ProductReviewQueue::where('status','pending')
                ->orderByDesc('risk_score')
                ->lockForUpdate()
                ->first();

            if(!$queue){
                return null;
            }

            $queue->update([
                'status' => 'reviewing',
                'reviewer_id' => auth()->id(),
                'review_started_at' => now()
            ]);

            return $queue;

        });

        if(!$queue){
            return view('admin.product_review.empty');
        }

        return redirect()->route(
            'product.review.show',
            $queue->product_id
        );
    }

    public function dashboard()
    {
        $pending = ProductReviewQueue::where('status','pending')->count();

        $reviewing = ProductReviewQueue::where('status','reviewing')->count();

        $approved = ProductReviewQueue::where('status','approved')->count();

        $rejected = ProductReviewQueue::where('status','rejected')->count();

        $needFix = ProductReviewQueue::where('status','need_fix')->count();

        return view('admin.product_review.dashboard',[
            'pending'=>$pending,
            'reviewing'=>$reviewing,
            'approved'=>$approved,
            'rejected'=>$rejected,
            'needFix'=>$needFix
        ]);
    }

    public function requestReview(Product $product)
    {
        $user = auth()->user();

        // 自分の商品だけ
        if ($product->shop->user_id !== $user->id) {
            abort(403);
        }

        // 1日制限
        $limit = 5;

        $count = ProductReviewQueue::where('user_id', $user->id)
            ->whereDate('requested_at', today())
            ->count();

        if ($count >= $limit) {
            return back()->with('error', '本日の審査依頼は上限に達しました');
        }

        // 既に審査中ならNG
        $exists = ProductReviewQueue::where('product_id', $product->id)
            ->whereIn('status', ['pending','reviewing'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'すでに審査依頼中です');
        }

        // 🔥 登録（ここ重要）
        ProductReviewQueue::updateOrCreate(
            ['product_id' => $product->id],
            [
                'user_id' => $user->id,
                'status' => 'pending',
                'requested_at' => now()
            ]
        );
        // AIジョブをディスパッチ
        AnalyzeProductImageJob::dispatch($product);

        return back()->with('success','審査（AI審査を含む）依頼しました');
    }


}
