<?php

// app/Models/Flow.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flow extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'overview',
    ];

    public function steps()
    {
        return $this->hasMany(FlowStep::class)
            ->orderBy('step_order');
    }
}

