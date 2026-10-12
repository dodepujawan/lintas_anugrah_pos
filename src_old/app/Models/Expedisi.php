<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expedisi extends Model
{
    protected $table = 'expedisi';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];
}
