<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CorteCajaComponent extends Component
{
    protected $listeners = [
        'venta-completada' => '$refresh',
        'transaccion-registrada' => 'actualizarTotales',
        'update-caja' => 'refreshFinanzas'
    ];

    public function actualizarTotales()
    {
        // Recalcular
    }

    public function refreshFinanzas()
    {
        // Recalcular
    }

    public function render()
    {
        return view('livewire.corte-caja-component');
    }
}
