<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Planes Activos') }}
        </h2>
    </x-slot>

    <style>
        .plan-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            overflow: hidden;
            border-left: 6px solid #10b981;
        }
        .plan-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        .badge-activo {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 9999px;
            letter-spacing: 0.05em;
        }
        .badge-pendiente {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 9999px;
            letter-spacing: 0.05em;
        }
        .config-section {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-top: 1rem;
        }
        .btn-config {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            font-weight: 700;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
        }
        .btn-config:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($planesActivos->isEmpty())
                <div class="empty-state">
                    <div style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #374151; margin-bottom: 0.5rem;">
                        No tienes planes activos
                    </h3>
                    <p style="color: #6b7280; margin-bottom: 2rem;">
                        Explora nuestros planes disponibles y comienza a integrar tu tienda
                    </p>
                    <a href="{{ route('cliente.planes') }}" style="display: inline-block; padding: 1rem 2rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: #000; font-weight: 700; border-radius: 0.75rem; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em;">
                        <i class="fas fa-shopping-cart"></i> Ver Planes Disponibles
                    </a>
                </div>
            @else
                <div style="display: grid; gap: 1.5rem;">
                    @foreach($planesActivos as $solicitud)
                        <div class="plan-card">
                            <div style="padding: 1.5rem;">
                                <!-- Header -->
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                            <h3 style="font-size: 1.5rem; font-weight: 700; color: #111827;">
                                                {{ $solicitud->plan->nombre }}
                                            </h3>
                                            <span class="badge-activo">
                                                <i class="fas fa-check-circle"></i> Activo
                                            </span>
                                        </div>
                                        <p style="color: #6b7280; display: flex; align-items: center; gap: 0.5rem;">
                                            <i class="fas fa-building"></i>
                                            {{ $solicitud->plan->empresa->nombre }}
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 2rem; font-weight: 700; color: #10b981;">
                                            ${{ number_format($solicitud->plan->precio, 2) }}
                                        </div>
                                        <div style="font-size: 0.875rem; color: #6b7280;">USD / mes</div>
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <p style="color: #374151; margin-bottom: 1rem;">
                                    {{ $solicitud->plan->descripcion }}
                                </p>

                                <!-- Estado de Configuración -->
                                <div class="config-section">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <h4 style="font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 0.25rem;">
                                                <i class="fas fa-cog"></i> Configuración de Integración
                                            </h4>
                                            @if($solicitud->tienda_shopify && $solicitud->access_token && $solicitud->api_secret && $solicitud->api_key)
                                                <p style="color: #10b981; font-size: 0.875rem;">
                                                    <i class="fas fa-check-circle"></i> Configuración completa
                                                </p>
                                            @else
                                                <p style="color: #f59e0b; font-size: 0.875rem;">
                                                    <i class="fas fa-exclamation-triangle"></i> Configuración pendiente
                                                </p>
                                            @endif
                                        </div>
                                        <button onclick="abrirConfiguracion({{ $solicitud->id }}, '{{ $solicitud->plan->nombre }}')" class="btn-config">
                                            <i class="fas fa-wrench"></i> Configurar Integración
                                        </button>
                                    </div>
                                </div>

                                <!-- Info adicional -->
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                    <div>
                                        <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; margin-bottom: 0.25rem;">Fecha de activación</div>
                                        <div style="font-weight: 600; color: #111827;">
                                            {{ $solicitud->fecha_pago ? $solicitud->fecha_pago->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; margin-bottom: 0.25rem;">Próximo pago</div>
                                        <div style="font-weight: 600; color: #111827;">
                                            {{ $solicitud->fecha_pago ? $solicitud->fecha_pago->addMonth()->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de Configuración -->
    <div id="configModal" style="display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(4px);">
        <div style="background: white; margin: 2% auto; padding: 0; border-radius: 1rem; width: 90%; max-width: 700px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 2rem; border-radius: 1rem 1rem 0 0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: white; margin: 0;">
                        <i class="fas fa-cog"></i> Configurar Integración
                    </h3>
                    <p id="configPlanNombre" style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;"></p>
                </div>
                <button onclick="cerrarConfigModal()" style="color: white; background: rgba(255,255,255,0.2); border: none; width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="fas fa-times" style="font-size: 1.5rem;"></i>
                </button>
            </div>
            
            <!-- Contenido -->
            <form id="configForm" style="padding: 2rem; max-height: 60vh; overflow-y: auto;">
                <input type="hidden" id="solicitud_id">
                
                <div style="background: #dbeafe; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border-left: 4px solid #3b82f6;">
                    <p style="color: #1e40af; margin: 0; font-size: 0.875rem;">
                        <i class="fas fa-info-circle"></i> Completa estos datos para activar la sincronización entre tu tienda Shopify y el sistema.
                    </p>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                        Tienda Shopify *
                    </label>
                    <input type="text" id="tienda_shopify" required placeholder="tu-tienda.myshopify.com" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-family: monospace;">
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Formato: tu-tienda.myshopify.com</p>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                        Access Token *
                    </label>
                    <input type="text" id="access_token" required placeholder="shpat_xxxxxxxxxxxxx" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-family: monospace;">
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Token de API de tu app personalizada de Shopify</p>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                        API Secret *
                    </label>
                    <input type="text" id="api_secret" required placeholder="shpss_xxxxxxxxxxxxx" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-family: monospace;">
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Secret key para validar webhooks de Shopify</p>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                        API Key Lioren *
                    </label>
                    <input type="text" id="api_key" required placeholder="tu_api_key_de_lioren" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem; font-family: monospace;">
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Token de autenticación de la API de Lioren</p>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                        Teléfono de contacto
                    </label>
                    <input type="text" id="telefono" placeholder="+56 9 1234 5678" style="width: 100%; padding: 0.75rem; border: 2px solid #d1d5db; border-radius: 0.5rem;">
                </div>
            </form>

            <!-- Footer -->
            <div style="padding: 1.5rem 2rem; background: #f9fafb; border-radius: 0 0 1rem 1rem; display: flex; gap: 1rem;">
                <button onclick="cerrarConfigModal()" style="flex: 1; padding: 0.75rem; background: white; color: #374151; font-weight: 600; border: 2px solid #d1d5db; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#9ca3af'" onmouseout="this.style.borderColor='#d1d5db'">
                    Cancelar
                </button>
                <button onclick="guardarConfiguracion()" style="flex: 2; padding: 0.75rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; font-weight: 700; border: none; border-radius: 0.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 25px -5px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
            </div>
        </div>
    </div>

    <script>
        function abrirConfiguracion(solicitudId, planNombre) {
            document.getElementById('solicitud_id').value = solicitudId;
            document.getElementById('configPlanNombre').textContent = planNombre;
            
            // Cargar datos existentes si los hay
            fetch(`/cliente/solicitudes/${solicitudId}/config`)
                .then(response => response.json())
                .then(data => {
                    if (data.tienda_shopify) document.getElementById('tienda_shopify').value = data.tienda_shopify;
                    if (data.access_token) document.getElementById('access_token').value = data.access_token;
                    if (data.api_secret) document.getElementById('api_secret').value = data.api_secret;
                    if (data.api_key) document.getElementById('api_key').value = data.api_key;
                    if (data.telefono) document.getElementById('telefono').value = data.telefono;
                })
                .catch(error => console.error('Error:', error));
            
            document.getElementById('configModal').style.display = 'block';
        }

        function cerrarConfigModal() {
            document.getElementById('configModal').style.display = 'none';
            document.getElementById('configForm').reset();
        }

        let guardandoConfig = false;

        function guardarConfiguracion() {
            if (guardandoConfig) return;

            const tienda = document.getElementById('tienda_shopify').value.trim();
            const accessToken = document.getElementById('access_token').value.trim();
            const apiSecret = document.getElementById('api_secret').value.trim();
            const apiKey = document.getElementById('api_key').value.trim();

            if (!tienda || !accessToken || !apiSecret || !apiKey) {
                alert('Por favor completa todos los campos obligatorios (*)');
                return;
            }

            guardandoConfig = true;
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            const solicitudId = document.getElementById('solicitud_id').value;
            const formData = {
                tienda_shopify: tienda,
                access_token: accessToken,
                api_secret: apiSecret,
                api_key: apiKey,
                telefono: document.getElementById('telefono').value.trim(),
            };

            fetch(`/cliente/solicitudes/${solicitudId}/config`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('✓ Configuración guardada exitosamente');
                    cerrarConfigModal();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo guardar la configuración'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                let mensaje = 'Error al guardar configuración';
                if (error.errors) {
                    mensaje = Object.values(error.errors).flat().join('\n');
                } else if (error.message) {
                    mensaje = error.message;
                }
                alert(mensaje);
            })
            .finally(() => {
                guardandoConfig = false;
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar Configuración';
            });
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('configModal');
            if (event.target == modal) {
                cerrarConfigModal();
            }
        }
    </script>
</x-app-layout>
