<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rute extends Model
{
    use HasFactory;

    protected $table = 'rute';
    protected $primaryKey = 'id';
    public $timestamps = true; // true untuk menggunakan created_at & updated_at

    protected $fillable = [
        'RUTE',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
