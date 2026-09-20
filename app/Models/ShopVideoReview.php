<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopVideoReview extends Model
{

    protected $fillable = [

        'shop_video_draft_id',

        'status',

        'reviewer_id',

        'comment',

        'reviewed_at',

    ];


    public function video()
    {
        return $this->belongsTo(
            ShopVideoDraft::class,
            'shop_video_draft_id'
        );
    }


    public function reviewer()
    {
        return $this->belongsTo(
            User::class,
            'reviewer_id'
        );
    }


}