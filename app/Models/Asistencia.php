<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    public const TYPE_IN = 'in';

    public const TYPE_OUT = 'out';

    public const TYPES = [
        self::TYPE_IN => 'Entrada',
        self::TYPE_OUT => 'Salida',
    ];

    public const STATUS_PRESENT = 'present';

    public const STATUS_ABSENT = 'absent';

    public const STATUSES = [
        self::STATUS_PRESENT => 'Presente',
        self::STATUS_ABSENT => 'Ausente',
    ];

    protected $fillable = [
        'personal_id',
        'turno_id',
        'fecha_asistencia',
        'hora_asistencia',
        'registrado_en',
        'type',
        'status',
        'notes',
    ];

    protected $casts = [
        'fecha_asistencia' => 'date',
        'registrado_en' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
