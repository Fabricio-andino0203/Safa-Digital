<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CajaSesion extends Model
{
    use HasFactory;

    protected $table = 'caja_sesiones';

    protected $fillable = [
        'usuario_id',
        'estado',
        'monto_inicial',
        'fecha_apertura',
        'fecha_cierre',
        'monto_final_esperado',
        'monto_contado_fisico',
        'diferencia',
        'notas',
    ];

    protected $casts = [
        'fecha_apertura'       => 'datetime',
        'fecha_cierre'         => 'datetime',
        'monto_inicial'        => 'decimal:2',
        'monto_final_esperado' => 'decimal:2',
        'monto_contado_fisico' => 'decimal:2',
        'diferencia'           => 'decimal:2',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(VentaPos::class, 'caja_sesion_id');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers de negocio
    // ──────────────────────────────────────────────────────────────────────────

    /** Total de ventas completadas en esta sesión */
    public function totalVentas(): float
    {
        return $this->ventas()->where('estado', 'completada')->sum('total');
    }

    /** Ventas agrupadas por método de pago (para el corte) */
    public function ventasPorMetodo(): array
    {
        return $this->ventas()
            ->where('estado', 'completada')
            ->selectRaw('metodo_pago, SUM(total) as total_metodo')
            ->groupBy('metodo_pago')
            ->pluck('total_metodo', 'metodo_pago')
            ->toArray();
    }

    /** ¿Hay una sesión abierta para este usuario? */
    public static function sesionAbierta(): ?self
    {
        return self::where('estado', 'abierta')->latest()->first();
    }
}
