<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programacion extends Model
{
    use HasFactory;

    protected $table = 'programaciones';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_FINALIZED = 'finalized';

    public const STATUS_REPROGRAMMED = 'reprogrammed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_SCHEDULED => 'Programado',
        self::STATUS_FINALIZED => 'Finalizado',
        self::STATUS_REPROGRAMMED => 'Reprogramado',
        self::STATUS_CANCELLED => 'Cancelado',
    ];

    protected $fillable = [
        'grupo_personal_id',
        'turno_id',
        'zona_id',
        'vehiculo_id',
        'conductor_id',
        'fecha_programada',
        'status',
        'notes',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
    ];

    public function personnelGroup(): BelongsTo
    {
        return $this->belongsTo(GrupoPersonal::class, 'grupo_personal_id');
    }

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
        return $this->belongsToMany(Personal::class, 'programacion_ayudantes', 'programacion_id', 'personal_id')
            ->withTimestamps();
    }

    public function changes(): HasMany
    {
        return $this->hasMany(CambioProgramacion::class, 'programacion_id')->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
