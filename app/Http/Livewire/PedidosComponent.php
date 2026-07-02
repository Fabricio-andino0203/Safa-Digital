<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class PedidosComponent extends Component
{
    public function descargarArchivo($rutaArchivo)
    {
        if (Storage::disk('public')->exists($rutaArchivo)) {
            return Storage::disk('public')->download($rutaArchivo);
        }
        $this->emit('swal-error', ['title' => 'Error', 'message' => 'El archivo no se encontró en el disco del servidor.']);
    }

    public function render()
    {
        return view('livewire.pedidos-component');
    }
}
