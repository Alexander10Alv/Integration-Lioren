<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG PLAN UPDATE ===\n\n";

// Ver las rutas registradas
$routes = Route::getRoutes();
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'planes') && in_array('PUT', $route->methods())) {
        echo "Ruta PUT encontrada:\n";
        echo "  URI: {$route->uri()}\n";
        echo "  Name: {$route->getName()}\n";
        echo "  Action: {$route->getActionName()}\n";
        echo "\n";
    }
}

// Ver planes en la base de datos
echo "=== PLANES EN LA BASE DE DATOS ===\n\n";
$planes = DB::table('planes')->get();
foreach ($planes as $plan) {
    echo "Plan ID: {$plan->id}\n";
    echo "  Nombre: {$plan->nombre}\n";
    echo "  Precio: {$plan->precio}\n";
    echo "  Moneda: {$plan->moneda}\n";
    echo "  URL esperada: /planes/{$plan->id}\n";
    echo "\n";
}
