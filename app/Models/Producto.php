<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'imagen',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────────────────────────────────

    /** Categoría a la que pertenece este blank */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Todas las variantes vendibles de este blank.
     * Ej: Camisa Oversize → [Negro M, Negro L, Blanco M, ...]
     */
    public function variantes(): HasMany
    {
        return $this->hasMany(ProductoVariante::class);
    }

    /** Solo variantes activas */
    public function variantesActivas(): HasMany
    {
        return $this->hasMany(ProductoVariante::class)->where('activo', true);
    }

    /** Extras asociados a este producto */
    public function extras(): HasMany
    {
        return $this->hasMany(ProductoExtra::class);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Accesores de stock agregado (calculados desde las variantes)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Stock físico total: suma del stock_fisico de todas las variantes.
     * Útil para mostrar un resumen rápido en la lista de inventario.
     */
    public function getStockTotalFisicoAttribute(): int
    {
        return (int) $this->variantes->sum('stock_fisico');
    }

    /**
     * Stock disponible total: suma del stock_disponible calculado de cada variante.
     * (stock_fisico - stock_reservado) por cada variante.
     */
    public function getStockTotalDisponibleAttribute(): int
    {
        return (int) $this->variantes->sum('stock_disponible');
    }
}
