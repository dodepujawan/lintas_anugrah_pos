<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    protected $fillable = [
        'KODE', 'NAMA', 'CABANG', 'KETERANGAN', 'USER', 'USEREDIT', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
