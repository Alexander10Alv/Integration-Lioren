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

            return view('integracion.bodegas', compact('config', 'shopifyLocations', 'liorenBodegas'));

        } catch (\Exception $e) {
            Log::error("Error cargando configuración de bodegas: " . $e->getMessage());
            
            return back()->with('error', 'Error cargando configuración: ' . $e->getMessage());
        }
    }

    /**
     * Configurar modo (simple o avanzado)
     */
    public function setMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:simple,advanced',
            'bodega_id' => 'required|integer',
            'bodega_name' => 'required|string',
        ]);

        try {
            $inventoryService = new InventorySyncService(auth()->id());

            if ($request->mode === 'simple') {
                $inventoryService->configureSimpleMode(
                    $request->bodega_id,
                    $request->bodega_name
                );
            } else {
                $inventoryService->configureAdvancedMode(
                    $request->bodega_id,
                    $request->bodega_name
                );
            }

            return redirect()->route('integracion.bodegas')
                ->with('success', '✅ Configuración guardada correctamente');

        } catch (\Exception $e) {
            Log::error("Error configurando modo: " . $e->getMessage());
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Guardar mapeo de location a bodega
     */
    public function saveMapping(Request $request)
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

            return redirect()->route('integracion.bodegas')
                ->with('success', '✅ Mapeo guardado correctamente');

        } catch (\Exception $e) {
            Log::error("Error guardando mapeo: " . $e->getMessage());
            
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

            return redirect()->route('integracion.bodegas')
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

    /**
     * Obtener configuración actual (AJAX)
     */
    public function getConfig()
    {
        try {
            $inventoryService = new InventorySyncService(auth()->id());
            $config = $inventoryService->getCurrentConfig();

            return response()->json($config);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener bodegas de Lioren (AJAX)
     */
    public function getLiorenBodegas()
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

    /**
     * Obtener locations de Shopify (AJAX)
     */
    public function getShopifyLocations()
    {
        try {
            $inventoryService = new InventorySyncService(auth()->id());
            $locations = $inventoryService->getShopifyLocations();

            return response()->json([
                'success' => true,
                'locations' => $locations,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Configurar modo simple (AJAX)
     */
    public function configureSimple(Request $request)
    {
        $request->validate([
            'bodega_id' => 'required',
            'bodega_name' => 'required|string',
        ]);

        try {
            $inventoryService = new InventorySyncService(auth()->id());

            $inventoryService->configureSimpleMode(
                $request->bodega_id,
                $request->bodega_name
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada correctamente',
            ]);

        } catch (\Exception $e) {
            Log::error("Error configurando modo simple: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Configurar modo avanzado (AJAX)
     */
    public function configureAdvanced(Request $request)
    {
        $request->validate([
            'default_bodega_id' => 'required',
            'default_bodega_name' => 'required|string',
            'mappings' => 'nullable|array',
        ]);

        try {
            $inventoryService = new InventorySyncService(auth()->id());

            // Configurar modo avanzado
            $inventoryService->configureAdvancedMode(
                $request->default_bodega_id,
                $request->default_bodega_name
            );

            // Guardar mapeos si existen
            if ($request->mappings) {
                foreach ($request->mappings as $locationId => $mapping) {
                    $inventoryService->createLocationMapping(
                        $locationId,
                        $mapping['location_name'],
                        $mapping['bodega_id'],
                        $mapping['bodega_name']
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada correctamente',
            ]);

        } catch (\Exception $e) {
            Log::error("Error configurando modo avanzado: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
