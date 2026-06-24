<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaPosDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_pos_detalles';

    protected $fillable = [
        'venta_pos_id',
        'producto_id',
        'nombre_producto',   // snapshot del nombre al momento de vender
        'cantidad',
        'precio_unitario',
        'descuento_linea',
        'subtotal',
    ];

    protected $casts = [
        'cantidad'        => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento_linea' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────────────────────────────────

    public function venta(): BelongsTo
    {
        return $this->belongsTo(VentaPos::class, 'venta_pos_id');
    }

    /**
     * Relación al producto real (puede ser null si el producto fue eliminado).
     * Siempre consultar 'nombre_producto' del detalle para reportes históricos.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
