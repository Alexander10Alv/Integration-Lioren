<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ESTRUCTURA DE LA TABLA PLANES ===\n\n";

// Obtener estructura de la tabla
$columns = DB::select("DESCRIBE planes");
echo "Columnas de la tabla 'planes':\n";
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type}) - Null: {$column->Null}, Default: {$column->Default}\n";
}

echo "\n=== DATOS ACTUALES EN PLANES ===\n\n";

// Obtener todos los planes
$planes = DB::table('planes')->get();
echo "Total de planes: " . $planes->count() . "\n\n";

foreach ($planes as $plan) {
    echo "Plan ID: {$plan->id}\n";
    echo "  Nombre: {$plan->nombre}\n";
    echo "  Precio: " . ($plan->precio ?? 'NULL') . "\n";
    echo "  Moneda: " . ($plan->moneda ?? 'NULL') . "\n";
    echo "  Empresa ID: {$plan->empresa_id}\n";
    echo "  Activo: {$plan->activo}\n";
    echo "  ---\n";
}
