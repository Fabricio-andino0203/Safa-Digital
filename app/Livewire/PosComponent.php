<?php

namespace App\Livewire;

use Livewire\Component;

class PosComponent extends Component
{
    public function cobrarVenta()
    {
        // Procesar cobro...
        
        // Emitir evento para actualizar el corte de caja en tiempo real
        $this->dispatch('venta-completada');
    }

    public function render()
    {
        return view('livewire.pos-component');
    }
}
