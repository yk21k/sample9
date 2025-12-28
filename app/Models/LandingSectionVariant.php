<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSectionVariant extends Model
{
    protected $fillable = [
        'section_type',
        'variant_key',
        'title',
        'description',
        'cta_text',
        'cta_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'features' => 'array', // JSONを配列に変換
    ];

    /*
     |--------------------------------------------------------------------------
     | 制約ロジック
     |--------------------------------------------------------------------------
     | 同じ section_type で is_active = true は必ず1件だけ
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->is_active) {
                static::where('section_type', $model->section_type)
                    ->where('id', '!=', $model->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    /*
     |--------------------------------------------------------------------------
     | Scope
     |--------------------------------------------------------------------------
     */
    public function scopeActive($query, string $type)
    {
        return $query->where('section_type', $type)
                     ->where('is_active', true);
    }
}

