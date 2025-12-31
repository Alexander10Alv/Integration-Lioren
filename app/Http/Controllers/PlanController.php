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
        $validated = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'caracteristicas' => ['required', 'array'],
            'caracteristicas.*' => ['required', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'activo' => ['boolean'],
        ]);

        Plan::create($validated);

        return redirect()->route('planes.index')
            ->with('success', 'Plan creado exitosamente');
    }

    public function show(Plan $plan)
    {
        $plan->load('empresa');
        return view('planes.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        $empresas = Empresa::all();
        return view('planes.edit', compact('plan', 'empresas'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'caracteristicas' => ['required', 'array'],
            'caracteristicas.*' => ['required', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'activo' => ['boolean'],
        ]);

        $plan->update($validated);

        return redirect()->route('planes.index')
            ->with('success', 'Plan actualizado exitosamente');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('planes.index')
            ->with('success', 'Plan eliminado exitosamente');
    }
}
