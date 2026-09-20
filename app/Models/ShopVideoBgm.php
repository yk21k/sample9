<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopVideoBgm extends Model
{
    protected $table = 'shop_video_bgms';

    protected $fillable = [
        'name',
        'file_path',
        'duration',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'duration' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * このBGMを使用している動画
     */
    public function videos(): HasMany
    {
        return $this->hasMany(
            ShopVideoDraft::class,
            'bgm_id'
        );
    }
}