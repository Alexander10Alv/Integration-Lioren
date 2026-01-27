<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Dashboard del cliente
     */
    public function dashboard()
    {
        return view('cliente.dashboard');
    }

    /**
     * Planes disponibles
     */
    public function planes()
    {
        $planes = \App\Models\Plan::with('empresa')
            ->where('activo', true)
            ->get();
        
        return view('cliente.planes', compact('planes'));
    }

    /**
     * Estados de solicitud
     */
    public function estadosSolicitud()
    {
        $user = auth()->user();
        
        $solicitudes = \App\Models\Solicitud::where('cliente_id', $user->id)
            ->with(['plan.empresa'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('cliente.solicitudes.estados', compact('solicitudes'));
    }

    /**
     * Planes activos (ahora muestra suscripciones)
     */
    public function planesActivos()
    {
        $user = auth()->user();
        
        $suscripcionActiva = \App\Models\Suscripcion::where('user_id', $user->id)
            ->where('estado', 'activa')
            ->with('plan')
            ->first();
        
        $historialPagos = \App\Models\Payment::where('user_id', $user->id)
            ->whereNotNull('suscripcion_id')
            ->with('suscripcion.plan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('cliente.planes-activos', compact('suscripcionActiva', 'historialPagos'));
    }

    /**
     * Mis facturas/boletas
     */
    public function facturas()
    {
        // TODO: Implementar listado de facturas del cliente
        return view('cliente.facturas');
    }
}
