<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaMovimiento extends Model
{
    use HasFactory;

    protected $table = 'caja_movimientos';

    protected $fillable = [
        'caja_sesion_id',
        'tipo',
        'monto',
        'concepto',
        'referencia',
        'pedido_id',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────────────────────────────────

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'caja_sesion_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers de UI
    // ──────────────────────────────────────────────────────────────────────────

    /** Etiqueta visible en la UI: Depósito / Retiro */
    public function getTipoLabelAttribute(): string
    {
        return $this->tipo === 'ingreso' ? 'Depósito' : 'Retiro';
    }
}
