<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaFinanciera extends Model
{
    protected $table = 'cuenta_financieras';

    protected $fillable = [
        'nombre',
        'tipo',
        'saldo_actual',
    ];

    protected $casts = [
        'saldo_actual' => 'decimal:2',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoTesoreria::class, 'cuenta_id');
    }
}
