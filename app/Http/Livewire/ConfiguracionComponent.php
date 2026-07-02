<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ConfiguracionComponent extends Component
{
    public function resetPruebas()
    {
        try {
            \App\Models\VentaPosDetalle::query()->delete();
            \App\Models\VentaPos::query()->delete();
            \App\Models\PedidoArchivo::query()->delete();
            \App\Models\PedidoHistorial::query()->delete();
            \App\Models\PedidoDetalle::query()->delete();
            \App\Models\Pedido::query()->delete();
            \App\Models\CorteCaja::query()->delete();
            \App\Models\CajaMovimiento::query()->delete();
            \App\Models\CajaSesion::query()->delete();
            \App\Models\CotizacionDetalle::query()->delete();
            \App\Models\Cotizacion::query()->delete();

            \App\Models\ProductoVariante::query()->update([
                'stock_fisico'    => 0,
                'stock_reservado' => 0,
            ]);

            // Emitir evento éxito con Swal en Livewire v2
            $this->emit('swal-success', [
                'title' => 'Datos Limpiados',
                'message' => 'El sistema está listo para operar en limpio.'
            ]);

        } catch (\Exception $e) {
            // Emitir error con Swal en Livewire v2
            $this->emit('swal-error', [
                'title' => 'Error del Sistema',
                'message' => $e->getMessage()
            ]);
        }
    }
}
