<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalculadoraController extends Controller
{
    /**
     * Muestra la Calculadora de Precios para Stickers y Corte.
     */
    public function index()
    {
        return view('calculadora.index');
    }
}
