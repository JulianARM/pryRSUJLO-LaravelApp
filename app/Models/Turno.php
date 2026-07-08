<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'turnos';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'descripcion',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'turno_id');
    }

    public function personnelGroups(): HasMany
    {
        return $this->hasMany(GrupoPersonal::class, 'turno_id');
    }

    public function routeSchedules(): HasMany
    {
        return $this->hasMany(Programacion::class, 'turno_id');
    }
}
