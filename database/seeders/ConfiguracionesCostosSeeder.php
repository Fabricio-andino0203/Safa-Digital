<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Cache;

class ConfiguracionesCostosSeeder extends Seeder
{
    /**
     * Inyecta las variables globales de costeo de la Calculadora Comercial.
     */
    public function run(): void
    {
        $costos = [
            ['grupo' => 'calculadora', 'llave' => 'calc_costo_yarda', 'valor' => '150', 'tipo' => 'numero'],
            ['grupo' => 'calculadora', 'llave' => 'calc_costo_pulgada_cuadrada', 'valor' => '0.08', 'tipo' => 'numero'],
            ['grupo' => 'calculadora', 'llave' => 'calc_costo_banner_in2', 'valor' => '0.05', 'tipo' => 'numero'],
            ['grupo' => 'calculadora', 'llave' => 'calc_costo_troquelado_in2', 'valor' => '0.09', 'tipo' => 'numero'],
            ['grupo' => 'calculadora', 'llave' => 'calc_costo_impreso_in2', 'valor' => '0.08', 'tipo' => 'numero'],
            ['grupo' => 'calculadora', 'llave' => 'calc_costo_pvc_3mm', 'valor' => '0.12', 'tipo' => 'numero'],
            ['grupo' => 'calculadora', 'llave' => 'calc_costo_pvc_5mm', 'valor' => '0.18', 'tipo' => 'numero'],
            ['grupo' => 'calculadora', 'llave' => 'calc_costo_fijo_transporte', 'valor' => '100', 'tipo' => 'numero'],
            ['grupo' => 'calculadora', 'llave' => 'calc_margen_ganancia_default', 'valor' => '50', 'tipo' => 'numero'],
        ];

        foreach ($costos as $costo) {
            Configuracion::updateOrCreate(
                ['llave' => $costo['llave']],
                $costo
            );
            Cache::forget('configuracion_' . $costo['llave']);
        }
    }
}
