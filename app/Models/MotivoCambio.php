<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoCambio extends Model
{
    use HasFactory;

    protected $table = 'motivos_cambio';

    protected $fillable = [
        'name',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function cambios(): HasMany
    {
        return $this->hasMany(CambioProgramacion::class, 'motivo_cambio_id');
    }

    public function scheduleChanges(): HasMany
    {
        return $this->cambios();
    }
}
