<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlowController extends Controller
{
    private $apiKey;
    private $secretKey;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('flow.api_key');
        $this->secretKey = config('flow.secret_key');
        $this->apiUrl = config('flow.api_url');
    }

    /**
     * Mostrar formulario de pago
     */
    public function showPaymentForm()
    {
        return view('flow.payment-form');
    }

    /**
     * Crear orden de pago en Flow
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:350',
            'subject' => 'required|string|max:255',
        ]);

        // Parámetros requeridos por Flow
        $params = [
            'apiKey' => $this->apiKey,
            'commerceOrder' => uniqid('ORDER-'), // ID único de tu orden
            'subject' => $request->subject,
            'currency' => 'CLP',
            'amount' => $request->amount,
            'email' => 'elianfa3000@gmail.com', // Email que funciona con Flow
            'urlConfirmation' => route('flow.confirmation'),
            'urlReturn' => route('flow.return'),
            'optional' => json_encode([
                'user_id' => auth()->id(),
                'custom_data' => 'datos adicionales'
            ]),
        ];

        // Firmar parámetros
        $params['s'] = $this->signParams($params);

        // Debug temporal - remover en producción
        // Log::info('Flow API Request', [
        //     'url' => "{$this->apiUrl}/payment/create",
        //     'params' => $params,
        //     'api_key_length' => strlen($this->apiKey),
        //     'secret_key_length' => strlen($this->secretKey)
        // ]);

        try {
            // Llamar a Flow API
            $response = Http::withoutVerifying()->asForm()->post("{$this->apiUrl}/payment/create", $params);

            if ($response->successful()) {
                $data = $response->json();

                // Construir URL de checkout
                $checkoutUrl = $data['url'] . '?token=' . $data['token'];

                // Guardar orden en tu base de datos si es necesario
                // Order::create([...]);

                return redirect($checkoutUrl);
            }

            return back()->withErrors(['error' => 'Error al crear el pago: ' . $response->body()]);
        } catch (\Exception $e) {
            Log::error('Error Flow createPayment: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al procesar el pago']);
        }
    }

    /**
     * Flow redirige aquí después del pago
     */
    public function returnFromFlow(Request $request)
    {
        $token = $request->get('token');

        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Token no válido']);
        }

        // Obtener estado del pago
        $paymentStatus = $this->getPaymentStatus($token);

        if ($paymentStatus && isset($paymentStatus['status']) && $paymentStatus['status'] == 2) {
            // Pago exitoso
            return view('flow.success', ['payment' => $paymentStatus]);
        }

        // Pago fallido o pendiente
        return view('flow.failed', ['payment' => $paymentStatus ?? []]);
    }

    /**
     * Webhook de confirmación (Flow llama aquí en segundo plano)
     */
    public function confirmationWebhook(Request $request)
    {
        $token = $request->get('token');

        if (!$token) {
            return response('Token inválido', 400);
        }

        // Obtener estado del pago
        $paymentStatus = $this->getPaymentStatus($token);

        if ($paymentStatus && $paymentStatus['status'] == 2) {
            // Actualizar tu base de datos
            // Order::where('commerce_order', $paymentStatus['commerceOrder'])
            //     ->update(['status' => 'paid', 'flow_order' => $paymentStatus['flowOrder']]);

            Log::info('Pago confirmado', $paymentStatus);
        }

        // IMPORTANTE: Devolver HTTP 200
        return response('OK', 200);
    }

    /**
     * Obtener estado del pago desde Flow
     */
    private function getPaymentStatus($token)
    {
        $params = [
            'apiKey' => $this->apiKey,
            'token' => $token,
        ];

        $params['s'] = $this->signParams($params);

        try {
            $response = Http::withoutVerifying()->get("{$this->apiUrl}/payment/getStatus", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Error getStatus: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Error Flow getStatus: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Firmar parámetros con HMAC SHA256
     */
    private function signParams(array $params)
    {
        // Ordenar alfabéticamente
        ksort($params);

        // Concatenar parámetros
        $toSign = '';
        foreach ($params as $key => $value) {
            $toSign .= $key . $value;
        }

        // Generar firma HMAC SHA256 (método de la línea 212 en routes)
        return hash_hmac('sha256', $toSign, $this->secretKey);
    }

    /**
     * Crear un pago para un plan específico (versión sin BD)
     */
    public function createPlanPayment(Request $request)
    {
        Log::info('createPlanPayment iniciado', ['request_data' => $request->all()]);

        $request->validate([
            'plan_id' => 'required|numeric',
        ]);

        // Simular datos del plan (en producción esto vendría de la BD)
        $planData = [
            'id' => $request->plan_id,
            'nombre' => 'Plan Demo',
            'empresa' => 'Empresa Demo',
            'precio' => 50 // USD
        ];

        // Convertir precio de USD a CLP
        $amountCLP = $planData['precio'] * 800;

        // Parámetros requeridos por Flow
        $params = [
            'apiKey' => $this->apiKey,
            'commerceOrder' => uniqid('PLAN-'), // ID único de tu orden
            'subject' => 'Plan ' . $planData['nombre'] . ' - ' . $planData['empresa'],
            'currency' => 'CLP',
            'amount' => $amountCLP,
            'email' => 'elianfa3000@gmail.com',
            'urlConfirmation' => route('flow.confirmation'),
            'urlReturn' => route('flow.return'),
            'optional' => json_encode([
                'plan_id' => $planData['id'],
                'user_id' => auth()->id(),
            ]),
        ];

        // Firmar parámetros
        $params['s'] = $this->signParams($params);

        Log::info('Datos para Flow', ['payment_data' => $params]);

        try {
            // Llamar a Flow API
            $response = Http::withoutVerifying()->asForm()->post("{$this->apiUrl}/payment/create", $params);

            Log::info('Respuesta de Flow', ['response' => $response->json()]);

            if ($response->successful()) {
                $data = $response->json();

                // Construir URL de checkout
                $checkoutUrl = $data['url'] . '?token=' . $data['token'];

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'redirect_url' => $checkoutUrl,
                    'plan_data' => $planData
                ]);
            }

            Log::error('Error de Flow', ['error' => $response->body()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el pago: ' . $response->body()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Flow Service Exception', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión con Flow: ' . $e->getMessage()
            ], 500);
        }
    }
}
