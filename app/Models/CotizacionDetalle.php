<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CotizacionDetalle extends Model
{
    use HasFactory;

    protected $table = 'cotizacion_detalles';

    protected $fillable = [
        'cotizacion_id',
        'tipo_producto',
        'producto_variante_id',
        'nombre_libre',
        'descripcion_libre',
        'costo_libre',
        'precio_venta',
        'cantidad',
        'subtotal'
    ];

    protected $casts = [
        'costo_libre'  => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'cantidad'     => 'integer',
        'subtotal'     => 'decimal:2',
    ];

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }
}
