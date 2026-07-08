<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'marca_id',
        'modelo_vehiculo_id',
        'tipo_vehiculo_id',
        'color_vehiculo_id',
        'name',
        'code',
        'placa',
        'anio',
        'capacidad_carga',
        'capacidad_combustible',
        'capacidad_compactacion',
        'capacidad_personas',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'capacidad_carga' => 'decimal:2',
        'capacidad_combustible' => 'decimal:2',
        'capacidad_compactacion' => 'decimal:2',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(ModeloVehiculo::class, 'modelo_vehiculo_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ColorVehiculo::class, 'color_vehiculo_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ImagenVehiculo::class, 'vehiculo_id');
    }

    public function profileImage()
    {
        return $this->hasOne(ImagenVehiculo::class, 'vehiculo_id')->where('es_principal', true);
    }

    public function personnelGroups(): HasMany
    {
        return $this->hasMany(GrupoPersonal::class, 'vehiculo_id');
    }

    public function routeSchedules(): HasMany
    {
        return $this->hasMany(Programacion::class, 'vehiculo_id');
    }
}
