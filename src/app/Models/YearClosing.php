<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YearClosing extends Model
{
    protected $table = 'year_closings';

    protected $fillable = [
        'year',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];
}
