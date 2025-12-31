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
        // TODO: Implementar listado de estados de solicitudes del cliente
        return view('cliente.estados-solicitud');
    }

    /**
     * Planes activos
     */
    public function planesActivos()
    {
        // TODO: Implementar listado de planes activos del cliente
        return view('cliente.planes-activos');
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
