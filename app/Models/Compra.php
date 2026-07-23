<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';

    protected $fillable = [
        'numero_orden',
        'pedido_id',
        'proveedor_id',
        'fecha',
        'total',
        'estado',
        'notes', // compatible con notas
        'notas',
        'extras',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
        'extras' => 'array',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'compra_id');
    }
}
