<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    use HasFactory;
    protected $fillable = ['shop_id', 'category_id', 'question', 'answer', 'keywords', 'is_approved', 'target'];

    public function category()
    {
        return $this->belongsTo(FAQCategory::class, 'category_id');
    }
}
