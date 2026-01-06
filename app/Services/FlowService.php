<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlowService
{
    private $apiKey;
    private $secretKey;
    private $baseUrl;
    private $sandbox;

    public function __construct()
    {
        $this->apiKey = config('flow.api_key');
        $this->secretKey = config('flow.secret_key');
        $this->sandbox = config('flow.sandbox', true);
        $this->baseUrl = config('flow.api_url', 'https://www.flow.cl/api');
    }

    /**
     * Crear un pago
     */
    public function createPayment($data)
    {
        $params = [
            'apiKey' => $this->apiKey,
            'commerceOrder' => $data['order_id'],
            'subject' => $data['subject'],
            'currency' => $data['currency'] ?? 'CLP',
            'amount' => $data['amount'],
            'email' => $data['email'],
            'paymentMethod' => $data['payment_method'] ?? 1, // 1 = Webpay
            'urlConfirmation' => $data['url_confirmation'],
            'urlReturn' => $data['url_return'],
        ];

        Log::info('Parámetros enviados a Flow', ['params' => $params]);

        // Agregar firma
        $params['s'] = $this->generateSignature($params);

        Log::info('Parámetros con firma', ['params_with_signature' => $params]);

        try {
            $response = Http::withoutVerifying()->post($this->baseUrl . '/payment/create', $params);

            Log::info('Respuesta HTTP de Flow', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Flow API Error', ['response' => $response->body()]);
            return ['error' => 'Error al crear el pago: ' . $response->body()];
        } catch (\Exception $e) {
            Log::error('Flow Service Exception', ['message' => $e->getMessage()]);
            return ['error' => 'Error de conexión con Flow: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener estado de un pago
     */
    public function getPaymentStatus($token)
    {
        $params = [
            'apiKey' => $this->apiKey,
            'token' => $token,
        ];

        $params['s'] = $this->generateSignature($params);

        try {
            $response = Http::withoutVerifying()->get($this->baseUrl . '/payment/getStatus', $params);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Error al obtener estado del pago'];
        } catch (\Exception $e) {
            Log::error('Flow Get Status Exception', ['message' => $e->getMessage()]);
            return ['error' => 'Error de conexión con Flow'];
        }
    }

    /**
     * Generar firma para autenticación
     */
    private function generateSignature($params)
    {
        // Ordenar parámetros alfabéticamente
        ksort($params);

        // Crear string para firmar
        $toSign = '';
        foreach ($params as $key => $value) {
            $toSign .= $key . $value;
        }

        // Agregar secret key
        $toSign .= $this->secretKey;

        // Generar hash
        return hash('sha256', $toSign);
    }

    /**
     * Verificar firma de webhook
     */
    public function verifySignature($params, $signature)
    {
        $calculatedSignature = $this->generateSignature($params);
        return hash_equals($calculatedSignature, $signature);
    }
}
