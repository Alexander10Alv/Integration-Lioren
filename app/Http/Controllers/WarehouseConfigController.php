<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InventorySyncService;
use Illuminate\Support\Facades\Log;

class WarehouseConfigController extends Controller
{
    /**
     * Mostrar configuración de bodegas
     */
    public function index()
    {
        try {
            $inventoryService = new InventorySyncService(auth()->id());

            // Obtener configuración actual
            $config = $inventoryService->getCurrentConfig();

            // Obtener locations de Shopify
            $shopifyLocations = $inventoryService->getShopifyLocations();

            // Obtener bodegas de Lioren
            $liorenBodegas = $inventoryService->getLiorenBodegas();

            return view('integracion.warehouse-config', compact('config', 'shopifyLocations', 'liorenBodegas'));

        } catch (\Exception $e) {
            Log::error("Error cargando configuración de bodegas: " . $e->getMessage());
            
            return back()->with('error', 'Error cargando configuración: ' . $e->getMessage());
        }
    }

    /**
     * Configurar modo simple
     */
    public function configureSimple(Request $request)
    {
        $request->validate([
            'bodega_id' => 'required|integer',
            'bodega_name' => 'required|string',
        ]);

        try {
            $inventoryService = new InventorySyncService(auth()->id());

            $inventoryService->configureSimpleMode(
                $request->bodega_id,
                $request->bodega_name
            );

            return redirect()->route('warehouse.config')
                ->with('success', '✅ Modo simple configurado correctamente');

        } catch (\Exception $e) {
            Log::error("Error configurando modo simple: " . $e->getMessage());
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Configurar modo avanzado
     */
    public function configureAdvanced(Request $request)
    {
        $request->validate([
            'default_bodega_id' => 'required|integer',
            'default_bodega_name' => 'required|string',
        ]);

        try {
            $inventoryService = new InventorySyncService(auth()->id());

            $inventoryService->configureAdvancedMode(
                $request->default_bodega_id,
                $request->default_bodega_name
            );

            return redirect()->route('warehouse.config')
                ->with('success', '✅ Modo avanzado configurado correctamente');

        } catch (\Exception $e) {
            Log::error("Error configurando modo avanzado: " . $e->getMessage());
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Crear mapeo de location a bodega
     */
    public function createMapping(Request $request)
    {
        $request->validate([
            'location_id' => 'required|string',
            'location_name' => 'required|string',
            'bodega_id' => 'required|integer',
            'bodega_name' => 'required|string',
        ]);

        try {
            $inventoryService = new InventorySyncService(auth()->id());

            $inventoryService->createLocationMapping(
                $request->location_id,
                $request->location_name,
                $request->bodega_id,
                $request->bodega_name
            );

            return redirect()->route('warehouse.config')
                ->with('success', '✅ Mapeo creado correctamente');

        } catch (\Exception $e) {
            Log::error("Error creando mapeo: " . $e->getMessage());
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar mapeo
     */
    public function deleteMapping($locationId)
    {
        try {
            $inventoryService = new InventorySyncService(auth()->id());

            $inventoryService->deleteLocationMapping($locationId);

            return redirect()->route('warehouse.config')
                ->with('success', '✅ Mapeo eliminado correctamente');

        } catch (\Exception $e) {
            Log::error("Error eliminando mapeo: " . $e->getMessage());
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Refrescar lista de bodegas desde Lioren
     */
    public function refreshBodegas()
    {
        try {
            $inventoryService = new InventorySyncService(auth()->id());

            $bodegas = $inventoryService->getLiorenBodegas();

            return response()->json([
                'success' => true,
                'bodegas' => $bodegas,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
