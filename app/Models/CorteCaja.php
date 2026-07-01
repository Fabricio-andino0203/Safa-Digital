<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorteCaja extends Model
{
    protected $table = 'cortes_caja';

    protected $fillable = [
        'usuario_id',
        'fecha_apertura',
        'fecha_cierre',
        'fondo_inicial',
        'ventas_efectivo',
        'total_esperado',
        'efectivo_real',
        'diferencia',
        'retiro_tesoreria',
        'notas',
    ];

    protected $casts = [
        'fecha_apertura'  => 'datetime',
        'fecha_cierre'    => 'datetime',
        'fondo_inicial'   => 'decimal:2',
        'ventas_efectivo' => 'decimal:2',
        'total_esperado'  => 'decimal:2',
        'efectivo_real'   => 'decimal:2',
        'diferencia'      => 'decimal:2',
        'retiro_tesoreria'=> 'decimal:2',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
