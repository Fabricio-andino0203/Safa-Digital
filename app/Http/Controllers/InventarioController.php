<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index()
    {
        $productos = Producto::where('tipo', 'producto')->get();
        $materiales = Producto::where('tipo', 'material')->get();

        return view('inventario.index', compact('productos', 'materiales'));
    }

    public function updateStock(Request $request, $id)
    {
        $validated = $request->validate([
            'cantidad' => 'required|integer' // Permite sumar (positivos) o restar (negativos) rápidamente
        ]);

        $producto = Producto::findOrFail($id);
        
        $producto->stock += $validated['cantidad'];
        
        // Opcional: Asegurarnos de que el stock no sea negativo
        if ($producto->stock < 0) {
            $producto->stock = 0;
        }
        
        $producto->save();

        return response()->json([
            'success' => true, 
            'nuevo_stock' => $producto->stock,
            'mensaje' => 'Stock actualizado correctamente'
        ]);
    }
}
