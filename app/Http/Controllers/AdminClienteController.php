<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminClienteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $clientes = Cliente::with('user')
            ->when($search, function($query) use ($search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('empresa', 'like', "%{$search}%")
                ->orWhere('rut', 'like', "%{$search}%")
                ->orWhere('telefono', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
        
        return view('clientes.index', compact('clientes'));
    }

    public function show(Cliente $cliente)
    {
        $cliente->load('user');
        return view('clientes.show', compact('cliente'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'rut' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'telefono_secundario' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'giro' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            // Crear usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Asignar rol de cliente
            $user->assignRole('cliente');

            // Crear perfil de cliente
            $cliente = Cliente::create([
                'user_id' => $user->id,
                'empresa' => $validated['empresa'] ?? null,
                'rut' => $validated['rut'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'telefono_secundario' => $validated['telefono_secundario'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'ciudad' => $validated['ciudad'] ?? null,
                'region' => $validated['region'] ?? null,
                'codigo_postal' => $validated['codigo_postal'] ?? null,
                'giro' => $validated['giro'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'estado' => 'activo',
            ]);

            DB::commit();

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear cliente: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $cliente->user_id],
            'password' => ['nullable', 'string', 'min:8'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'rut' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'telefono_secundario' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'giro' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        DB::beginTransaction();
        try {
            // Actualizar usuario
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $cliente->user->update($userData);

            // Actualizar cliente
            $cliente->update([
                'empresa' => $validated['empresa'] ?? null,
                'rut' => $validated['rut'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'telefono_secundario' => $validated['telefono_secundario'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'ciudad' => $validated['ciudad'] ?? null,
                'region' => $validated['region'] ?? null,
                'codigo_postal' => $validated['codigo_postal'] ?? null,
                'giro' => $validated['giro'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'estado' => $validated['estado'],
            ]);

            DB::commit();

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar cliente: ' . $e->getMessage()]);
        }
    }

    public function destroy(Cliente $cliente)
    {
        DB::beginTransaction();
        try {
            $user = $cliente->user;
            $cliente->delete();
            $user->delete();

            DB::commit();

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente eliminado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar cliente: ' . $e->getMessage()]);
        }
    }
}
