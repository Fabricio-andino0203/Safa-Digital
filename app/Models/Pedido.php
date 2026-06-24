<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'cliente_id',
        'estado',
        'total',
        'adelanto',
        'saldo',
        'fecha_entrega',
        'notas'
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function disenos(): HasMany
    {
        return $this->hasMany(Diseno::class);
    }

    public function movimientosCaja(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class);
    }
}
