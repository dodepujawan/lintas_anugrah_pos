<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $table = 'signature';

    protected $fillable = [
        'nama',
    ];
}
