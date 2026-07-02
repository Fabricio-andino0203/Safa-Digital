<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CorteCajaComponent extends Component
{
    protected $listeners = ['venta-completada' => '$refresh'];

    public function render()
    {
        return view('livewire.corte-caja-component');
    }
}
