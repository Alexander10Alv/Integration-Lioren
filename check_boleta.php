<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Buscar últimas boletas
$boletas = DB::table('boletas')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get(['id', 'folio', 'shopify_order_id', 'observaciones', 'status']);

echo "=== ÚLTIMAS BOLETAS ===\n\n";
foreach ($boletas as $boleta) {
    echo "ID: {$boleta->id}\n";
    echo "Folio: {$boleta->folio}\n";
    echo "Shopify Order ID: {$boleta->shopify_order_id}\n";
    echo "Observaciones: {$boleta->observaciones}\n";
    echo "Status: {$boleta->status}\n";
    echo "---\n";
}

// Buscar boleta específica para el order_id del reembolso
echo "\n=== BUSCANDO BOLETA PARA ORDER 7349833957526 ===\n\n";
$boleta = DB::table('boletas')
    ->where('shopify_order_id', '7349833957526')
    ->first();

if ($boleta) {
    echo "✅ ENCONTRADA!\n";
    echo "ID: {$boleta->id}\n";
    echo "Folio: {$boleta->folio}\n";
    echo "Shopify Order ID: {$boleta->shopify_order_id}\n";
} else {
    echo "❌ NO ENCONTRADA\n";
}
