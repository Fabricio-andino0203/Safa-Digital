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

        $ventasHoy = Pedido::whereDate('created_at', today())
            ->where('estado', '!=', 'Cancelado')
            ->sum('total_pedido');

        $clientesTotales = Cliente::count();

        $alertasStock = ProductoVariante::whereRaw('(stock_fisico - stock_reservado) < 5')->count();

        $pedidosRecientes = Pedido::with('cliente')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'pedidosActivos',
            'ventasHoy',
            'clientesTotales',
            'alertasStock',
            'pedidosRecientes'
        ));
    }
}
