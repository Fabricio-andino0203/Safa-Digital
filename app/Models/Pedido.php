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
        'numero_orden',
        'cliente_id',
        'prioridad',
        'estado',
        'subtotal',
        'descuento',
        'total_pedido',
        'total_abonado',
        'saldo_pendiente',
        'fecha_estimada_entrega',
        'fecha_entrega',
        'hora_estimada_entrega',
        'notas',
        'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha_estimada_entrega' => 'date',
        'fecha_entrega' => 'date',
    ];

    /**
     * Autogenerar el numero_orden al crear un nuevo pedido.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pedido) {
            if (empty($pedido->numero_orden)) {
                $ultimoPedido = static::orderBy('id', 'desc')->first();
                $siguienteNumero = $ultimoPedido ? $ultimoPedido->id + 1 : 1;
                // Formato: ORD-000001
                $pedido->numero_orden = 'ORD-' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function archivos()
    {
        return $this->hasMany(PedidoArchivo::class);
    }

    public function disenos(): HasMany
    {
        return $this->hasMany(Diseno::class);
    }

    public function historiales(): HasMany
    {
        return $this->hasMany(PedidoHistorial::class, 'pedido_id')->orderBy('created_at', 'asc');
    }

    public function getEstadoPagoAttribute()
    {
        if ($this->total_abonado >= $this->total_pedido && $this->total_pedido > 0) {
            return 'Pagado';
        } elseif ($this->total_abonado > 0) {
            return 'Parcial';
        }
        return 'Pendiente';
    }

    public function movimientosCaja(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class);
    }
}
