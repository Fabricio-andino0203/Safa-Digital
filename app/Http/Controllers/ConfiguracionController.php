<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configuracion;
use App\Models\MensajePlantilla;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function index()
    {
        if (MensajePlantilla::count() === 0) {
            (new \Database\Seeders\PlantillasSeeder())->run();
        }

        if (Configuracion::count() === 0) {
            (new \Database\Seeders\ConfiguracionSeeder())->run();
        }

        $configs = Configuracion::pluck('valor', 'llave')->toArray();
        $plantillas = MensajePlantilla::all();
        $usuarios = \App\Models\User::all();
        return view('configuracion.index', compact('configs', 'plantillas', 'usuarios'));
    }

    public function updateEmpresa(Request $request)
    {
        $request->validate([
            'nombre_comercial' => 'required|string',
            'telefono' => 'required|string',
            'direccion' => 'required|string',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,svg|max:2048',
            'favicon' => 'nullable|file|mimes:jpeg,png,jpg,svg,ico|max:1024'
        ]);

        $this->saveSetting('empresa', 'nombre_comercial', $request->nombre_comercial, 'texto');
        $this->saveSetting('empresa', 'telefono', $request->telefono, 'texto');
        $this->saveSetting('empresa', 'direccion', $request->direccion, 'texto');

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ruta = $file->store('config', 'public');
            $absPath = public_path('storage/' . $ruta);
            
            // Si es SVG, omitimos el redimensionamiento ya que no es compatible con GD/Intervention
            $isSvg = $file->getClientOriginalExtension() === 'svg' || $file->getMimeType() === 'image/svg+xml';
            
            if (!$isSvg) {
                // Redimensionar para evitar Memory Exhaustion en DOMPDF
                ini_set('memory_limit', '512M');
                if (file_exists($absPath)) {
                    $info = getimagesize($absPath);
                    if ($info) {
                        $width = $info[0];
                        $height = $info[1];
                        if ($width > 300) {
                            $src = null;
                            if ($info['mime'] == 'image/jpeg') $src = imagecreatefromjpeg($absPath);
                            elseif ($info['mime'] == 'image/png') $src = imagecreatefrompng($absPath);
                            
                            if ($src) {
                                $newWidth = 300;
                                $newHeight = floor($height * ($newWidth / $width));
                                $thumb = imagecreatetruecolor($newWidth, $newHeight);
                                
                                if ($info['mime'] == 'image/png') {
                                    imagealphablending($thumb, false);
                                    imagesavealpha($thumb, true);
                                }
                                
                                imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                                
                                if ($info['mime'] == 'image/jpeg') imagejpeg($thumb, $absPath, 85);
                                elseif ($info['mime'] == 'image/png') imagepng($thumb, $absPath, 8);
                                
                                imagedestroy($thumb);
                                imagedestroy($src);
                            }
                        }
                    }
                }
            }
            
            $this->saveSetting('sistema', 'logo_ruta', 'storage/' . $ruta, 'imagen');
        }

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $ruta = $file->store('config', 'public');
            $this->saveSetting('sistema', 'favicon_ruta', 'storage/' . $ruta, 'imagen');
        }

        return back()->with('success', 'Configuración de empresa actualizada.');
    }

    public function updateTickets(Request $request)
    {
        $request->validate([
            'ticket_mensaje_pie' => 'required|string',
            'terminos_cotizacion' => 'required|string'
        ]);

        $this->saveSetting('ticket', 'ticket_mensaje_pie', $request->ticket_mensaje_pie, 'texto');
        $this->saveSetting('ticket', 'terminos_cotizacion', $request->terminos_cotizacion, 'texto');

        return back()->with('success', 'Configuración de tickets y cotizaciones actualizada.');
    }

    public function updateWhatsapp(Request $request)
    {
        $data = $request->input('plantillas', []);
        
        DB::beginTransaction();
        try {
            foreach ($data as $id => $plantillaData) {
                $plantilla = MensajePlantilla::find($id);
                if ($plantilla) {
                    $plantilla->update([
                        'contenido' => $plantillaData['contenido'],
                        'activa' => isset($plantillaData['activa']) ? true : false,
                    ]);
                }
            }
            DB::commit();
            return back()->with('success', 'Plantillas de WhatsApp actualizadas.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar plantillas.');
        }
    }

    public function limpiarTransacciones(Request $request)
    {
        if (auth()->check() && !auth()->user()->tienePermiso('configuracion')) {
            abort(403, 'No autorizado.');
        }

        DB::beginTransaction();
        try {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            }

            // Tablas transaccionales permitidas para ser vaciadas
            $tablas = [
                'venta_pos_detalles',
                'ventas_pos',
                'caja_movimientos',
                'caja_sesiones',
                'cortes_caja',
                'pedido_historiales',
                'pedido_archivos',
                'pedido_detalles',
                'pedidos',
                'cotizacion_detalles',
                'cotizaciones',
                'compra_detalles',
                'compras',
                'movimiento_tesorerias',
                'ajustes_stock',
                'notifications',
                'gastos'
            ];

            foreach ($tablas as $tabla) {
                if (\Schema::hasTable($tabla)) {
                    DB::table($tabla)->truncate();
                }
            }

            // REGLA DE MODIFICACIÓN: Para el inventario, establecer stock en cero sin borrar productos ni variantes
            if (\Schema::hasTable('producto_variantes')) {
                DB::table('producto_variantes')->update([
                    'stock_fisico' => 0,
                    'stock_reservado' => 0
                ]);
            }
            if (\Schema::hasColumn('productos', 'stock')) {
                DB::table('productos')->update(['stock' => 0]);
            }

            // Restablecer saldo de cuentas financieras centralizadas
            if (\Schema::hasTable('cuenta_financieras')) {
                DB::table('cuenta_financieras')->update(['saldo_actual' => 0.00]);
            }

            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            }

            DB::commit();

            return redirect()->route('configuracion.index')->with('success', 'El historial transaccional ha sido borrado y los niveles de stock se han restablecido a cero exitosamente. Los datos maestros han sido protegidos.');

        } catch (\Exception $e) {
            DB::rollBack();
            try {
                if (DB::getDriverName() === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = ON;');
                } else {
                    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
                }
            } catch (\Exception $ex) {}

            return back()->with('error', 'Error al restablecer los datos transaccionales: ' . $e->getMessage());
        }
    }

    private function saveSetting($grupo, $llave, $valor, $tipo)
    {
        Configuracion::updateOrCreate(
            ['llave' => $llave],
            ['grupo' => $grupo, 'valor' => $valor, 'tipo' => $tipo]
        );
        Cache::forget('configuracion_' . $llave);
    }
}
