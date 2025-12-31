<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Permisos de Integración
            'view-integracion',
            'manage-integracion',
            'configure-webhooks',
            
            // Permisos de Bodegas
            'view-bodegas',
            'manage-bodegas',
            
            // Permisos de Boletas/Facturas
            'view-boletas',
            'manage-boletas',
            'emit-documentos',
            
            // Permisos de Productos
            'view-productos',
            'sync-productos',
            
            // Permisos de Pedidos
            'view-pedidos',
            'view-own-pedidos',
            
            // Permisos de Facturas Cliente
            'view-facturas',
            'view-own-facturas',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear rol Admin
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Admin tiene todos los permisos
        $adminRole->givePermissionTo(Permission::all());

        // Crear rol Cliente
        $clienteRole = Role::firstOrCreate(['name' => 'cliente']);
        
        // Cliente solo tiene permisos limitados
        $clienteRole->givePermissionTo([
            'view-own-pedidos',
            'view-own-facturas',
        ]);

        $this->command->info('Roles y permisos creados exitosamente!');
        $this->command->info('- Admin: Todos los permisos');
        $this->command->info('- Cliente: Solo ver sus pedidos y facturas');
    }
}
