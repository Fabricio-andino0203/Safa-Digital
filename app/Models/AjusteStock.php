<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjusteStock extends Model
{
    protected $table = 'ajustes_stock';

    protected $fillable = [
        'producto_variante_id',
        'cantidad',
        'motivo',
        'usuario_id',
    ];

    public function variante(): BelongsTo
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
