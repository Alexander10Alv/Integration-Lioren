<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Empresa;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $planes = Plan::with('empresa')
            ->when($search, function($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%")
                      ->orWhereHas('empresa', function($q) use ($search) {
                          $q->where('nombre', 'like', "%{$search}%");
                      });
            })
            ->latest()
            ->paginate(10);
        
        return view('planes.index', compact('planes'));
    }

    public function create()
    {
        $empresas = Empresa::all();
        return view('planes.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        $empresa = Empresa::find($request->empresa_id);
        $isLioren = $empresa && $empresa->slug === 'lioren';

        $rules = [
            'empresa_id' => ['required', 'exists:empresas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'in:CLP,UF'],
            'activo' => ['boolean'],
        ];

        if ($isLioren) {
            // Para Lioren: características predefinidas
            $rules['facturacion_enabled'] = ['boolean'];
            $rules['shopify_visibility_enabled'] = ['boolean'];
            $rules['notas_credito_enabled'] = ['boolean'];
            $rules['order_limit_enabled'] = ['boolean'];
            $rules['monthly_order_limit'] = ['nullable', 'integer', 'min:1'];
        } else {
            // Para otras empresas: características libres
            $rules['caracteristicas'] = ['required', 'array'];
            $rules['caracteristicas.*'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        // Convertir explícitamente los campos booleanos de Lioren
        if ($isLioren) {
            $validated['facturacion_enabled'] = (bool) ($request->input('facturacion_enabled', 0));
            $validated['shopify_visibility_enabled'] = (bool) ($request->input('shopify_visibility_enabled', 0));
            $validated['notas_credito_enabled'] = (bool) ($request->input('notas_credito_enabled', 0));
            $validated['order_limit_enabled'] = (bool) ($request->input('order_limit_enabled', 0));
        }

        // Construir características para Lioren
        if ($isLioren) {
            $caracteristicas = [];
            if ($request->facturacion_enabled) $caracteristicas[] = '✅ Emisión de facturas electrónicas';
            if ($request->shopify_visibility_enabled) $caracteristicas[] = '👁️ Visibilidad desde Shopify';
            if ($request->notas_credito_enabled) $caracteristicas[] = '🔄 Notas de Crédito Automáticas';
            if ($request->order_limit_enabled && $request->monthly_order_limit) {
                $caracteristicas[] = "📊 Límite: {$request->monthly_order_limit} pedidos/mes";
            } elseif (!$request->order_limit_enabled) {
                $caracteristicas[] = '♾️ Sin límite de pedidos';
            }
            $validated['caracteristicas'] = $caracteristicas;
        }

        Plan::create($validated);

        return redirect()->route('planes.index')
            ->with('success', 'Plan creado exitosamente');
    }

    public function show(Plan $plan)
    {
        // Redirigir al index ya que usamos modal para ver detalles
        return redirect()->route('planes.index');
    }

    public function edit(Plan $plan)
    {
        $empresas = Empresa::all();
        return view('planes.edit', compact('plan', 'empresas'));
    }

    public function update(Request $request, $plane)
    {
        \Log::info('========== UPDATE PLAN START ==========');
        \Log::info('Plan ID from URL', ['id' => $plane, 'type' => gettype($plane)]);
        
        // Buscar plan manualmente porque el Route Model Binding no funciona con el modal
        $plan = Plan::find($plane);
        
        if (!$plan) {
            \Log::error('Plan not found', ['id' => $plane]);
            return redirect()->route('planes.index')
                ->withErrors(['error' => 'Plan no encontrado']);
        }
        
        \Log::info('Plan Found', [
            'plan_id' => $plan->id,
            'plan_nombre' => $plan->nombre,
            'plan_precio_actual' => $plan->precio,
            'plan_moneda_actual' => $plan->moneda ?? 'NULL',
        ]);
        
        \Log::info('Request Data', [
            'all_data' => $request->all(),
            'method' => $request->method(),
        ]);

        try {
            $empresa = Empresa::find($request->empresa_id);
            $isLioren = $empresa && $empresa->slug === 'lioren';

            $rules = [
                'empresa_id' => ['required', 'exists:empresas,id'],
                'nombre' => ['required', 'string', 'max:255'],
                'descripcion' => ['required', 'string'],
                'precio' => ['required', 'numeric', 'min:0'],
                'moneda' => ['required', 'in:CLP,UF'],
                'activo' => ['boolean'],
            ];

            if ($isLioren) {
                // Para Lioren: características predefinidas
                $rules['facturacion_enabled'] = ['boolean'];
                $rules['shopify_visibility_enabled'] = ['boolean'];
                $rules['notas_credito_enabled'] = ['boolean'];
                $rules['order_limit_enabled'] = ['boolean'];
                $rules['monthly_order_limit'] = ['nullable', 'integer', 'min:1'];
            } else {
                // Para otras empresas: características libres
                $rules['caracteristicas'] = ['required', 'array'];
                $rules['caracteristicas.*'] = ['required', 'string'];
            }

            $validated = $request->validate($rules);

            \Log::info('Validation Passed', ['validated_data' => $validated]);

            // Convertir explícitamente los campos booleanos de Lioren
            if ($isLioren) {
                $validated['facturacion_enabled'] = (bool) ($request->input('facturacion_enabled', 0));
                $validated['shopify_visibility_enabled'] = (bool) ($request->input('shopify_visibility_enabled', 0));
                $validated['notas_credito_enabled'] = (bool) ($request->input('notas_credito_enabled', 0));
                $validated['order_limit_enabled'] = (bool) ($request->input('order_limit_enabled', 0));
            }

            // Construir características para Lioren
            if ($isLioren) {
                $caracteristicas = [];
                if ($request->facturacion_enabled) $caracteristicas[] = '✅ Emisión de facturas electrónicas';
                if ($request->shopify_visibility_enabled) $caracteristicas[] = '👁️ Visibilidad desde Shopify';
                if ($request->notas_credito_enabled) $caracteristicas[] = '🔄 Notas de Crédito Automáticas';
                if ($request->order_limit_enabled && $request->monthly_order_limit) {
                    $caracteristicas[] = "📊 Límite: {$request->monthly_order_limit} pedidos/mes";
                } elseif (!$request->order_limit_enabled) {
                    $caracteristicas[] = '♾️ Sin límite de pedidos';
                }
                $validated['caracteristicas'] = $caracteristicas;
            }

            $plan->update($validated);

            \Log::info('Plan Updated in DB', [
                'plan_id' => $plan->id,
                'new_precio' => $plan->precio,
                'new_moneda' => $plan->moneda,
            ]);

            \Log::info('========== UPDATE PLAN SUCCESS ==========');

            // Forzar refresh completo agregando timestamp
            return redirect()->route('planes.index', ['refresh' => time()])
                ->with('success', 'Plan actualizado exitosamente');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Failed', [
                'errors' => $e->errors(),
            ]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Update Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Error al actualizar el plan'])
                ->withInput();
        }
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('planes.index')
            ->with('success', 'Plan eliminado exitosamente');
    }
}
