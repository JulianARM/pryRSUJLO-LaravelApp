<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeloVehiculo extends Model
{
    use HasFactory;

    protected $table = 'modelos_vehiculo';

    protected $fillable = [
        'marca_id',
        'name',
        'code',
        'descripcion',
    ];

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function brand(): BelongsTo
    {
        return $this->marca();
    }

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'modelo_vehiculo_id');
    }
}
