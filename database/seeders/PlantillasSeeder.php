<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MensajePlantilla;

class PlantillasSeeder extends Seeder
{
    public function run()
    {
        $plantillas = [
            [
                'nombre' => 'Pedido Creado',
                'evento' => 'Pedido Creado',
                'contenido' => "Hola {cliente}.\n\nTu pedido #{orden} ha sido registrado exitosamente.\n\nFecha estimada de entrega:\n{fecha_entrega}\n\nPara continuar, por favor envíanos las imágenes, logos o archivos necesarios respondiendo a este mensaje.\n\nGracias por elegirnos.\n\n🎉 Promoción de temporada\nObtén 10% de descuento en tu próxima compra mostrando este mensaje.",
                'activa' => true,
            ],
            [
                'nombre' => 'Solicitud de Archivos',
                'evento' => 'Esperando Material del Cliente',
                'contenido' => "Hola {cliente}.\n\nPara iniciar la producción de tu pedido #{orden} necesitamos que nos envíes las imágenes, diseños o referencias correspondientes.\n\nPuedes responder directamente a este mensaje.\n\nQuedamos atentos.",
                'activa' => true,
            ],
            [
                'nombre' => 'Diseño Listo',
                'evento' => 'Diseño Terminado',
                'contenido' => "Hola {cliente}.\n\nEl diseño de tu pedido #{orden} está listo para revisión.\n\nPor favor confirma si podemos continuar con producción.",
                'activa' => true,
            ],
            [
                'nombre' => 'Pedido en Producción',
                'evento' => 'Producción Iniciada',
                'contenido' => "Hola {cliente}.\n\nTe informamos que tu pedido #{orden} acaba de entrar en nuestra línea de producción. Te avisaremos en cuanto esté listo para entrega.",
                'activa' => true,
            ],
            [
                'nombre' => 'Pedido Listo',
                'evento' => 'Pedido Listo para Entrega',
                'contenido' => "Hola {cliente}.\n\nTu pedido #{orden} ya está listo para ser retirado.\n\nSaldo pendiente:\nL. {saldo}\n\nTe esperamos.",
                'activa' => true,
            ]
        ];

        foreach ($plantillas as $p) {
            MensajePlantilla::updateOrCreate(
                ['evento' => $p['evento']],
                $p
            );
        }
    }
}
