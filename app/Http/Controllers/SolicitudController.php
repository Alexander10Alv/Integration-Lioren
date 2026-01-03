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
}
