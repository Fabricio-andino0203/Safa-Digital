<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoTesoreria extends Model
{
    protected $table = 'movimiento_tesorerias';

    protected $fillable = [
        'cuenta_id',
        'tipo',
        'monto',
        'concepto',
        'referencia_modulo',
        'usuario_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(CuentaFinanciera::class, 'cuenta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
