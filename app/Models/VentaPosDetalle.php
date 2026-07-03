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
        'variante_id',
        'nombre_snapshot',  // Snapshot del nombre completo: "Camisa Oversize — Negro / M"
        'sku_snapshot',     // Snapshot del SKU: "CAM-NEG-M"
        'cantidad',
        'precio_unitario',
        'costo_unitario',
        'descuento_linea',
        'subtotal',
        'extras',
    ];

    protected $casts = [
        'cantidad'        => 'integer',
        'precio_unitario' => 'decimal:2',
        'costo_unitario'  => 'decimal:2',
        'descuento_linea' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'extras'          => 'array',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────────────────────────────────

    public function venta(): BelongsTo
    {
        return $this->belongsTo(VentaPos::class, 'venta_pos_id');
    }

    /**
     * Variante vendida (puede ser null si fue eliminada).
     * SIEMPRE usar nombre_snapshot / sku_snapshot para reportes históricos.
     */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id');
    }
}
