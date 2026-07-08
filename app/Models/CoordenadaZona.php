<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoordenadaZona extends Model
{
    use HasFactory;

    protected $table = 'coordenadas_zona';

    protected $fillable = [
        'zona_id',
        'latitud',
        'longitud',
        'orden',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }
}
