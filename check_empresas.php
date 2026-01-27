<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== EMPRESAS EN LA BASE DE DATOS ===\n\n";

$empresas = DB::table('empresas')->get();

if ($empresas->count() === 0) {
    echo "❌ No hay empresas registradas\n";
} else {
    echo "Total: {$empresas->count()}\n\n";
    foreach ($empresas as $empresa) {
        echo "ID: {$empresa->id}\n";
        echo "Nombre: {$empresa->nombre}\n";
        echo "Slug: {$empresa->slug}\n";
        echo "Disponible: " . ($empresa->disponible ? 'SÍ' : 'NO') . "\n";
        echo "---\n";
    }
}
