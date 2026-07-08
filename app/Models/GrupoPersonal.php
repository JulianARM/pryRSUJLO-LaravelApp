<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoPersonal extends Model
{
    use HasFactory;

    protected $table = 'grupos_personal';

    public const DAYS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    protected $fillable = [
        'turno_id',
        'zona_id',
        'vehiculo_id',
        'conductor_id',
        'name',
        'dias_semana',
        'activo',
    ];

    protected $casts = [
        'dias_semana' => 'array',
        'activo' => 'boolean',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'conductor_id');
    }

    public function helpers(): BelongsToMany
    {
        return $this->belongsToMany(Personal::class, 'grupo_personal_ayudantes', 'grupo_personal_id', 'personal_id')
            ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Programacion::class, 'grupo_personal_id');
    }

    public function getDaysLabelAttribute(): string
    {
        return collect($this->dias_semana ?? [])
            ->map(fn ($day) => self::DAYS[(int) $day] ?? $day)
            ->implode(', ');
    }
}
