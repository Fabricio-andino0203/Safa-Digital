<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    public function run()
    {
        $configs = [
            // Empresa
            ['grupo' => 'empresa', 'llave' => 'nombre_comercial', 'valor' => 'Safa Digital', 'tipo' => 'texto'],
            ['grupo' => 'empresa', 'llave' => 'razon_social', 'valor' => 'Inversiones Solucels', 'tipo' => 'texto'],
            ['grupo' => 'empresa', 'llave' => 'telefono', 'valor' => '+504 0000-0000', 'tipo' => 'texto'],
            ['grupo' => 'empresa', 'llave' => 'direccion', 'valor' => 'Dirección de la empresa', 'tipo' => 'texto'],
            ['grupo' => 'empresa', 'llave' => 'logo_ruta', 'valor' => 'images/logo.png', 'tipo' => 'imagen'],
            
            // Tickets y PDFs
            ['grupo' => 'ticket', 'llave' => 'ticket_mensaje_pie', 'valor' => 'Gracias por su preferencia.', 'tipo' => 'texto'],
            ['grupo' => 'ticket', 'llave' => 'terminos_cotizacion', 'valor' => 'Cotización válida por 15 días.', 'tipo' => 'texto'],
        ];

        foreach ($configs as $config) {
            Configuracion::updateOrCreate(
                ['llave' => $config['llave']],
                $config
            );
        }
    }
}
