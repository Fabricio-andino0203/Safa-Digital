<?php

namespace App\Http\Controllers;

use App\Models\CuentaFinanciera;
use App\Models\MovimientoTesoreria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TesoreriaController extends Controller
{
    public function index()
    {
        $cuentas = CuentaFinanciera::all();
        
        $capitalNeto = $cuentas->sum('saldo_actual');
        $totalBancos = $cuentas->where('tipo', 'banco')->sum('saldo_actual');
        $totalEfectivo = $cuentas->where('tipo', 'efectivo')->sum('saldo_actual');

        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $ingresosMes = MovimientoTesoreria::where('tipo', 'ingreso')
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('monto');

        $egresosMes = MovimientoTesoreria::where('tipo', 'egreso')
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('monto');

        $gananciasMes = $ingresosMes - $egresosMes;

        $movimientos = MovimientoTesoreria::with(['cuenta', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular deudas por pagar (Compras en estado Valorizada)
        $deudasPorPagar = \App\Models\Compra::where('estado', 'Valorizada')->sum('total');

        // Calcular cuentas por cobrar (Pedidos activos con saldo pendiente)
        $deudasPorCobrar = \App\Models\Pedido::where('saldo_pendiente', '>', 0)
            ->whereNotIn('estado', ['Entregado', 'Cancelado'])
            ->sum('saldo_pendiente');

        return view('tesoreria.index', compact(
            'cuentas', 
            'capitalNeto', 
            'totalBancos', 
            'totalEfectivo', 
            'gananciasMes', 
            'movimientos',
            'deudasPorPagar',
            'deudasPorCobrar'
        ));
    }

    public function registrarMovimiento(Request $request)
    {
        $request->validate([
            'cuenta_id' => 'required|exists:cuenta_financieras,id',
            'tipo'      => 'required|in:ingreso,egreso',
            'monto'     => 'required|numeric|min:0.01',
            'concepto'  => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $cuenta = CuentaFinanciera::findOrFail($request->cuenta_id);

            MovimientoTesoreria::create([
                'cuenta_id' => $request->cuenta_id,
                'tipo'      => $request->tipo,
                'monto'     => $request->monto,
                'concepto'  => $request->concepto,
                'usuario_id'=> Auth::id() ?? 1,
            ]);

            if ($request->tipo === 'ingreso') {
                $cuenta->increment('saldo_actual', $request->monto);
            } else {
                $cuenta->decrement('saldo_actual', $request->monto);
            }

            DB::commit();

            $label = $request->tipo === 'ingreso' ? 'Depósito' : 'Retiro';
            return redirect()->route('tesoreria.index')->with('success', "{$label} registrado con éxito por L. " . number_format($request->monto, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar el movimiento: ' . $e->getMessage())->withInput();
        }
    }

    public function trasladarFondos(Request $request)
    {
        $request->validate([
            'cuenta_origen_id'  => 'required|exists:cuenta_financieras,id',
            'cuenta_destino_id' => 'required|exists:cuenta_financieras,id|different:cuenta_origen_id',
            'monto'             => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $cuentaOrigen = CuentaFinanciera::findOrFail($request->cuenta_origen_id);
            $cuentaDestino = CuentaFinanciera::findOrFail($request->cuenta_destino_id);

            if ($cuentaOrigen->saldo_actual < $request->monto) {
                throw new \Exception("La cuenta de origen '{$cuentaOrigen->nombre}' no tiene fondos suficientes. Saldo actual: L. " . number_format($cuentaOrigen->saldo_actual, 2));
            }

            $concepto = "Traslado de fondos desde '{$cuentaOrigen->nombre}' hacia '{$cuentaDestino->nombre}'";

            // 1. Registrar egreso en cuenta origen
            MovimientoTesoreria::create([
                'cuenta_id' => $cuentaOrigen->id,
                'tipo'      => 'egreso',
                'monto'     => $request->monto,
                'concepto'  => $concepto,
                'usuario_id'=> Auth::id() ?? 1,
            ]);
            $cuentaOrigen->decrement('saldo_actual', $request->monto);

            // 2. Registrar ingreso en cuenta destino
            MovimientoTesoreria::create([
                'cuenta_id' => $cuentaDestino->id,
                'tipo'      => 'ingreso',
                'monto'     => $request->monto,
                'concepto'  => $concepto,
                'usuario_id'=> Auth::id() ?? 1,
            ]);
            $cuentaDestino->increment('saldo_actual', $request->monto);

            DB::commit();

            return redirect()->route('tesoreria.index')->with('success', "Traslado de fondos por L. " . number_format($request->monto, 2) . " completado exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function revertirMovimiento($id)
    {
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        DB::beginTransaction();
        try {
            $movimiento = MovimientoTesoreria::findOrFail($id);
            $cuenta = $movimiento->cuenta;

            if ($cuenta->tipo === 'efectivo') {
                // Regla estricta de caja: aplicar reversión sobre la sesión de caja activa
                $sesionActiva = \App\Models\CajaSesion::sesionAbierta();
                if (!$sesionActiva) {
                    throw new \Exception("No hay una sesión de caja activa abierta para realizar esta reversión en efectivo.");
                }

                if ($movimiento->tipo === 'egreso') {
                    // Revertir egreso (Retiro): devolver dinero a la caja activa (ingreso)
                    \App\Models\CajaMovimiento::create([
                        'caja_sesion_id' => $sesionActiva->id,
                        'tipo'           => 'ingreso',
                        'monto'          => $movimiento->monto,
                        'concepto'       => "Reversión de Retiro: " . $movimiento->concepto,
                        'referencia'     => 'Efectivo',
                        'fecha'          => now()->toDateString(),
                    ]);
                } else {
                    // Revertir ingreso (Depósito): descontar dinero de la caja activa (egreso)
                    \App\Models\CajaMovimiento::create([
                        'caja_sesion_id' => $sesionActiva->id,
                        'tipo'           => 'egreso',
                        'monto'          => $movimiento->monto,
                        'concepto'       => "Reversión de Depósito: " . $movimiento->concepto,
                        'referencia'     => 'Efectivo',
                        'fecha'          => now()->toDateString(),
                    ]);
                }
            } else {
                // Cuenta bancaria: ajustar saldo de forma inversa
                if ($movimiento->tipo === 'ingreso') {
                    $cuenta->decrement('saldo_actual', $movimiento->monto);
                } else {
                    $cuenta->increment('saldo_actual', $movimiento->monto);
                }
            }

            $movimiento->delete();
            DB::commit();

            return redirect()->route('tesoreria.index')->with('success', 'Movimiento financiero revertido y eliminado con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al revertir el movimiento: ' . $e->getMessage());
        }
    }

    public function editarMovimiento(Request $request, $id)
    {
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        $request->validate([
            'monto'    => 'required|numeric|min:0.01',
            'concepto' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $movimiento = MovimientoTesoreria::findOrFail($id);
            $cuenta = $movimiento->cuenta;

            $montoViejo = $movimiento->monto;
            $montoNuevo = $request->monto;
            $delta = $montoNuevo - $montoViejo;

            $movimiento->update([
                'monto'    => $montoNuevo,
                'concepto' => $request->concepto,
            ]);

            if (abs($delta) > 0.0001) {
                if ($movimiento->tipo === 'ingreso') {
                    // Si el depósito aumentó, sumamos delta. Si disminuyó, restamos delta.
                    $cuenta->increment('saldo_actual', $delta);
                } else {
                    // Si el retiro aumentó, restamos delta de la cuenta. Si disminuyó, sumamos delta.
                    $cuenta->decrement('saldo_actual', $delta);
                }
            }

            DB::commit();

            return redirect()->route('tesoreria.index')->with('success', 'Movimiento financiero editado con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al editar el movimiento: ' . $e->getMessage());
        }
    }
}
