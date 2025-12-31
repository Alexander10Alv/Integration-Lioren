<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear usuario cliente si no existe
        $cliente = User::firstOrCreate(
            ['email' => 'cliente@demo.com'],
            [
                'name' => 'Cliente Demo',
                'password' => Hash::make('12345678'),
            ]
        );

        // Asignar rol de cliente usando Spatie
        if (!$cliente->hasRole('cliente')) {
            $cliente->assignRole('cliente');
        }

        $this->command->info('Usuario cliente creado: cliente@demo.com / 12345678');
    }
}
