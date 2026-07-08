<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudVacacion extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_vacaciones';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_APPROVED => 'Aprobado',
        self::STATUS_REJECTED => 'Rechazado',
        self::STATUS_CANCELLED => 'Cancelado',
    ];

    protected $fillable = [
        'personal_id',
        'saldo_vacacion_id',
        'fecha_solicitud',
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'dias_restantes',
        'status',
        'notes',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function vacationBalance(): BelongsTo
    {
        return $this->belongsTo(SaldoVacacion::class, 'saldo_vacacion_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
