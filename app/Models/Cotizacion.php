<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'numero_cotizacion',
        'cliente_id',
        'fecha_emision',
        'validez_dias',
        'subtotal',
        'descuento',
        'total',
        'estado',
        'notes', // Just in case, keeping notes/notas both or mapping them
        'notas',
        'pedido_id'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'validez_dias'  => 'integer',
        'subtotal'      => 'decimal:2',
        'descuento'     => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CotizacionDetalle::class);
    }
}
