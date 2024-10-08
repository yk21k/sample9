<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Traits\Translatable;

class Categories extends \TCG\Voyager\Models\Category
{
    use Translatable;

    protected $translatable = ['slug', 'name', 'shop_id'];

    protected $table = 'categories';

    protected $fillable = ['slug', 'name', 'shop_id'];

    public function posts()
    {
        return $this->hasMany(Voyager::modelClass('Post'))
            ->published()
            ->orderBy('created_at', 'DESC');
    }

    public function parentId()
    {
        return $this->belongsTo(self::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }

    public function children()
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }

    public function allProducts()
    {
        
        $allProducts = collect([]);

        $mainCategoryProducts = $this->products;

        $allProducts = $allProducts->concat($mainCategoryProducts);
        // dd($allProducts);
        if($this->children->isNotEmpty()){

            foreach($this->children as $child){
                $allProducts = $allProducts->concat($child->products);
            }

        }
        

        // dd($allProducts);
        return $allProducts;
    }


}
