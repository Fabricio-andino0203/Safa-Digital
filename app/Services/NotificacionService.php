<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\MensajePlantilla;

class NotificacionService
{
    /**
     * Procesa una plantilla de notificación para un pedido específico.
     * Reemplaza las variables dinámicas por los valores reales del modelo.
     *
     * @param int $pedido_id
     * @param string $evento
     * @return string|null Retorna el texto formateado o null si la plantilla no existe o está inactiva.
     */
    public function procesarPlantilla($pedido_id, $evento)
    {
        $plantilla = MensajePlantilla::where('evento', $evento)
                                     ->where('activa', true)
                                     ->first();

        if (!$plantilla) {
            return null;
        }

        $pedido = Pedido::with('cliente')->find($pedido_id);
        if (!$pedido) {
            return null;
        }

        $texto = $plantilla->contenido;

        $reemplazos = [
            '{cliente}'       => $pedido->cliente ? $pedido->cliente->nombre : 'Cliente',
            '{telefono}'      => $pedido->cliente ? $pedido->cliente->telefono : '',
            '{orden}'         => $pedido->numero_orden,
            '{fecha_entrega}' => $pedido->fecha_estimada_entrega ? $pedido->fecha_estimada_entrega->format('d/m/Y') : 'Por definir',
            '{total}'         => number_format($pedido->total_pedido, 2),
            '{abonado}'       => number_format($pedido->total_abonado, 2),
            '{saldo}'         => number_format($pedido->saldo_pendiente, 2),
            '{empresa}'       => 'Inversiones Solucels',
            '{link}'          => route('pedidos.track', $pedido->numero_orden),
            '[link_rastreo]'  => route('pedidos.track', $pedido->numero_orden),
            '{link_rastreo}'  => route('pedidos.track', $pedido->numero_orden),
            '[rastreo]'       => route('pedidos.track', $pedido->numero_orden),
        ];

        return str_replace(array_keys($reemplazos), array_values($reemplazos), $texto);
    }
}
