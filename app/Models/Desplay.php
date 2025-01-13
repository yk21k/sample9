<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Observers\DesplayObserver;

class Desplay extends Model
{
    use HasFactory;
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    // Accessor
    public function getStaticUriAttribute() {

        // 静的HTMLファイルのURI
        return '/statics/shop_desplay_page'. $this->shop_name .'.html';

    }

    // Others
    public function generate_static() {

        // 静的HTMLのデータを作成
        $path = public_path($this->uri);
        // dd($path);
        $html = view('shop_site.shop_site_1')
            ->with('desplay', $this)
            ->render();
        return file_put_contents($path. '/' . 'statics/shop_desplay_page'. $this->shop_name .'.html', $html);

    }


}
