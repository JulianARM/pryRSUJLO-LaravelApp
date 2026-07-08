<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    protected $fillable = [
        'tipo_personal_id',
        'dni',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'telefono',
        'email',
        'licencia',
        'password',
        'direccion',
        'ruta_foto',
        'activo',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
        'password' => 'hashed',
    ];

    public function staffType(): BelongsTo
    {
        return $this->belongsTo(TipoPersonal::class, 'tipo_personal_id');
    }

    public function tipoPersonal(): BelongsTo
    {
        return $this->staffType();
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contrato::class, 'personal_id');
    }

    public function contratos(): HasMany
    {
        return $this->contracts();
    }

    public function scopeWithActiveContrato(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->whereHas('contracts', function (Builder $contractQuery) use ($today) {
            $contractQuery
                ->where('activo', true)
                ->whereDate('fecha_inicio', '<=', $today)
                ->where(function (Builder $dateQuery) use ($today) {
                    $dateQuery
                        ->whereNull('fecha_fin')
                        ->orWhereDate('fecha_fin', '>=', $today);
                });
        });
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'personal_id');
    }

    public function vacationRequests(): HasMany
    {
        return $this->hasMany(SolicitudVacacion::class, 'personal_id');
    }

    public function solicitudesVacaciones(): HasMany
    {
        return $this->vacationRequests();
    }

    public function vacationBalances(): HasMany
    {
        return $this->hasMany(SaldoVacacion::class, 'personal_id');
    }

    public function saldosVacaciones(): HasMany
    {
        return $this->vacationBalances();
    }

    public function drivenPersonnelGroups(): HasMany
    {
        return $this->hasMany(GrupoPersonal::class, 'conductor_id');
    }

    public function assistedPersonnelGroups()
    {
        return $this->belongsToMany(GrupoPersonal::class, 'grupo_personal_ayudantes', 'personal_id', 'grupo_personal_id')
            ->withTimestamps();
    }

    public function drivenRouteSchedules(): HasMany
    {
        return $this->hasMany(Programacion::class, 'conductor_id');
    }

    public function assistedRouteSchedules()
    {
        return $this->belongsToMany(Programacion::class, 'programacion_ayudantes', 'personal_id', 'programacion_id')
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->nombres.' '.$this->apellidos);
    }

    public function vacationDaysAvailable(int $year): int
    {
        $balance = $this->relationLoaded('vacationBalances')
            ? $this->vacationBalances->firstWhere('anio', $year)
            : $this->vacationBalances()->where('anio', $year)->first();

        return $balance?->dias_disponibles ?? SaldoVacacion::DEFAULT_ANNUAL_DAYS;
    }
}
