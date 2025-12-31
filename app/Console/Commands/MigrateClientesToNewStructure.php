<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Console\Command;

class MigrateClientesToNewStructure extends Command
{
    protected $signature = 'clientes:migrate';
    protected $description = 'Migra usuarios con rol cliente a la nueva estructura de tabla clientes';

    public function handle()
    {
        $this->info('Iniciando migración de clientes...');

        // Obtener todos los usuarios con rol cliente
        $usuarios = User::role('cliente')->get();

        if ($usuarios->isEmpty()) {
            $this->warn('No se encontraron usuarios con rol cliente.');
            return 0;
        }

        $this->info("Se encontraron {$usuarios->count()} usuarios con rol cliente.");

        $migrados = 0;
        $existentes = 0;

        foreach ($usuarios as $user) {
            // Verificar si ya existe en la tabla clientes
            if (Cliente::where('user_id', $user->id)->exists()) {
                $existentes++;
                $this->line("  - {$user->name} ya existe en la tabla clientes");
                continue;
            }

            // Crear registro en tabla clientes
            Cliente::create([
                'user_id' => $user->id,
                'empresa' => null,
                'rut' => null,
                'telefono' => null,
                'telefono_secundario' => null,
                'direccion' => null,
                'ciudad' => null,
                'region' => null,
                'codigo_postal' => null,
                'giro' => null,
                'notas' => 'Migrado automáticamente',
                'estado' => 'activo',
            ]);

            $migrados++;
            $this->info("  ✓ {$user->name} migrado exitosamente");
        }

        $this->newLine();
        $this->info("Migración completada:");
        $this->info("  - Migrados: {$migrados}");
        $this->info("  - Ya existentes: {$existentes}");
        $this->info("  - Total: {$usuarios->count()}");

        return 0;
    }
}
