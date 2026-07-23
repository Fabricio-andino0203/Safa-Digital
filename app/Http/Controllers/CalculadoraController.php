<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Cache;

class CalculadoraController extends Controller
{
    /**
     * Muestra la Calculadora de Precios para Stickers y Corte.
     */
    public function index()
    {
        $settings = [
            'calc_costo_yarda'             => (float) get_setting('calc_costo_yarda', 150),
            'calc_costo_pulgada_cuadrada' => (float) get_setting('calc_costo_pulgada_cuadrada', 0.08),
            'calc_costo_banner_in2'       => (float) get_setting('calc_costo_banner_in2', 0.05),
            'calc_costo_troquelado_in2'   => (float) get_setting('calc_costo_troquelado_in2', 0.09),
            'calc_costo_impreso_in2'      => (float) get_setting('calc_costo_impreso_in2', 0.08),
            'calc_costo_pvc_3mm'          => (float) get_setting('calc_costo_pvc_3mm', 0.12),
            'calc_costo_pvc_5mm'          => (float) get_setting('calc_costo_pvc_5mm', 0.18),
            'calc_costo_fijo_transporte'  => (float) get_setting('calc_costo_fijo_transporte', 100),
            'calc_margen_ganancia_default'=> (float) get_setting('calc_margen_ganancia_default', 50),
        ];

        return view('calculadora.index', compact('settings'));
    }

    /**
     * Guarda la configuración global de costos en la base de datos (Admin).
     */
    public function guardarConfiguracion(Request $request)
    {
        $validated = $request->validate([
            'calc_costo_yarda'             => 'required|numeric|min:0',
            'calc_costo_pulgada_cuadrada' => 'required|numeric|min:0',
            'calc_costo_banner_in2'       => 'nullable|numeric|min:0',
            'calc_costo_troquelado_in2'   => 'nullable|numeric|min:0',
            'calc_costo_impreso_in2'      => 'nullable|numeric|min:0',
            'calc_costo_pvc_3mm'          => 'nullable|numeric|min:0',
            'calc_costo_pvc_5mm'          => 'nullable|numeric|min:0',
            'calc_costo_fijo_transporte'  => 'nullable|numeric|min:0',
            'calc_margen_ganancia_default'=> 'required|numeric|min:0',
        ]);

        foreach ($validated as $llave => $valor) {
            Configuracion::updateOrCreate(
                ['llave' => $llave],
                ['grupo' => 'calculadora', 'valor' => (string) $valor, 'tipo' => 'numero']
            );
            Cache::forget('configuracion_' . $llave);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Valores de costeo vinculados a configuración global.',
            'settings' => $validated,
        ]);
    }

    /**
     * Envía la cotización de la calculadora al módulo de Creación de Pedidos a través de la sesión.
     */
    public function enviarAPedido(Request $request)
    {
        $validated = $request->validate([
            'tipo_material'          => 'required|string',
            'ancho'                  => 'required|numeric|min:0.1',
            'alto'                   => 'required|numeric|min:0.1',
            'unidad'                 => 'required|string',
            'cantidad'               => 'required|integer|min:1',
            'precio_unitario'        => 'required|numeric|min:0',
            'precio_total'           => 'required|numeric|min:0',
            'costo_produccion_total' => 'required|numeric|min:0',
        ]);

        $datos = [
            'material'         => $validated['tipo_material'],
            'ancho'            => $validated['ancho'],
            'alto'             => $validated['alto'],
            'unidad'           => $validated['unidad'],
            'cantidad'         => $validated['cantidad'],
            'precio_unitario'  => $validated['precio_unitario'],
            'precio_total'     => $validated['precio_total'],
            'costo_total'      => $validated['costo_produccion_total'],
            'es_maquila'       => in_array($validated['tipo_material'], ['banner', 'pvc', 'troquelado', 'impreso']),
        ];

        session()->put('draft_calculadora', $datos);

        return redirect()->route('pedidos.index');
    }
}
