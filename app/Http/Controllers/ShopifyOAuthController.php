<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\IntegracionConfig;
use App\Models\Solicitud;

class ShopifyOAuthController extends Controller
{
    /**
     * Iniciar flujo OAuth con Shopify
     */
    public function iniciarOAuth(Request $request)
    {
        $request->validate([
            'shop_url' => ['required', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/'],
            'lioren_api_key' => 'required|string',
            'solicitud_id' => 'required|exists:solicitudes,id'
        ]);

        $shop = $request->shop_url;
        $nonce = Str::random(32);

        // Guardar datos en sesión para recuperarlos en el callback
        session([
            'shopify_nonce' => $nonce,
            'shopify_shop' => $shop,
            'lioren_api_key' => $request->lioren_api_key,
            'solicitud_id' => $request->solicitud_id
        ]);

        // Construir URL de autorización OAuth
        $authUrl = "https://{$shop}/admin/oauth/authorize?" . http_build_query([
            'client_id' => config('shopify.client_id'),
            'scope' => config('shopify.scopes'),
            'redirect_uri' => config('shopify.redirect_uri'),
            'state' => $nonce
        ]);

        Log::info("Iniciando OAuth para shop: {$shop}", ['user_id' => auth()->id()]);

        return redirect($authUrl);
    }

    /**
     * Manejar callback de Shopify después de autorización
     */
    public function handleCallback(Request $request)
    {
        $queryParams = $request->all();
        $code = $queryParams['code'] ?? '';
        $shop = $queryParams['shop'] ?? '';
        $state = $queryParams['state'] ?? '';

        // Validar state (protección CSRF)
        if ($state !== session('shopify_nonce')) {
            Log::error('Estado OAuth inválido', ['state_recibido' => $state]);
            return redirect()->route('cliente.estados-solicitud')
                ->with('error', 'Estado OAuth inválido. Por favor intenta nuevamente.');
        }

        // Validar HMAC
        if (!$this->verifyHmac($queryParams, config('shopify.client_secret'))) {
            Log::error('Firma HMAC inválida', ['shop' => $shop]);
            return redirect()->route('cliente.estados-solicitud')
                ->with('error', 'Firma HMAC inválida. Conexión rechazada por seguridad.');
        }

        try {
            // Intercambiar código por access token
            $accessToken = $this->exchangeCodeForToken($shop, $code);

            // Recuperar datos de sesión
            $solicitudId = session('solicitud_id');
            $liorenApiKey = session('lioren_api_key');

            // Actualizar solicitud con credenciales OAuth
            $solicitud = Solicitud::findOrFail($solicitudId);
            $solicitud->update([
                'tienda_shopify' => $shop,
                'access_token' => $accessToken,
                'api_secret' => config('shopify.client_secret'),
                'api_key' => $liorenApiKey,
            ]);

            // Crear/actualizar IntegracionConfig
            IntegracionConfig::updateOrCreate(
                ['user_id' => auth()->id()],
                [
                    'solicitud_id' => $solicitudId,
                    'shopify_tienda' => $shop,
                    'shopify_token' => $accessToken,
                    'shopify_secret' => config('shopify.client_secret'),
                    'lioren_api_key' => $liorenApiKey,
                    'shop_domain' => $shop,
                    'auth_method' => 'oauth',
                    'oauth_installed_at' => now(),
                    'activo' => false, // Se activará después de conectar
                ]
            );

            // Limpiar sesión
            session()->forget(['shopify_nonce', 'shopify_shop', 'lioren_api_key', 'solicitud_id']);

            Log::info("OAuth exitoso para shop: {$shop}", [
                'user_id' => auth()->id(),
                'solicitud_id' => $solicitudId
            ]);

            return redirect()->route('cliente.estados-solicitud')
                ->with('success', '¡Credenciales guardadas! Ahora puedes conectar la integración.');

        } catch (\Exception $e) {
            Log::error('Error en callback OAuth: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('cliente.estados-solicitud')
                ->with('error', 'Error al conectar: ' . $e->getMessage());
        }
    }

    /**
     * Verificar firma HMAC de Shopify
     */
    private function verifyHmac(array $params, string $secret): bool
    {
        $hmac = $params['hmac'] ?? '';
        
        // Remover hmac y signature de los parámetros
        unset($params['hmac'], $params['signature']);
        
        // Ordenar alfabéticamente
        ksort($params);
        
        // Construir query string
        $queryString = http_build_query($params);
        
        // Calcular HMAC
        $calculatedHmac = hash_hmac('sha256', $queryString, $secret);
        
        // Comparación segura
        return hash_equals($calculatedHmac, $hmac);
    }

    /**
     * Intercambiar código temporal por access token permanente
     */
    private function exchangeCodeForToken(string $shop, string $code): string
    {
        $response = Http::post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => config('shopify.client_id'),
            'client_secret' => config('shopify.client_secret'),
            'code' => $code
        ]);

        if ($response->failed()) {
            Log::error('Error intercambiando código por token', [
                'shop' => $shop,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('No se pudo obtener el access token de Shopify');
        }

        $data = $response->json();
        
        if (!isset($data['access_token'])) {
            throw new \Exception('Respuesta de Shopify no contiene access_token');
        }

        return $data['access_token'];
    }
}
