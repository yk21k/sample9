<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Fovorite extends Model
{
    use HasFactory;

    public function favoriteDisplay()
    {
        return $this->hasMany(FavoritesDisplay::class);
    }

    public function genarateFavoRates()
    {
        $product_id = $this->product_id;

        // dd($product_id);
        // dd($favoItems);
        // dd($this->favoriteDisplay());

        $averageRatings = Fovorite::select('product_id', DB::raw('AVG(wants) * 0.4 as average_rating'))->groupBy('product_id')->orderByDesc('average_rating')->get()->toArray();
        // dd($averageRatings);

        $norm_rates = collect($averageRatings)->where('product_id', $product_id)->first();
        // dd($norm_rates);

        $pram_fovorite = FavoritesDisplay::where('product_id', $product_id)->latest()->first();
        
        
        FavoritesDisplay::where('product_id', $product_id)->update([
                'norm_rate'=>$norm_rates['average_rating'],
                'fovorite_id'=> $product_id,
        ]);
        
        $norm_total = $pram_fovorite->norm_total + $norm_rates['average_rating'];
                
        FavoritesDisplay::where('product_id', $product_id)->update(['norm_total'=>$norm_total]);
           
    }
}
