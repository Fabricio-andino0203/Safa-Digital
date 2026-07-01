<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar usuarios antiguos
        User::truncate();

        // Crear administrador por defecto con username
        User::create([
            'name'     => 'Administrador',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'rol'      => 'admin',
            'permisos' => ['pedidos', 'pos', 'clientes', 'cotizaciones', 'inventario', 'compras', 'caja', 'configuracion'],
        ]);

        $this->call(CuentasFinancierasSeeder::class);
    }
}
