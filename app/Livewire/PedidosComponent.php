<?php

namespace App\Livewire;

use Livewire\Component;

class PedidosComponent extends Component
{
    public function descargarArchivo($rutaArchivo) {
        $ruta = str_replace('storage/', '', $rutaArchivo);
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($ruta)) {
            $this->dispatch('swal-error', ['title' => 'Error', 'message' => 'Archivo no encontrado']);
            return;
        }
        return response()->download(storage_path('app/public/' . $ruta));
    }

    public function render()
    {
        return view('livewire.pedidos-component');
    }
}
