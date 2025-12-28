<?php

// app/Models/QandAPage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QandAPage extends Model
{
    protected $table = 'q_and_a_pages';

    protected $fillable = [
        'slug',
        'title',
        'question',
        'answer',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}


