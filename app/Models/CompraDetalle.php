<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalle extends Model
{
    use HasFactory;

    protected $table = 'compra_detalles';

    protected $fillable = [
        'compra_id',
        'producto_variante_id',
        'nombre_snapshot',
        'cantidad',
        'costo_unitario',
        'subtotal',
        'costo_proveedor',
        'costo_extra',
        'costo_total',
    ];

    protected $casts = [
        'costo_unitario' => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'cantidad'       => 'integer',
        'costo_proveedor'=> 'decimal:2',
        'costo_extra'    => 'decimal:2',
        'costo_total'    => 'decimal:2',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }
}
