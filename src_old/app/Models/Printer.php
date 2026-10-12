<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $table = 'printers';

    protected $fillable = [
        'user_id',
        'Printer_name'
    ];
}
