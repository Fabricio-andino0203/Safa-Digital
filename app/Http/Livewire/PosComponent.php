<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PosComponent extends Component
{
    public array $selectedExtras = [];

    public function abrirModalExtras()
    {
        $this->selectedExtras = [];
    }

    public function cobrarVenta()
    {
        // Procesar cobro...
        
        // Emitir evento para actualizar el corte de caja en tiempo real
        $this->emit('venta-completada');
        $this->emit('update-caja');
    }

    public function render()
    {
        return view('livewire.pos-component');
    }
}
