<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyGdprController extends Controller
{
    /**
     * Verificar firma HMAC de Shopify
     */
    private function verifyWebhook(Request $request): bool
    {
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $secret = config('shopify.client_secret');
        
        if (!$hmac || !$secret) {
            return false;
        }
        
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
        
        return hash_equals($hmac, $calculatedHmac);
    }

    /**
     * Webhook: Solicitud de datos del cliente (GDPR)
     * Shopify solicita que proporcionemos todos los datos que tenemos del cliente
     */
    public function customersDataRequest(Request $request)
    {
        // Verificar firma HMAC
        if (!$this->verifyWebhook($request)) {
            Log::warning('GDPR Webhook: Firma HMAC inválida', [
                'webhook' => 'customers/data_request',
                'ip' => $request->ip()
            ]);
            return response('Unauthorized', 401);
        }

        $data = $request->json()->all();
        
        Log::info('GDPR: Solicitud de datos del cliente recibida', [
            'shop_domain' => $data['shop_domain'] ?? null,
            'customer_id' => $data['customer']['id'] ?? null,
            'customer_email' => $data['customer']['email'] ?? null,
        ]);

        // TODO: Implementar lógica para recopilar y enviar datos del cliente
        // Por ahora solo registramos la solicitud
        
        return response('', 200);
    }

    /**
     * Webhook: Solicitud de eliminación de datos del cliente (GDPR)
     * Shopify solicita que eliminemos todos los datos del cliente
     */
    public function customersRedact(Request $request)
    {
        // Verificar firma HMAC
        if (!$this->verifyWebhook($request)) {
            Log::warning('GDPR Webhook: Firma HMAC inválida', [
                'webhook' => 'customers/redact',
                'ip' => $request->ip()
            ]);
            return response('Unauthorized', 401);
        }

        $data = $request->json()->all();
        
        Log::info('GDPR: Solicitud de eliminación de datos del cliente', [
            'shop_domain' => $data['shop_domain'] ?? null,
            'customer_id' => $data['customer']['id'] ?? null,
            'customer_email' => $data['customer']['email'] ?? null,
        ]);

        // TODO: Implementar lógica para eliminar datos del cliente
        // - Eliminar de integracion_configs
        // - Eliminar de solicitudes
        // - Eliminar de facturas/boletas relacionadas
        // - Anonimizar datos si es necesario por regulaciones
        
        return response('', 200);
    }

    /**
     * Webhook: Solicitud de eliminación de datos de la tienda (GDPR)
     * Shopify solicita que eliminemos todos los datos de la tienda
     */
    public function shopRedact(Request $request)
    {
        // Verificar firma HMAC
        if (!$this->verifyWebhook($request)) {
            Log::warning('GDPR Webhook: Firma HMAC inválida', [
                'webhook' => 'shop/redact',
                'ip' => $request->ip()
            ]);
            return response('Unauthorized', 401);
        }

        $data = $request->json()->all();
        
        Log::info('GDPR: Solicitud de eliminación de datos de la tienda', [
            'shop_domain' => $data['shop_domain'] ?? null,
            'shop_id' => $data['shop_id'] ?? null,
        ]);

        // TODO: Implementar lógica para eliminar todos los datos de la tienda
        // - Eliminar integracion_configs de esta tienda
        // - Eliminar solicitudes relacionadas
        // - Eliminar webhooks
        // - Eliminar facturas/boletas
        // - Eliminar mappings de productos
        
        return response('', 200);
    }
}
