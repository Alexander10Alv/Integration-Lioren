<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST DE ACTUALIZACIÓN DE PLAN ===\n\n";

// Buscar plan ID 1
$plan = App\Models\Plan::find(1);

if (!$plan) {
    echo "❌ Plan ID 1 no encontrado\n";
    exit;
}

echo "Plan encontrado:\n";
echo "ID: {$plan->id}\n";
echo "Nombre: {$plan->nombre}\n";
echo "Precio actual: \${$plan->precio}\n";
echo "Moneda actual: " . ($plan->moneda ?? 'NULL') . "\n\n";

// Intentar actualizar
echo "Actualizando precio a 30...\n";
$plan->precio = 30;
$plan->moneda = 'CLP';
$plan->save();

echo "✅ Plan actualizado\n\n";

// Verificar
$planActualizado = App\Models\Plan::find(1);
echo "Verificación:\n";
echo "Precio nuevo: \${$planActualizado->precio}\n";
echo "Moneda nueva: {$planActualizado->moneda}\n";
