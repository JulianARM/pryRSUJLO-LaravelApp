<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaldoVacacion extends Model
{
    use HasFactory;

    protected $table = 'saldos_vacaciones';

    public const DEFAULT_ANNUAL_DAYS = 30;

    protected $fillable = [
        'personal_id',
        'anio',
        'dias_totales',
        'dias_usados',
        'dias_disponibles',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function vacationRequests(): HasMany
    {
        return $this->hasMany(SolicitudVacacion::class, 'saldo_vacacion_id');
    }

    public function canUse(int $days): bool
    {
        return $this->dias_disponibles >= $days;
    }

    public function discount(int $days): void
    {
        $this->forceFill([
            'dias_usados' => $this->dias_usados + $days,
            'dias_disponibles' => $this->dias_disponibles - $days,
        ])->save();
    }

    public function restoreDays(int $days): void
    {
        $availableDays = min($this->dias_totales, $this->dias_disponibles + $days);

        $this->forceFill([
            'dias_usados' => max(0, $this->dias_usados - $days),
            'dias_disponibles' => $availableDays,
        ])->save();
    }
}
