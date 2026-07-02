<?php

namespace App\Http\Livewire;

use Livewire\Component;

class DashboardComponent extends Component
{
    protected $listeners = [
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
        return view('livewire.dashboard-component');
    }
}
