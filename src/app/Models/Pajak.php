<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
{
    use HasFactory;

    protected $table = 'ol_pajaks';

    protected $fillable = [
        'PPN', 'DISC1', 'DISC2', 'USER', 'USEREDIT', 'is_active',
    ];

    protected $casts = [
        'PPN' => 'decimal:2',
        'DISC1' => 'decimal:2',
        'DISC2' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
