<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suscripcion;
use App\Models\Payment;
use App\Models\Plan;

class SuscripcionController extends Controller
{
    /**
     * Vista de suscripciones del cliente
     */
    public function index()
    {
        $user = auth()->user();
        
        $suscripcionActiva = Suscripcion::where('user_id', $user->id)
            ->where('estado', 'activa')
            ->with('plan')
            ->first();
        
        $historialPagos = Payment::where('user_id', $user->id)
            ->whereNotNull('suscripcion_id')
            ->with('suscripcion.plan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('cliente.suscripciones.index', compact('suscripcionActiva', 'historialPagos'));
    }

    /**
     * Vista admin: todas las suscripciones
     */
    public function admin()
    {
        $suscripciones = Suscripcion::with(['user', 'plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $estadisticas = [
            'activas' => Suscripcion::where('estado', 'activa')->count(),
            'vencidas' => Suscripcion::where('estado', 'vencida')->count(),
            'canceladas' => Suscripcion::where('estado', 'cancelada')->count(),
            'proximas_vencer' => Suscripcion::where('estado', 'activa')
                ->whereBetween('proximo_pago', [now(), now()->addDays(7)])
                ->count(),
        ];
        
        return view('admin.suscripciones.index', compact('suscripciones', 'estadisticas'));
    }

    /**
     * Cancelar suscripción
     */
    public function cancelar(Suscripcion $suscripcion)
    {
        if ($suscripcion->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'No autorizado');
        }

        try {
            \DB::beginTransaction();

            // Marcar suscripción como cancelada
            $suscripcion->update(['estado' => 'cancelada']);

            // Desactivar la integración del usuario
            $integracionConfig = \App\Models\IntegracionConfig::where('user_id', $suscripcion->user_id)
                ->where('activo', true)
                ->first();

            if ($integracionConfig) {
                $integracionConfig->update(['activo' => false]);
                
                \Log::info("Integración desactivada por cancelación de suscripción", [
                    'user_id' => $suscripcion->user_id,
                    'suscripcion_id' => $suscripcion->id,
                    'integracion_config_id' => $integracionConfig->id
                ]);
            }

            \DB::commit();

            return back()->with('success', 'Suscripción cancelada exitosamente. Tu integración ha sido desactivada.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Error al cancelar suscripción: " . $e->getMessage());
            return back()->with('error', 'Error al cancelar la suscripción.');
        }
    }

    /**
     * Renovar suscripción (redirige a pago)
     */
    public function renovar(Suscripcion $suscripcion)
    {
        if ($suscripcion->user_id !== auth()->id()) {
            abort(403, 'No autorizado');
        }

        // Redirigir al pago del plan
        return redirect()->route('flow.create-plan-payment', ['plan' => $suscripcion->plan_id]);
    }
}
