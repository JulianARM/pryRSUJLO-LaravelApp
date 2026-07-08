<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feriado extends Model
{
    use HasFactory;

    protected $table = 'feriados';

    protected $fillable = [
        'date',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'date' => 'date',
        'activo' => 'boolean',
    ];
}
