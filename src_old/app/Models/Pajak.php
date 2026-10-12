<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
{
    protected $table = 'ol_pajak';

    protected $fillable = [
        'ppn',
    ];
}
