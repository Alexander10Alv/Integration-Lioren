<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== USUARIOS EN LA BASE DE DATOS ===\n\n";

$users = DB::table('users')->get();

if ($users->count() === 0) {
    echo "❌ No hay usuarios registrados\n";
} else {
    echo "Total: {$users->count()}\n\n";
    foreach ($users as $user) {
        echo "ID: {$user->id}\n";
        echo "Nombre: {$user->name}\n";
        echo "Email: {$user->email}\n";
        
        // Obtener roles
        $roles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('roles.name')
            ->toArray();
        
        echo "Roles: " . (empty($roles) ? 'Sin rol' : implode(', ', $roles)) . "\n";
        echo "---\n";
    }
}

echo "\n=== ROLES DISPONIBLES ===\n\n";
$roles = DB::table('roles')->get(['id', 'name']);
foreach ($roles as $role) {
    echo "- {$role->name} (ID: {$role->id})\n";
}
