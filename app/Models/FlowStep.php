<?php

// app/Models/FlowStep.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowStep extends Model
{
    protected $fillable = [
        'flow_id',
        'step_order',
        'title',
        'description',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }
}
