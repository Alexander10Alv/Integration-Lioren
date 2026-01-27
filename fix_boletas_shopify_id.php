<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ACTUALIZANDO BOLETAS CON SHOPIFY_ORDER_ID ===\n\n";

// Obtener todas las boletas sin shopify_order_id
$boletas = DB::table('boletas')
    ->whereNull('shopify_order_id')
    ->orWhere('shopify_order_id', '')
    ->get();

echo "Boletas a actualizar: " . $boletas->count() . "\n\n";

foreach ($boletas as $boleta) {
    // Extraer el order_number de las observaciones
    // Formato: "Pedido Shopify #1038"
    if (preg_match('/Pedido Shopify #(\d+)/', $boleta->observaciones, $matches)) {
        $orderNumber = $matches[1];
        
        echo "Boleta ID {$boleta->id} (Folio {$boleta->folio}): Order Number #{$orderNumber}\n";
        
        // Buscar el order_id en Shopify usando la API
        // Por ahora, solo mostramos lo que encontramos
        echo "  → Necesita buscar order_id en Shopify para order_number {$orderNumber}\n";
    } else {
        echo "Boleta ID {$boleta->id}: No se pudo extraer order_number de '{$boleta->observaciones}'\n";
    }
}

echo "\n=== SOLUCIÓN TEMPORAL ===\n";
echo "Como no podemos buscar en Shopify fácilmente, la mejor solución es:\n";
echo "1. Crear un NUEVO pedido en Shopify\n";
echo "2. Verificar que se guarde con shopify_order_id\n";
echo "3. Hacer el reembolso de ese pedido nuevo\n\n";
