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
                'tienda_shopify' => 'required|string',
                'descripcion' => 'nullable|string',
                'telefono' => 'nullable|string',
                'email' => 'required|email',
                'access_token' => 'required|string',
                'api_secret' => 'required|string',
                'api_key' => 'required|string',
            ]);

            $solicitud = Solicitud::create([
                'cliente_id' => auth()->id(),
                'plan_id' => $validated['plan_id'],
                'tienda_shopify' => $validated['tienda_shopify'],
                'descripcion' => $validated['descripcion'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'],
                'access_token' => $validated['access_token'],
                'api_secret' => $validated['api_secret'],
                'api_key' => $validated['api_key'],
                'estado' => 'pendiente',
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
            'estado' => 'required|in:pendiente,aprobada,rechazada,en_proceso',
            'notas_admin' => 'nullable|string',
        ]);

        $solicitud->update($validated);

        return redirect()->back()->with('success', 'Estado actualizado');
    }
}
