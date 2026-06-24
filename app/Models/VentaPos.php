<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VentaPos extends Model
{
    use HasFactory;

    protected $table = 'ventas_pos';

    protected $fillable = [
        'caja_sesion_id',
        'cliente_id',
        'subtotal',
        'descuento',
        'total',
        'metodo_pago',
        'monto_entregado',
        'cambio',
        'estado',
        'notas',
    ];

    protected $casts = [
        'subtotal'       => 'decimal:2',
        'descuento'      => 'decimal:2',
        'total'          => 'decimal:2',
        'monto_entregado'=> 'decimal:2',
        'cambio'         => 'decimal:2',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────────────────────────────────

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'caja_sesion_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaPosDetalle::class, 'venta_pos_id');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers de UI — Etiquetas legibles para métodos de pago
    // ──────────────────────────────────────────────────────────────────────────

    /** Devuelve la etiqueta visual del método de pago */
    public function getLabelMetodoPagoAttribute(): string
    {
        return match($this->metodo_pago) {
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia',
            'tarjeta'       => 'Tarjeta',
            'mixto'         => 'Mixto',
            default         => ucfirst($this->metodo_pago),
        };
    }
}
