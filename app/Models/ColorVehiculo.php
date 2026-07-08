<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColorVehiculo extends Model
{
    use HasFactory;

    protected $table = 'colores_vehiculo';

    protected $fillable = [
        'name',
        'code',
        'descripcion',
    ];

    protected $casts = [
        'code' => 'string',
    ];

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'color_vehiculo_id');
    }
}

