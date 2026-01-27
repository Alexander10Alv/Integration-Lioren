<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Plan;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'plan_id' => 'required|exists:planes,id',
            ]);

            $solicitud = Solicitud::create([
                'cliente_id' => auth()->id(),
                'plan_id' => $validated['plan_id'],
                'estado' => 'pendiente',
                'email' => auth()->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'solicitud_id' => $solicitud->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $solicitudes = Solicitud::with(['cliente', 'plan.empresa'])
            ->latest()
            ->paginate(10);
        
        return view('admin.solicitudes.index', compact('solicitudes'));
    }

    public function show(Solicitud $solicitud)
    {
        $solicitud->load(['cliente', 'plan.empresa']);
        return view('admin.solicitudes.show', compact('solicitud'));
    }

    public function updateEstado(Request $request, Solicitud $solicitud)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,aprobada,rechazada,en_proceso,activa',
            'notas_admin' => 'nullable|string',
        ]);

        $solicitud->update($validated);

        return redirect()->back()->with('success', 'Estado actualizado');
    }

    public function getConfig(Solicitud $solicitud)
    {
        // Verificar que la solicitud pertenece al usuario autenticado
        if ($solicitud->cliente_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json([
            'tienda_shopify' => $solicitud->tienda_shopify,
            'access_token' => $solicitud->access_token,
            'api_secret' => $solicitud->api_secret,
            'api_key' => $solicitud->api_key,
            'telefono' => $solicitud->telefono,
        ]);
    }

    public function updateConfig(Request $request, Solicitud $solicitud)
    {
        // Verificar que la solicitud pertenece al usuario autenticado
        if ($solicitud->cliente_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $validated = $request->validate([
                'tienda_shopify' => 'required|string',
                'access_token' => 'required|string',
                'api_secret' => 'required|string',
                'api_key' => 'required|string',
                'telefono' => 'nullable|string',
            ]);

            $solicitud->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada correctamente',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración',
            ], 500);
        }
    }

    /**
     * Vista para que el cliente ingrese sus credenciales
     */
    public function credenciales()
    {
        // Obtener solicitudes del cliente que están pendientes de credenciales
        $solicitudes = Solicitud::where('cliente_id', auth()->id())
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->with('plan.empresa')
            ->latest()
            ->get();

        return view('cliente.solicitudes.credenciales', compact('solicitudes'));
    }

    /**
     * Guardar credenciales de integración
     */
    public function guardarCredenciales(Request $request, Solicitud $solicitud)
    {
        // Verificar que la solicitud pertenece al usuario autenticado
        if ($solicitud->cliente_id !== auth()->id()) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'tienda_shopify' => 'required|string|regex:/^[a-zA-Z0-9\-]+\.myshopify\.com$/',
            'access_token' => 'required|string|min:20',
            'api_secret' => 'required|string|min:20',
            'api_key' => 'required|string|min:10',
            'telefono' => 'nullable|string',
        ]);

        $solicitud->update($validated);

        return redirect()->route('cliente.solicitudes.credenciales')
            ->with('success', 'Credenciales guardadas exitosamente. El administrador procederá con la conexión.');
    }

    /**
     * Vista de admin para conectar integraciones
     */
    public function pendientesConexion()
    {
        $solicitudes = Solicitud::where('estado', 'en_proceso')
            ->where('integracion_conectada', false)
            ->whereNotNull('tienda_shopify')
            ->whereNotNull('access_token')
            ->whereNotNull('api_secret')
            ->whereNotNull('api_key')
            ->with(['cliente', 'plan.empresa', 'payment'])
            ->latest()
            ->paginate(10);

        return view('admin.solicitudes.pendientes-conexion', compact('solicitudes'));
    }

    /**
     * Conectar integración (Admin)
     */
    public function conectarIntegracion(Solicitud $solicitud)
    {
        try {
            // Verificar que tiene credenciales
            if (!$solicitud->tieneCredencialesCompletas()) {
                return back()->with('error', 'La solicitud no tiene credenciales completas');
            }

            // Verificar que está en estado correcto
            if (!$solicitud->puedeConectar()) {
                return back()->with('error', 'La solicitud no está en estado válido para conectar');
            }

            // Llamar al servicio de integración
            $integracionService = new \App\Services\IntegracionMulticlienteService();
            $result = $integracionService->conectarCliente($solicitud);

            if ($result['success']) {
                return redirect()->route('admin.solicitudes.pendientes-conexion')
                    ->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            \Log::error('Error conectando integración: ' . $e->getMessage());
            return back()->with('error', 'Error al conectar: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar solicitud (Admin)
     */
    public function rechazar(Request $request, Solicitud $solicitud)
    {
        $validated = $request->validate([
            'notas_admin' => 'required|string|min:10',
        ]);

        $solicitud->update([
            'estado' => 'rechazada',
            'notas_admin' => $validated['notas_admin'],
        ]);

        // TODO: Enviar notificación al cliente

        return redirect()->route('admin.solicitudes.pendientes-conexion')
            ->with('success', 'Solicitud rechazada');
    }
}
