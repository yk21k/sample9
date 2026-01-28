<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $table = 'checklist_items';

    protected $fillable = [
        'name',
        'label',
        'purpose',
        'recommendation',
        'search_keywords',
        'sort_order',
    ];

    protected $casts = [
        'search_keywords' => 'array',
    ];
}

