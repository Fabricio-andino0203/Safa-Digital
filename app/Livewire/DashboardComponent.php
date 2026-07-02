<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class DashboardComponent extends Component
{
    public $efectivoHoy = 0;
    public $cuentasCobrar = 0;
    public $transferenciaHoy = 0;

    #[On('transaccion-registrada')]
    public function actualizarTotales()
    {
        // Recalcular
    }

    #[On('update-caja')]
    public function refreshFinanzas()
    {
        $this->mount();
    }

    public function render()
    {
        return view('livewire.dashboard-component');
    }
}
