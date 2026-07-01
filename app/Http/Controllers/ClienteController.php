<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $totalClientes = $clientes->count();
        $mejorCliente = Cliente::orderByDesc('total_gastado')->first();

        return view('clientes.index', compact('clientes', 'totalClientes', 'mejorCliente'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:255',
        ]);

        $cliente = Cliente::create(array_merge($validated, ['total_gastado' => 0]));

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:255',
        ]);

        $cliente = Cliente::create(array_merge($validated, ['total_gastado' => 0]));

        return response()->json(['success' => true, 'cliente' => $cliente]);
    }
}
