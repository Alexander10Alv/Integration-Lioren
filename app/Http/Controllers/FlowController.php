<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Suscripcion;
use App\Models\Plan;

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
            // Extraer datos del optional
            $optional = $paymentStatus['optional'] ?? '';
            parse_str(str_replace(['|', ':'], ['&', '='], $optional), $optionalData);
            
            $planId = $optionalData['plan_id'] ?? null;
            $userId = $optionalData['user_id'] ?? null;

            // Guardar pago en BD
            $payment = Payment::create([
                'order_id' => $paymentStatus['commerceOrder'],
                'flow_token' => $token,
                'subject' => $paymentStatus['subject'],
                'amount' => $paymentStatus['amount'],
                'currency' => $paymentStatus['currency'],
                'email' => $paymentStatus['payer'],
                'payment_method' => $paymentStatus['paymentMethod'] ?? 0,
                'status' => $paymentStatus['status'],
                'flow_response' => $paymentStatus,
                'paid_at' => now(),
                'user_id' => $userId,
            ]);

            // Si es pago de plan, crear o renovar suscripción
            if ($planId && $userId) {
                $this->crearORenovarSuscripcion($userId, $planId, $payment);
            }

            Log::info('Pago confirmado', $paymentStatus);
        }

        // IMPORTANTE: Devolver HTTP 200
        return response('OK', 200);
    }

    /**
     * Crear o renovar suscripción
     */
    private function crearORenovarSuscripcion($userId, $planId, Payment $payment)
    {
        $fechaInicio = now();
        $fechaFin = now()->addDays(30);
        $proximoPago = $fechaFin->copy();

        // Buscar suscripción activa del usuario para este plan
        $suscripcion = Suscripcion::where('user_id', $userId)
            ->where('plan_id', $planId)
            ->where('estado', 'activa')
            ->first();

        if ($suscripcion) {
            // Renovar: extender 30 días desde la fecha_fin actual
            $fechaInicio = $suscripcion->fecha_fin;
            $fechaFin = $fechaInicio->copy()->addDays(30);
            $proximoPago = $fechaFin->copy();

            $suscripcion->update([
                'fecha_fin' => $fechaFin,
                'proximo_pago' => $proximoPago,
                'estado' => 'activa',
            ]);

            Log::info("Suscripción renovada", ['suscripcion_id' => $suscripcion->id]);
        } else {
            // Crear nueva suscripción
            $suscripcion = Suscripcion::create([
                'user_id' => $userId,
                'plan_id' => $planId,
                'estado' => 'activa',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'proximo_pago' => $proximoPago,
            ]);

            Log::info("Nueva suscripción creada", ['suscripcion_id' => $suscripcion->id]);
        }

        // Actualizar payment con datos de suscripción
        $payment->update([
            'suscripcion_id' => $suscripcion->id,
            'periodo_inicio' => $fechaInicio,
            'periodo_fin' => $fechaFin,
        ]);
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
     * Crear un pago para un plan específico
     */
    public function createPlanPayment(Request $request)
    {
        Log::info('createPlanPayment iniciado', ['request_data' => $request->all()]);

        $request->validate([
            'plan_id' => 'required|numeric',
        ]);

        // Obtener plan desde la base de datos
        $plan = \App\Models\Plan::with('empresa')->find($request->plan_id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan no encontrado'
            ], 404);
        }

        // Obtener email del usuario autenticado
        $userEmail = auth()->user()->email ?? 'cliente@example.com';

        // Parámetros requeridos por Flow
        $params = [
            'apiKey' => $this->apiKey,
            'commerceOrder' => uniqid('PLAN-'), // ID único de tu orden
            'subject' => 'Plan ' . $plan->nombre . ' - ' . $plan->empresa->nombre,
            'currency' => $plan->moneda, // CLP o UF desde la BD
            'amount' => $plan->precio,
            'email' => $userEmail,
            'urlConfirmation' => route('flow.confirmation'),
            'urlReturn' => route('flow.return'),
            // optional se usa internamente para recuperar datos después del pago
            // Flow puede mostrarlo en la página, así que lo dejamos limpio
            'optional' => 'plan_id:' . $plan->id . '|user_id:' . auth()->id(),
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
                    'plan_data' => [
                        'id' => $plan->id,
                        'nombre' => $plan->nombre,
                        'empresa' => $plan->empresa->nombre,
                        'precio' => $plan->precio,
                        'moneda' => $plan->moneda
                    ]
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
