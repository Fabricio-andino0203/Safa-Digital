<?php

namespace App\Livewire;

use Livewire\Component;

class ConfiguracionComponent extends Component
{
    public function resetPruebas()
    {
        try {
            // Usar Eloquent query()->delete() de forma segura
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

            // Restablecer stock físico y reservado a 0
            \App\Models\ProductoVariante::query()->update([
                'stock_fisico'    => 0,
                'stock_reservado' => 0,
            ]);

            // Despachar evento éxito con Swal
            $this->dispatch('swal-success', [
                'title' => 'Datos Limpiados',
                'message' => 'El sistema está listo para operar en limpio.'
            ]);

        } catch (\Exception $e) {
            // Despachar error con Swal
            $this->dispatch('swal-error', [
                'title' => 'Error del Sistema',
                'message' => $e->getMessage()
            ]);
        }
    }
}
