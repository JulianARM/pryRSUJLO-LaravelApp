<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CambioProgramacion extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'cambios_programacion';

    protected $fillable = [
        'programacion_id',
        'usuario_id',
        'motivo_cambio_id',
        'lote_uuid',
        'action',
        'tipo_cambio',
        'descripcion',
        'detail',
        'valores_anteriores',
        'valores_nuevos',
    ];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Programacion::class, 'programacion_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(MotivoCambio::class, 'motivo_cambio_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getFechaProgramacionAfectadaAttribute(): ?Carbon
    {
        $date = $this->schedule?->fecha_programada
            ?? data_get($this->valores_nuevos, 'fecha_programada')
            ?? data_get($this->valores_anteriores, 'fecha_programada');

        return $date ? Carbon::parse($date) : null;
    }
}
