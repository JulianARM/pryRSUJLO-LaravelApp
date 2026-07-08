<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPersonal extends Model
{
    use HasFactory;

    protected $table = 'tipos_personal';

    public const DRIVER = 'Conductor';

    protected $fillable = [
        'name',
        'descripcion',
        'es_sistema',
    ];

    protected $casts = [
        'es_sistema' => 'boolean',
    ];

    public function personal(): HasMany
    {
        return $this->hasMany(Personal::class, 'tipo_personal_id');
    }

    public function isDriver(): bool
    {
        return strcasecmp($this->name, self::DRIVER) === 0;
    }
}
