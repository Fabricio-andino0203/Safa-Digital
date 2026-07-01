<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CuentasFinancierasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\CuentaFinanciera::firstOrCreate([
            'nombre' => 'Caja Fuerte / Tesorería',
        ], [
            'tipo' => 'efectivo',
            'saldo_actual' => 0.00,
        ]);

        \App\Models\CuentaFinanciera::firstOrCreate([
            'nombre' => 'Banco Principal',
        ], [
            'tipo' => 'banco',
            'saldo_actual' => 0.00,
        ]);
    }
}
