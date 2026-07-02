<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductoVariante extends Model
{
    use HasFactory;

    protected $table = 'producto_variantes';

    protected $fillable = [
        'producto_id',
        'sku',
        'atributos',
        'imagen',
        'costo',
        'precio',
        'stock_fisico',
        'stock_reservado',
        'stock_minimo',
        'activo',
    ];

    protected $casts = [
        'atributos'       => 'array',  // JSON ↔ PHP array automático
        'costo'           => 'decimal:2',
        'precio'          => 'decimal:2',
        'stock_fisico'    => 'integer',
        'stock_reservado' => 'integer',
        'stock_minimo'    => 'integer',
        'activo'          => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relaciones
    // ──────────────────────────────────────────────────────────────────────────

    /** El blank al que pertenece esta variante */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /** Líneas de pedidos que incluyen esta variante */
    public function pedidoDetalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class, 'variante_id');
    }

    /** Líneas de ventas POS que incluyen esta variante */
    public function ventaDetalles(): HasMany
    {
        return $this->hasMany(VentaPosDetalle::class, 'variante_id');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Accesor: stock_disponible (sin columna en DB — calculado en tiempo real)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Stock disponible para vender o asignar a nuevos pedidos.
     * Nunca puede ser negativo.
     *
     * Fórmula: stock_fisico - stock_reservado
     */
    public function getStockDisponibleAttribute(): int
    {
        return max(0, $this->stock_fisico - $this->stock_reservado);
    }

    /**
     * Indica si la variante tiene bajo stock (útil para alertas en UI).
     */
    public function getBajoStockAttribute(): bool
    {
        return $this->stock_disponible <= $this->stock_minimo && $this->stock_minimo > 0;
    }

    /**
     * Nombre legible para mostrar en UI y snapshots.
     * Ej: "Camisa Oversize — Negro / M"
     */
    public function getNombreCompletoAttribute(): string
    {
        $nombreProducto = $this->producto?->nombre ?? 'Producto';

        if (empty($this->atributos)) {
            return $nombreProducto;
        }

        $atributosStr = implode(' / ', array_values($this->atributos));
        return "{$nombreProducto} — {$atributosStr}";
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Lógica de stock — Métodos de negocio (transaccionales)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * RESERVAR: Se llama al crear un pedido.
     * Incrementa stock_reservado SIN tocar stock_fisico.
     * El stock_disponible baja (stock_fisico - stock_reservado).
     *
     * @throws \Exception Si no hay stock disponible suficiente
     */
    public function reservar(int $cantidad): void
    {
        if ($this->stock_disponible < $cantidad) {
            throw new \Exception(
                "Stock insuficiente para '{$this->nombre_completo}'. " .
                "Disponible: {$this->stock_disponible}, Solicitado: {$cantidad}."
            );
        }

        $this->increment('stock_reservado', $cantidad);
    }

    /**
     * LIBERAR RESERVA: Se llama al cancelar un pedido.
     * Decrementa stock_reservado. El stock_disponible vuelve a subir.
     */
    public function liberarReserva(int $cantidad): void
    {
        $liberar = min($cantidad, $this->stock_reservado); // Nunca dejar negativo
        $this->decrement('stock_reservado', $liberar);
    }

    /**
     * CONFIRMAR ENTREGA: Se llama cuando el pedido pasa a estado "Entregado".
     * Descuenta stock_fisico Y stock_reservado en el mismo movimiento.
     * El stock_disponible no cambia (ya estaba reservado), solo el físico baja.
     */
    public function confirmarEntrega(int $cantidad): void
    {
        $this->decrement('stock_fisico', $cantidad);
        $this->decrement('stock_reservado', min($cantidad, $this->stock_reservado));
        
        $this->refresh();
        $this->verificarStockBajo();
    }

    /**
     * VENTA DIRECTA POS: Descuenta solo stock_fisico (sin pasar por reserva).
     * Usado en ventas en mostrador — inmediato y sin reserva previa.
     *
     * @throws \Exception Si no hay stock disponible suficiente
     */
    public function venderDirecto(int $cantidad): void
    {
        if ($this->stock_disponible < $cantidad) {
            throw new \Exception(
                "Stock insuficiente para '{$this->nombre_completo}'. " .
                "Disponible: {$this->stock_disponible}, Solicitado: {$cantidad}."
            );
        }

        $this->decrement('stock_fisico', $cantidad);
        
        $this->refresh();
        $this->verificarStockBajo();
    }

    public function verificarStockBajo(): void
    {
        $disponible = $this->stock_disponible;
        $minimo = $this->stock_minimo > 0 ? $this->stock_minimo : 5;
        if ($disponible <= $minimo) {
            $admin = \App\Models\User::find(1);
            if ($admin) {
                $exists = $admin->unreadNotifications()
                    ->where('data->type', 'stock_bajo')
                    ->where('data->mensaje', 'LIKE', "%{$this->sku}%")
                    ->exists();
                if (!$exists) {
                    $admin->notify(new \App\Notifications\StockBajoNotification($this->sku, $this->nombre_completo, $disponible));
                }
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SKU Automático Sugerido
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Genera un SKU sugerido basado en el nombre del producto padre y los atributos.
     *
     * Algoritmo:
     *   1. Tomar primeras 3 letras del nombre del producto → "CAM"
     *   2. Para cada atributo: primeras 3 letras del valor → "NEG", "M"
     *   3. Unir con "-" → "CAM-NEG-M"
     *   4. Todo en mayúsculas y sin acentos
     *   5. Si ya existe, agregar sufijo numérico: "CAM-NEG-M-2"
     *
     * @param string $nombreProducto  El nombre del blank padre
     * @param array  $atributos       El array de atributos JSON
     */
    public static function generarSkuSugerido(string $nombreProducto, array $atributos = []): string
    {
        // Quitar acentos y caracteres especiales
        $normalizar = fn(string $str) => strtoupper(
            substr(
                preg_replace('/[^A-Z0-9]/', '', strtoupper(
                    transliterator_transliterate('Any-Latin; Latin-ASCII', $str) ?? $str
                )),
                0, 3
            )
        );

        $partes = [$normalizar($nombreProducto)];

        foreach ($atributos as $valor) {
            $partes[] = $normalizar((string) $valor);
        }

        $base = implode('-', array_filter($partes));

        // Verificar unicidad y agregar sufijo si es necesario
        $sku = $base;
        $contador = 2;
        while (static::where('sku', $sku)->exists()) {
            $sku = "{$base}-{$contador}";
            $contador++;
        }

        return $sku;
    }
}
