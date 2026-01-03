<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        // Cliente de prueba
        $cliente = User::create([
            'name' => 'Cliente Test',
            'email' => 'cliente@test.com',
            'password' => Hash::make('password'),
            'role' => 'cliente',
        ]);
        $cliente->assignRole('cliente');

        echo "Usuarios creados:\n";
        echo "- Admin: admin@admin.com / password\n";
        echo "- Cliente: cliente@test.com / password\n";
    }
}
