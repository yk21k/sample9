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

        DB::transaction(function() use ($request,$product){

            $queue = ProductReviewQueue::where('product_id',$product->id)
            ->lockForUpdate()
            ->first();

            if(!$queue || $queue->reviewer_id !== auth()->id()){
                abort(403,'審査担当ではありません');
            }

            $action = $request->action;

            $status = match($action){

                'approve' => 'approved',
                'reject' => 'rejected',
                'fix' => 'need_fix',

            };

            ProductReviewLog::create([

                'product_id'=>$product->id,
                'reviewer_id'=>auth()->id(),
                'action'=>$action,
                'comment'=>$request->comment

            ]);

            $product->update([

                'review_status'=>$status,
                'status'=> $status === 'approved' ? 1 : 0,
                'review_comment'=>$request->comment,
                'reviewed_by'=>auth()->id(),
                'reviewed_at'=>now()

            ]);

            $queue->update([

                'status'=>$status,
                'reviewed_at'=>now()

            ]);

        });

        return back()->with('success','審査完了');
    }


    public function show(Request $request, $id)
    {
        $product = Product::with('shop')->findOrFail($id);

        $product_movies = [];

        $product_movies = json_decode($product->movie, true);
        // dd($product_movies);

        $shop   = $product->shop;

        $seller = $product->shop;

        $salesCount = SubOrder::where('seller_id', $seller->id)
            ->where('status','completed')
            ->count();

        $rating = Fovorite::where('shop_id',$seller->id)->avg('wants');

        $violations = ProductViolation::where('user_id', $seller->id)
                ->latest()
                ->take(10)
                ->get();

        $logs = ProductReviewLog::where('product_id',$product->id)->latest()->get(); 

        $queue = ProductReviewQueue::where('product_id',$product->id)->first();

        if(!$queue){
            return back()->with('error','審査キューが存在しません');
        }

        if(

            $queue->status === 'reviewing' &&
            $queue->reviewer_id !== auth()->id()
        ){

            return back()->with('error','他の管理者が審査中です');

        } 

        $lastVersion = $product->versions()->latest()->first();

        $before = $lastVersion?->before_data ?? [];
        $after  = $lastVersion?->after_data ?? [];

        // ★ここが今回の原因修正
        $before = is_string($before) ? json_decode($before, true) : $before;
        $after  = is_string($after) ? json_decode($after, true) : $after;

        $diff = [];

        foreach(array_keys(array_merge($before, $after)) as $field){

            $old = $before[$field] ?? null;
            $value = $after[$field] ?? null;

            if($old != $value){

                $diff[$field] = [
                    'before' => $old,
                    'after'  => $value
                ];
            }
        }

        // dd($product->shop_id, $seller->id, $salesCount, $rating, $violations, $logs, $queue, $diff); 

        return view('admin.product_review.show',[
            'product' => $product,
            'seller' => $seller,
            'shop' => $shop,
            'salesCount' => $salesCount,
            'rating' => round($rating,1),
            'violations' => $violations,
            'logs' => $logs,
            'queue' => $queue,
            'diff' => $diff,
            'product_movies' => $product_movies
        ]);

    }

    public function approve(Product $product)
    {
        DB::transaction(function() use ($product){

            $queue = ProductReviewQueue::where('product_id',$product->id)
            ->lockForUpdate()
            ->first();

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

        return back()->with('success','承認しました');
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
                'comment'=>$request->review_comment
            ]);

        });

        return back()->with('error','否認しました');
    }

    public function fix(Request $request, Product $product)
    {
        $product->update([
            'status'=>0,
            'review_status'=>'need_fix',
            'review_comment'=>$request->review_comment
        ]);

        ProductReviewQueue::where('product_id',$product->id)
        ->update([
            'status'=>'need_fix'
        ]);

        ProductReviewLog::create([
            'product_id'=>$product->id,
            'reviewer_id'=>auth()->id(),
            'action'=>'revision',
            'comment'=>$request->review_comment
        ]);

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

}
