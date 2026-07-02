<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\AjusteStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // PANTALLA PRINCIPAL
    // ══════════════════════════════════════════════════════════════════════════

    public function index()
    {
        $productos  = Producto::with(['categoria', 'variantes', 'extras'])->where('activo', true)->orderBy('nombre')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('inventario.index', compact('productos', 'categorias'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRODUCTOS BASE (BLANKS)
    // ══════════════════════════════════════════════════════════════════════════

    public function storeProducto(Request $request)
    {
        $validated = $request->validate([
            'nombre'       => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:categorias,id',
            'descripcion'  => 'nullable|string|max:1000',
            'imagen'       => 'nullable|string|max:1000',
            'extras'       => 'nullable|array',
            'extras.*.nombre' => 'required|string|max:255',
            'extras.*.costo'  => 'required|numeric|min:0',
            'extras.*.precio' => 'required|numeric|min:0',
        ]);

        $producto = Producto::create($validated);

        if (!empty($request->extras)) {
            foreach ($request->extras as $extra) {
                if (!empty($extra['nombre'])) {
                    $producto->extras()->create([
                        'nombre' => $extra['nombre'],
                        'costo' => $extra['costo'],
                        'precio' => $extra['precio'],
                    ]);
                }
            }
        }

        return response()->json(['success' => true, 'producto' => $producto->load(['categoria', 'extras'])]);
    }

    public function updateProducto(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre'       => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:categorias,id',
            'descripcion'  => 'nullable|string|max:1000',
            'imagen'       => 'nullable|string|max:1000',
            'extras'       => 'nullable|array',
            'extras.*.nombre' => 'required|string|max:255',
            'extras.*.costo'  => 'required|numeric|min:0',
            'extras.*.precio' => 'required|numeric|min:0',
        ]);

        $producto = Producto::findOrFail($id);
        $producto->update($validated);

        $producto->extras()->delete();
        if (!empty($request->extras)) {
            foreach ($request->extras as $extra) {
                if (!empty($extra['nombre'])) {
                    $producto->extras()->create([
                        'nombre' => $extra['nombre'],
                        'costo' => $extra['costo'],
                        'precio' => $extra['precio'],
                    ]);
                }
            }
        }

        return response()->json(['success' => true, 'producto' => $producto->load(['categoria', 'extras'])]);
    }

    public function destroyProducto($id)
    {
        $producto = Producto::findOrFail($id);

        // Verificar que no tenga variantes con stock activo
        $conStock = $producto->variantes()->where('stock_fisico', '>', 0)->count();
        if ($conStock > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: el producto tiene variantes con stock en bodega.'
            ], 422);
        }

        $producto->update(['activo' => false]);

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // VARIANTES
    // ══════════════════════════════════════════════════════════════════════════

    public function storeVariante(Request $request)
    {
        $rules = [
            'producto_id'    => 'required|exists:productos,id',
            'sku'            => 'required|string|max:100|unique:producto_variantes,sku',
            'atributos'      => 'nullable|array',
            'imagen'         => 'nullable|string|max:1000',
            'precio'         => 'required|numeric|min:0',
            'stock_fisico'   => 'required|integer|min:0',
            'stock_minimo'   => 'nullable|integer|min:0',
        ];

        if (auth()->id() === 1) {
            $rules['costo'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules);

        if (auth()->id() === 1) {
            $validated['costo'] = $validated['costo'] ?? 0.00;
        } else {
            $validated['costo'] = 0.00;
        }

        $variante = ProductoVariante::create([
            ...$validated,
            'stock_reservado' => 0,
            'stock_minimo'    => $validated['stock_minimo'] ?? 0,
            'activo'          => true,
        ]);

        return response()->json(['success' => true, 'variante' => $variante]);
    }

    public function updateVariante(Request $request, $id)
    {
        $variante = ProductoVariante::findOrFail($id);

        $rules = [
            'sku'          => 'required|string|max:100|unique:producto_variantes,sku,' . $id,
            'atributos'    => 'nullable|array',
            'imagen'       => 'nullable|string|max:1000',
            'precio'       => 'required|numeric|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
        ];

        if (auth()->id() === 1) {
            $rules['costo'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules);

        if (auth()->id() === 1) {
            $validated['costo'] = $validated['costo'] ?? 0.00;
        } else {
            unset($validated['costo']);
        }

        $variante->update($validated);

        return response()->json(['success' => true, 'variante' => $variante->fresh()]);
    }

    public function destroyVariante($id)
    {
        $variante = ProductoVariante::findOrFail($id);

        if ($variante->stock_fisico > 0 || $variante->stock_reservado > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: la variante tiene stock en bodega o reservas activas.'
            ], 422);
        }

        $variante->update(['activo' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Ajuste manual de stock físico.
     * Permite sumar (entrada) o restar (merma/corrección) del stock_fisico.
     */
    public function ajustarStock(Request $request, $id)
    {
        if (auth()->id() !== 1) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado. Acción exclusiva del Administrador.'], 403);
        }

        $validated = $request->validate([
            'cantidad' => 'required|integer', // positivo = entrada, negativo = merma
            'motivo'   => 'nullable|string|max:255',
        ]);

        $variante = ProductoVariante::findOrFail($id);

        $nuevo = $variante->stock_fisico + $validated['cantidad'];
        if ($nuevo < 0) {
            return response()->json([
                'success' => false,
                'message' => 'El ajuste dejaría el stock en negativo. Stock actual: ' . $variante->stock_fisico
            ], 422);
        }

        $variante->update(['stock_fisico' => $nuevo]);

        // Registrar auditoría de ajuste de stock
        AjusteStock::create([
            'producto_variante_id' => $variante->id,
            'cantidad'             => $validated['cantidad'],
            'motivo'               => $validated['motivo'] ?? 'Ajuste manual de inventario',
            'usuario_id'           => auth()->id() ?? 1,
        ]);

        return response()->json([
            'success'          => true,
            'stock_fisico'     => $variante->fresh()->stock_fisico,
            'stock_disponible' => $variante->fresh()->stock_disponible,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CATEGORÍAS
    // ══════════════════════════════════════════════════════════════════════════

    public function storeCategorias(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:500',
            'icono'       => 'nullable|string|max:50',
        ]);

        $categoria = Categoria::create($validated);

        return response()->json(['success' => true, 'categoria' => $categoria]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ENDPOINT AJAX: SKU SUGERIDO
    // ══════════════════════════════════════════════════════════════════════════

    public function skuSugerido(Request $request)
    {
        $productoId = $request->get('producto_id');
        $atributos  = $request->get('atributos', []);

        $producto = Producto::find($productoId);
        if (!$producto) {
            return response()->json(['sku' => '']);
        }

        $sku = ProductoVariante::generarSkuSugerido($producto->nombre, array_values($atributos));

        return response()->json(['sku' => $sku]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UPLOAD IMAGEN
    // ══════════════════════════════════════════════════════════════════════════

    public function uploadImagen(Request $request)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        if ($request->file('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            return response()->json([
                'success' => true,
                'url'     => \Illuminate\Support\Facades\Storage::url($path)
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No se subió ningún archivo'], 400);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:4096',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ProductImport, $request->file('excel_file'));
            return back()->with('success', 'Productos importados correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al importar archivo: ' . $e->getMessage());
        }
    }
}
