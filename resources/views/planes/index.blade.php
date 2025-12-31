<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Planes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827;">Gestión de Planes</h2>
                        <button onclick="openModal()" style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); border-radius: 0.5rem; font-weight: 700; font-size: 0.875rem; color: #000; text-transform: uppercase; border: none; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                            <i class="fas fa-plus"></i> Nuevo Plan
                        </button>
                    </div>

                    @if(session('success'))
                        <div style="background: #10b981; color: white; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Buscador -->
                    <form method="GET" action="{{ route('planes.index') }}" style="margin-bottom: 1.5rem;">
                        <div style="display: flex; gap: 0.75rem;">
                            <div style="flex: 1; position: relative;">
                                <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #6b7280;"></i>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, descripción o empresa..." style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s;" onfocus="this.style.borderColor='#FFC107'; this.style.boxShadow='0 0 0 3px rgba(255, 193, 7, 0.1)'" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                            </div>
                            <button type="submit" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: #000; font-weight: 700; border: none; border-radius: 0.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            @if(request('search'))
                                <a href="{{ route('planes.index') }}" style="padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; font-weight: 600; border: none; border-radius: 0.5rem; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            @endif
                        </div>
                    </form>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f3f4f6;">
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #374151;">Nombre</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #374151;">Empresa</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #374151;">Precio (USD)</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #374151;">Estado</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #374151;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($planes as $plan)
                                    <tr style="border-bottom: 1px solid #e5e7eb;">
                                        <td style="padding: 0.75rem;">{{ $plan->nombre }}</td>
                                        <td style="padding: 0.75rem;">
                                            <span style="padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600; background: #dbeafe; color: #1e40af;">
                                                {{ $plan->empresa->nombre }}
                                            </span>
                                        </td>
                                        <td style="padding: 0.75rem; font-weight: 600;">${{ number_format($plan->precio, 2) }}</td>
                                        <td style="padding: 0.75rem;">
                                            <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; {{ $plan->activo ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;' }}">
                                                {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <button onclick="viewPlan('{{ $plan->nombre }}', '{{ addslashes($plan->descripcion) }}', '{{ $plan->empresa->nombre }}', {{ $plan->precio }}, {{ $plan->activo ? 'true' : 'false' }}, {{ json_encode($plan->caracteristicas) }})" style="color: #10b981; background: none; border: none; cursor: pointer; margin-right: 1rem; font-weight: 600;"><i class="fas fa-eye"></i> Ver</button>
                                            <button onclick="editPlan({{ $plan->id }}, '{{ $plan->nombre }}', '{{ addslashes($plan->descripcion) }}', {{ $plan->empresa_id }}, {{ $plan->precio }}, {{ $plan->activo ? 'true' : 'false' }}, {{ json_encode($plan->caracteristicas) }})" style="color: #3b82f6; background: none; border: none; cursor: pointer; margin-right: 1rem; font-weight: 600;"><i class="fas fa-edit"></i> Editar</button>
                                            <form action="{{ route('planes.destroy', $plan) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('¿Estás seguro de eliminar este plan?')" style="color: #ef4444; background: none; border: none; cursor: pointer; font-weight: 600;"><i class="fas fa-trash"></i> Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding: 2rem; text-align: center; color: #6b7280;">
                                            @if(request('search'))
                                                <i class="fas fa-search" style="font-size: 2rem; color: #d1d5db; margin-bottom: 0.5rem;"></i>
                                                <p>No se encontraron resultados para "{{ request('search') }}"</p>
                                            @else
                                                <i class="fas fa-clipboard-list" style="font-size: 2rem; color: #d1d5db; margin-bottom: 0.5rem;"></i>
                                                <p>No hay planes registrados</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div style="margin-top: 1.5rem;">
                        {{ $planes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar Plan -->
    <div id="planModal" style="display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); animation: fadeIn 0.3s ease;">
        <div style="background: white; margin: 2% auto; padding: 0; border-radius: 1rem; width: 90%; max-width: 800px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); animation: slideDown 0.3s ease;">
            <!-- Header del Modal -->
            <div style="background: linear-gradient(135deg, #FFD54F 0%, #FFCA28 100%); padding: 2rem; border-radius: 1rem 1rem 0 0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="background: rgba(0,0,0,0.1); padding: 0.75rem; border-radius: 0.75rem;">
                        <i class="fas fa-clipboard-list" style="font-size: 2rem; color: #1a1a1a;"></i>
                    </div>
                    <h3 id="modalTitle" style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 0;">Nuevo Plan</h3>
                </div>
                <button onclick="closeModal()" style="color: #1a1a1a; background: rgba(0,0,0,0.1); border: none; width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; font-size: 1.5rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='rgba(0,0,0,0.2)'" onmouseout="this.style.background='rgba(0,0,0,0.1)'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Formulario -->
            <form id="planForm" method="POST" action="{{ route('planes.store') }}" style="padding: 2rem; max-height: 70vh; overflow-y: auto;">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><i class="fas fa-building"></i> Empresa *</label>
                    <select name="empresa_id" id="empresa_id" required style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s;" onfocus="this.style.borderColor='#FFC107'; this.style.boxShadow='0 0 0 3px rgba(255, 193, 7, 0.1)'" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                        <option value="">Seleccione una empresa</option>
                        @foreach(\App\Models\Empresa::all() as $empresa)
                            <option value="{{ $empresa->id }}" {{ !$empresa->disponible ? 'disabled' : '' }}>
                                {{ $empresa->nombre }} {{ !$empresa->disponible ? '(Próximamente)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><i class="fas fa-tag"></i> Nombre del Plan *</label>
                    <input type="text" name="nombre" id="nombre" required style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s;" onfocus="this.style.borderColor='#FFC107'; this.style.boxShadow='0 0 0 3px rgba(255, 193, 7, 0.1)'" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><i class="fas fa-align-left"></i> Descripción *</label>
                    <textarea name="descripcion" id="descripcion" required rows="3" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s; resize: vertical;" onfocus="this.style.borderColor='#FFC107'; this.style.boxShadow='0 0 0 3px rgba(255, 193, 7, 0.1)'" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"></textarea>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><i class="fas fa-dollar-sign"></i> Precio (USD) *</label>
                    <input type="number" name="precio" id="precio" required step="0.01" min="0" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s;" onfocus="this.style.borderColor='#FFC107'; this.style.boxShadow='0 0 0 3px rgba(255, 193, 7, 0.1)'" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;"><i class="fas fa-list-check"></i> Características *</label>
                    <div id="caracteristicasContainer"></div>
                    <button type="button" onclick="addCaracteristica()" style="margin-top: 0.5rem; padding: 0.5rem 1rem; background: #f3f4f6; color: #374151; font-weight: 600; border: none; border-radius: 0.375rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        <i class="fas fa-plus"></i> Agregar Característica
                    </button>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="activo" id="activo" value="1" checked style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem; cursor: pointer;">
                        <span style="font-size: 0.875rem; font-weight: 600; color: #374151;">Plan Activo</span>
                    </label>
                </div>

                <!-- Botones de Acción -->
                <div style="display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <button type="button" onclick="closeModal()" style="padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; border: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        Cancelar
                    </button>
                    <button type="submit" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: #000; font-weight: 700; border: none; border-radius: 0.5rem; font-size: 0.875rem; text-transform: uppercase; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px -4px rgba(248, 184, 0, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                        <i class="fas fa-save"></i> Guardar Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ver Plan -->
    <div id="viewModal" style="display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); animation: fadeIn 0.3s ease;">
        <div style="background: white; margin: 2% auto; padding: 0; border-radius: 1rem; width: 90%; max-width: 700px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); animation: slideDown 0.3s ease;">
            <!-- Header del Modal -->
            <div style="background: linear-gradient(135deg, #FFD54F 0%, #FFCA28 100%); padding: 2rem; border-radius: 1rem 1rem 0 0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="background: rgba(0,0,0,0.1); padding: 0.75rem; border-radius: 0.75rem;">
                        <i class="fas fa-clipboard-list" style="font-size: 2rem; color: #1a1a1a;"></i>
                    </div>
                    <h3 id="viewModalTitle" style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 0;"></h3>
                </div>
                <button onclick="closeViewModal()" style="color: #1a1a1a; background: rgba(0,0,0,0.1); border: none; width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; font-size: 1.5rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='rgba(0,0,0,0.2)'" onmouseout="this.style.background='rgba(0,0,0,0.1)'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Contenido -->
            <div style="padding: 2rem;">
                <!-- Empresa y Precio -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border-left: 4px solid #FFC107;">
                        <div style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 0.5rem;">
                            <i class="fas fa-building"></i> Empresa
                        </div>
                        <div id="viewEmpresa" style="font-size: 1rem; font-weight: 600; color: #111827;"></div>
                    </div>
                    <div style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border-left: 4px solid #10b981;">
                        <div style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 0.5rem;">
                            <i class="fas fa-dollar-sign"></i> Precio
                        </div>
                        <div id="viewPrecio" style="font-size: 1.5rem; font-weight: 700; color: #10b981;"></div>
                    </div>
                </div>

                <!-- Descripción -->
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid #FFC107;">
                        <i class="fas fa-align-left"></i> Descripción
                    </h4>
                    <p id="viewDescripcion" style="color: #374151; line-height: 1.6;"></p>
                </div>

                <!-- Características -->
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid #FFC107;">
                        <i class="fas fa-list-check"></i> Características
                    </h4>
                    <ul id="viewCaracteristicas" style="list-style: none; padding: 0; margin: 0;"></ul>
                </div>

                <!-- Estado -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.875rem; font-weight: 600; color: #374151;">Estado:</span>
                    <span id="viewEstado"></span>
                </div>

                <!-- Botón Cerrar -->
                <div style="display: flex; justify-content: center; margin-top: 2rem;">
                    <button onclick="closeViewModal()" style="padding: 0.75rem 2rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: #000; font-weight: 700; border: none; border-radius: 0.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px -4px rgba(248, 184, 0, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                        <i class="fas fa-check"></i> Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideDown {
            from { 
                opacity: 0;
                transform: translateY(-50px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        let caracteristicaCount = 0;

        function openModal() {
            document.getElementById('planModal').style.display = 'block';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Nuevo Plan';
            document.getElementById('planForm').action = '{{ route("planes.store") }}';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('planForm').reset();
            document.getElementById('caracteristicasContainer').innerHTML = '';
            caracteristicaCount = 0;
            addCaracteristica();
        }

        function closeModal() {
            document.getElementById('planModal').style.display = 'none';
        }

        function addCaracteristica(value = '') {
            const container = document.getElementById('caracteristicasContainer');
            const div = document.createElement('div');
            div.style.cssText = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem;';
            div.innerHTML = `
                <input type="text" name="caracteristicas[]" value="${value}" required placeholder="Ej: Sincronización en tiempo real" style="flex: 1; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s;" onfocus="this.style.borderColor='#FFC107'; this.style.boxShadow='0 0 0 3px rgba(255, 193, 7, 0.1)'" onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                <button type="button" onclick="this.parentElement.remove()" style="padding: 0.75rem; background: #fee2e2; color: #991b1b; border: none; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(div);
            caracteristicaCount++;
        }

        function editPlan(id, nombre, descripcion, empresa_id, precio, activo, caracteristicas) {
            document.getElementById('planModal').style.display = 'block';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Plan';
            document.getElementById('planForm').action = '{{ route("planes.index") }}/' + id;
            document.getElementById('formMethod').value = 'PUT';
            
            document.getElementById('nombre').value = nombre;
            document.getElementById('descripcion').value = descripcion;
            document.getElementById('empresa_id').value = empresa_id;
            document.getElementById('precio').value = precio;
            document.getElementById('activo').checked = activo;
            
            document.getElementById('caracteristicasContainer').innerHTML = '';
            caracteristicaCount = 0;
            caracteristicas.forEach(car => addCaracteristica(car));
        }

        function viewPlan(nombre, descripcion, empresa, precio, activo, caracteristicas) {
            document.getElementById('viewModal').style.display = 'block';
            document.getElementById('viewModalTitle').textContent = nombre;
            document.getElementById('viewEmpresa').textContent = empresa;
            document.getElementById('viewPrecio').textContent = '$' + parseFloat(precio).toFixed(2) + ' USD';
            document.getElementById('viewDescripcion').textContent = descripcion;
            
            // Características
            const caracteristicasList = document.getElementById('viewCaracteristicas');
            caracteristicasList.innerHTML = '';
            caracteristicas.forEach(car => {
                const li = document.createElement('li');
                li.style.cssText = 'padding: 0.75rem; background: #f9fafb; border-radius: 0.5rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;';
                li.innerHTML = `
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.25rem;"></i>
                    <span style="color: #374151;">${car}</span>
                `;
                caracteristicasList.appendChild(li);
            });
            
            // Estado
            const estadoSpan = document.getElementById('viewEstado');
            if (activo) {
                estadoSpan.innerHTML = '<span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #d1fae5; color: #065f46;"><i class="fas fa-check-circle"></i> Activo</span>';
            } else {
                estadoSpan.innerHTML = '<span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #fee2e2; color: #991b1b;"><i class="fas fa-times-circle"></i> Inactivo</span>';
            }
        }

        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('planModal');
            const viewModal = document.getElementById('viewModal');
            if (event.target == modal) {
                closeModal();
            }
            if (event.target == viewModal) {
                closeViewModal();
            }
        }
    </script>
</x-app-layout>
