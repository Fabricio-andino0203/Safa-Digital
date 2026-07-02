<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class CorteCajaComponent extends Component
{
    public $dineroEsperado = 0;

    #[On('venta-completada')]
    public function refreshCorte()
    {
        // Refrescar los totales del corte de caja dinámicamente
    }

    public function render()
    {
        return view('livewire.corte-caja-component');
    }
}
