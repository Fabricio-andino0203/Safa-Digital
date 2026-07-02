<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Categoria;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $nombreProducto = trim($row['producto'] ?? $row['nombre_producto'] ?? $row['nombre'] ?? '');
            if (empty($nombreProducto)) {
                continue;
            }

            // Categoría
            $nombreCategoria = trim($row['categoria'] ?? '');
            $categoriaId = null;
            if (!empty($nombreCategoria)) {
                $categoria = Categoria::firstOrCreate(['nombre' => $nombreCategoria]);
                $categoriaId = $categoria->id;
            }

            // Buscar o crear el Producto Base (Blank)
            $producto = Producto::firstOrCreate(
                ['nombre' => $nombreProducto],
                [
                    'categoria_id' => $categoriaId,
                    'descripcion' => trim($row['descripcion'] ?? 'Importado masivamente'),
                    'activo' => true
                ]
            );

            // Generar SKU si no está definido
            $sku = trim($row['sku'] ?? '');
            
            // Atributos
            $atributos = [];
            if (isset($row['color']) && !empty(trim($row['color']))) {
                $atributos['Color'] = trim($row['color']);
            }
            if (isset($row['talla']) && !empty(trim($row['talla']))) {
                $atributos['Talla'] = trim($row['talla']);
            }
            if (isset($row['genero']) && !empty(trim($row['genero']))) {
                $atributos['Genero'] = trim($row['genero']);
            }

            if (empty($sku)) {
                $sku = ProductoVariante::generarSkuSugerido($producto->nombre, array_values($atributos));
            }

            // Costo y Precio
            $costo = floatval($row['costo'] ?? 0.00);
            $precio = floatval($row['precio'] ?? $row['precio_venta'] ?? 0.00);
            $stock = intval($row['stock'] ?? $row['stock_fisico'] ?? $row['cantidad'] ?? 0);

            // Crear o actualizar la Variante
            ProductoVariante::updateOrCreate(
                ['sku' => $sku],
                [
                    'producto_id' => $producto->id,
                    'atributos' => $atributos,
                    'costo' => $costo,
                    'precio' => $precio,
                    'stock_fisico' => $stock,
                    'stock_reservado' => 0,
                    'stock_minimo' => 0,
                    'activo' => true
                ]
            );
        }
    }
}
