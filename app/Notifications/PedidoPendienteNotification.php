<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PedidoPendienteNotification extends Notification
{
    use Queueable;

    protected $pedidoId;
    protected $numeroOrden;
    protected $clienteNombre;
    protected $total;

    public function __construct($pedidoId, $numeroOrden, $clienteNombre, $total)
    {
        $this->pedidoId = $pedidoId;
        $this->numeroOrden = $numeroOrden;
        $this->clienteNombre = $clienteNombre;
        $this->total = $total;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => 'pedido_pendiente',
            'titulo'  => 'Nuevo Pedido Pendiente',
            'mensaje' => "Se ha registrado el pedido #{$this->numeroOrden} de {$this->clienteNombre} por L. " . number_format($this->total, 2) . ".",
            'link'    => route('pedidos.index') . "?id=" . $this->pedidoId,
        ];
    }
}
