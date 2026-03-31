<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductReviewQueue;
// use App\Jobs\RunAiReviewJob;
use App\Jobs\AnalyzeProductImageJob;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'price',
        'shipping_fee',
        'stock',
        'shop_id',
        'campaigns_rate1',
        'review_comment',
        'reviewed_by',
        'reviewed_at',
        'review_status'
    ];

    protected $guarded = ['id'];


    // protected $casts = 
    // [
    //     'product_attributes'=>'array'
    // ];

    protected static function booted()
    {
        static::saving(function ($product) {

            if (request()->has('product_attributes')) {
                $product->product_attributes = json_encode(request('product_attributes'));
            }

        });

        static::created(function ($product) {

            try {

                ProductReviewQueue::firstOrCreate(
                    ['product_id' => $product->id],
                    [
                        'user_id'    => $product->shop->user_id ?? auth()->id(),
                        'status'     => 'pending',
                        'risk_score' => 0
                    ]
                );

                // AnalyzeProductImageJob::dispatch($product);コントローラーで行う二重になっているため

            } catch (\Throwable $e) {

                \Log::error('ProductReviewQueue create error: '.$e->getMessage());

            }

        });

        static::updated(function ($product) {

            Log::info('UPDATED EVENT FIRED', [
                'changed' => $product->wasChanged([
                    'cover_img',
                    'cover_img2',
                    'cover_img3',
                ])
            ]);

            if ($product->wasChanged([
                'cover_img',
                'cover_img2',
                'cover_img3',
                'movie'
            ])) {

                // ステータス戻す
                $product->updateQuietly([
                    'review_status' => 'pending'
                ]);

                // Queue更新
                ProductReviewQueue::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'user_id' => $product->shop->user_id ?? auth()->id(),
                        'status' => 'pending',
                        'reviewer_id' => null,
                        'review_started_at' => null
                    ]
                );

                // 🔥 AI再実行（これが正解）
                // AnalyzeProductImageJob::dispatch($product);
            }

        });
        
        static::updating(function ($product) {

            $changes = $product->getDirty();

            if(!$changes){
                return;
            }

            ProductVersion::create([

                'product_id' => $product->id,

                'user_id' => auth()->id(),

                'before_data' => array_intersect_key(
                    $product->getOriginal(),
                    $changes
                ),

                'after_data' => $changes,

                'change_type' => 'edit'

            ]);

        });

    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'category_product', 'product_id', 'categories_id');
    }

    public function fovo_dises()
    {
        return $this->hasMany(FavoritesDisplay::class);    
    }

    public function user_favo()
    {
        return $this->hasMany(Fovorite::class);    
    }

    public function generatePriceHistories()
    {   
        $priceHistories = PriceHistory::find($this->id);
        // dd($this->id);
        $priceHistories = PriceHistory::updateOrCreate([
            'product_id' => $this->id,
            'price' => $this->getOriginal('price'),  // 変更前の価格
            'shop_id' => $this->shop_id,
        ]);
    }

    public function generateStockHistories()
    {   
        $stockHistories = InventoryLog::find($this->id);
        // dd($this->id);
        $stockHistories = InventoryLog::updateOrCreate([
            'product_id' => $this->id,
            'date' => $this->updated_at,
            'stock' => $this->getOriginal('stock'),  // 変更前の価格
            'shop_id' => $this->shop_id,
        ]);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class); // 商品に関連する在庫ログ
    }

    public function scopePending($query)
    {
        return $query->where('status', 1);
    }
 
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function violations()
    {
        return $this->hasMany(ProductViolation::class);
    }

    public function reviewQueue()
    {
        return $this->hasOne(ProductReviewQueue::class);
    }

    public function reviewLogs()
    {
        return $this->hasMany(ProductReviewLog::class);
    }

    public function versions()
    {
        return $this->hasMany(ProductVersion::class);
    }

    public function getFixFieldsAttribute()
    {
        return $this->reviewQueue->fix_fields ?? [];
    }

    public function getFixCommentAttribute()
    {
        return $this->reviewQueue->comment ?? null;
    }



}
