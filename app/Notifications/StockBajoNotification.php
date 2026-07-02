<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockBajoNotification extends Notification
{
    use Queueable;

    protected $varianteSku;
    protected $varianteNombre;
    protected $stockDisponible;

    public function __construct($varianteSku, $varianteNombre, $stockDisponible)
    {
        $this->varianteSku = $varianteSku;
        $this->varianteNombre = $varianteNombre;
        $this->stockDisponible = $stockDisponible;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => 'stock_bajo',
            'titulo'  => 'Stock Bajo Detectado',
            'mensaje' => "La variante '{$this->varianteSku} - {$this->varianteNombre}' tiene stock crítico: {$this->stockDisponible} unidades.",
            'link'    => route('inventario.index'),
        ];
    }
}
