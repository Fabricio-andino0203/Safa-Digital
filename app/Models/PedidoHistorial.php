<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoHistorial extends Model
{
    use HasFactory;

    protected $table = 'pedido_historiales';

    public $timestamps = false; // Solo usamos created_at manualmente o con useCurrent()

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'estado_anterior',
        'estado_nuevo',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
