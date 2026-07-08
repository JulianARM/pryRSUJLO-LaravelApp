<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contrato extends Model
{
    use HasFactory;

    protected $table = 'contratos';

    public const TYPE_PERMANENT = 'permanent';

    public const TYPE_NAMED = 'named';

    public const TYPE_TEMPORARY = 'temporary';

    public const TYPES = [
        self::TYPE_PERMANENT => 'Permanente',
        self::TYPE_NAMED => 'Nombrado',
        self::TYPE_TEMPORARY => 'Temporal',
    ];

    protected $fillable = [
        'personal_id',
        'tipo_contrato',
        'fecha_inicio',
        'fecha_fin',
        'salario',
        'meses_periodo_prueba',
        'cargo',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'salario' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function getContratoTypeLabelAttribute(): string
    {
        return self::TYPES[$this->tipo_contrato] ?? $this->tipo_contrato;
    }
}
