<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\ProductoVariante;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $pedidosActivos = Pedido::whereNotIn('estado', ['Entregado', 'Liquidado', 'Cancelado'])->count();

        // 1. Efectivo en Caja (Hoy)
        $efectivoHoy = \App\Models\CajaMovimiento::whereDate('created_at', today())
            ->where('tipo', 'ingreso')
            ->where('referencia', 'EFECTIVO')
            ->sum('monto');

        // 2. Cuentas por Cobrar (Adeudado)
        $cuentasCobrar = Pedido::whereNotIn('estado', ['Liquidado', 'Cancelado'])
            ->sum('saldo_pendiente');

        // 3. Ingresos por Transferencia/Tarjeta (Hoy)
        $transferenciaHoy = \App\Models\CajaMovimiento::whereDate('created_at', today())
            ->where('tipo', 'ingreso')
            ->whereIn('referencia', ['TRANSFERENCIA', 'TARJETA'])
            ->sum('monto');

        $clientesTotales = Cliente::count();

        $alertasStock = ProductoVariante::whereRaw('(stock_fisico - stock_reservado) < 5')->count();

        $pedidosRecientes = Pedido::with('cliente')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'pedidosActivos',
            'efectivoHoy',
            'cuentasCobrar',
            'transferenciaHoy',
            'clientesTotales',
            'alertasStock',
            'pedidosRecientes'
        ));
    }
}
