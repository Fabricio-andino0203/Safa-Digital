<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear administrador por defecto con username
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Administrador',
                'password' => bcrypt('12345678'),
                'rol'      => 'admin',
                'permisos' => ['pedidos', 'pos', 'clientes', 'cotizaciones', 'inventario', 'compras', 'caja', 'configuracion', 'reportes'],
            ]
        );

        $this->call([
            CuentasFinancierasSeeder::class,
            ConfiguracionSeeder::class,
            ConfiguracionesCostosSeeder::class,
            PlantillasSeeder::class,
        ]);
    }
}
