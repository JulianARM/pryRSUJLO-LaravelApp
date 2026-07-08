<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zona extends Model
{
    use HasFactory;

    protected $table = 'zonas';

    protected $fillable = [
        'name',
        'departamento',
        'provincia',
        'distrito',
        'descripcion',
        'residuos_promedio_kg',
        'activo',
    ];

    protected $casts = [
        'residuos_promedio_kg' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function coordinates(): HasMany
    {
        return $this->hasMany(CoordenadaZona::class, 'zona_id')->orderBy('orden');
    }

    public function personnelGroups(): HasMany
    {
        return $this->hasMany(GrupoPersonal::class, 'zona_id');
    }

    public function routeSchedules(): HasMany
    {
        return $this->hasMany(Programacion::class, 'zona_id');
    }

    public function getLocationLabelAttribute(): string
    {
        return "{$this->departamento} / {$this->provincia} / {$this->distrito}";
    }
}
