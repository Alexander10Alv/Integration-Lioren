<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Planes Disponibles') }}
        </h2>
    </x-slot>

    <style>
        .plan-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.2);
        }

        .plan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%);
        }

        .plan-header {
            padding: 2rem;
            text-align: center;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        }

        .plan-empresa {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%);
            color: #1a1a1a;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 9999px;
            margin-bottom: 1rem;
        }

        .plan-nombre {
            font-size: 1.75rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .plan-precio {
            font-size: 3rem;
            font-weight: 900;
            color: #FFC107;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .plan-precio-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
        }

        .plan-body {
            padding: 2rem;
        }

        .plan-descripcion {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .caracteristica-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .caracteristica-item:hover {
            background: #f3f4f6;
            transform: translateX(5px);
        }

        .caracteristica-icon {
            color: #10b981;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .btn-solicitar {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%);
            color: #000;
            font-weight: 800;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(255, 193, 7, 0.4);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-solicitar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-solicitar:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(255, 193, 7, 0.6);
        }

        .btn-solicitar:hover::before {
            left: 100%;
        }

        .btn-solicitar:active {
            transform: translateY(0);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 1rem;">
                    Elige el Plan Perfecto para tu Negocio
                </h1>
                <p style="font-size: 1.125rem; color: #6b7280; max-width: 600px; margin: 0 auto;">
                    Conecta tu tienda con las mejores plataformas de gestión empresarial
                </p>
            </div>

            @if($planes->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #374151; margin-bottom: 0.5rem;">
                    No hay planes disponibles
                </h3>
                <p style="color: #6b7280;">
                    Pronto tendremos nuevos planes para ti
                </p>
            </div>
            @else
            <!-- Grid de Planes -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
                @foreach($planes as $plan)
                <div class="plan-card">
                    <!-- Header del Plan -->
                    <div class="plan-header">
                        <div class="plan-empresa">
                            <i class="fas fa-building"></i> {{ $plan->empresa->nombre }}
                        </div>
                        <h3 class="plan-nombre">{{ $plan->nombre }}</h3>
                        <div class="plan-precio">
                            ${{ number_format($plan->precio, 2) }}
                        </div>
                        <div class="plan-precio-label">USD / mes</div>
                    </div>

                    <!-- Body del Plan -->
                    <div class="plan-body">
                        <p class="plan-descripcion">{{ $plan->descripcion }}</p>

                        <!-- Características -->
                        <div style="margin-bottom: 2rem;">
                            <h4 style="font-size: 0.875rem; font-weight: 700; color: #111827; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                                <i class="fas fa-check-circle" style="color: #10b981;"></i> Incluye:
                            </h4>
                            @foreach($plan->caracteristicas as $caracteristica)
                            <div class="caracteristica-item">
                                <i class="fas fa-check-circle caracteristica-icon"></i>
                                <span style="color: #374151; font-weight: 500;">{{ $caracteristica }}</span>
                            </div>
                            @endforeach
                        </div>

                        <!-- Botones de Acción -->
                        <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <button onclick="verInformacion({{ $plan->id }}, '{{ $plan->nombre }}', '{{ addslashes($plan->descripcion) }}', '{{ $plan->empresa->nombre }}', {{ $plan->precio }}, {{ json_encode($plan->caracteristicas) }})" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #374151; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; border: 2px solid #d1d5db; border-radius: 0.75rem; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#e5e7eb'; this.style.borderColor='#9ca3af'" onmouseout="this.style.background='#f3f4f6'; this.style.borderColor='#d1d5db'">
                                <i class="fas fa-info-circle"></i> Más Info
                            </button>
                        </div>
                        <button onclick="solicitarPlan({{ $plan->id }}, '{{ addslashes($plan->nombre) }}', '{{ addslashes($plan->empresa->nombre) }}')" class="btn-solicitar">
                            <i class="fas fa-paper-plane"></i> Solicitar Plan
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Información Adicional -->
            <div style="margin-top: 4rem; padding: 2rem; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-radius: 1rem; text-align: center;">
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1e40af; margin-bottom: 1rem;">
                    <i class="fas fa-info-circle"></i> ¿Necesitas ayuda?
                </h3>
                <p style="color: #1e40af; font-size: 1.125rem;">
                    Nuestro equipo está listo para ayudarte a elegir el mejor plan para tu negocio
                </p>
            </div>
        </div>
    </div>

    <!-- Modal de Solicitud -->
    <div id="solicitudModal" style="display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
        <div style="background: white; margin: 5% auto; padding: 0; border-radius: 1rem; width: 90%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); animation: slideDown 0.3s ease;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #FFD54F 0%, #FFCA28 100%); padding: 2rem; border-radius: 1rem 1rem 0 0; text-align: center;">
                <i class="fas fa-paper-plane" style="font-size: 3rem; color: #1a1a1a; margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 0;">Solicitar Plan</h3>
            </div>

            <!-- Contenido -->
            <div style="padding: 2rem;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <p style="font-size: 1.125rem; color: #374151; margin-bottom: 0.5rem;">
                        Estás solicitando el plan:
                    </p>
                    <h4 id="solicitudPlanNombre" style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;"></h4>
                    <p id="solicitudEmpresa" style="color: #6b7280;"></p>
                </div>

                <div style="background: #f9fafb; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem; border-left: 4px solid #FFC107;">
                    <p style="color: #374151; line-height: 1.6; margin: 0;">
                        <i class="fas fa-info-circle" style="color: #FFC107;"></i>
                        Tu solicitud será revisada por nuestro equipo y nos pondremos en contacto contigo pronto.
                    </p>
                </div>

                <!-- Botones -->
                <div style="display: flex; gap: 1rem;">
                    <button onclick="closeSolicitudModal()" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #374151; font-weight: 600; border: none; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        Cancelar
                    </button>
                    <button onclick="confirmarSolicitud()" style="flex: 1; padding: 0.75rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: #000; font-weight: 700; border: none; border-radius: 0.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px -4px rgba(248, 184, 0, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                        <i class="fas fa-check"></i> Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Información -->
    <div id="infoModal" style="display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
        <div style="background: white; margin: 2% auto; padding: 0; border-radius: 1rem; width: 90%; max-width: 700px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); animation: slideDown 0.3s ease;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #FFD54F 0%, #FFCA28 100%); padding: 2rem; border-radius: 1rem 1rem 0 0; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="background: rgba(0,0,0,0.1); padding: 0.75rem; border-radius: 0.75rem;">
                        <i class="fas fa-info-circle" style="font-size: 2rem; color: #1a1a1a;"></i>
                    </div>
                    <h3 id="infoModalTitle" style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 0;"></h3>
                </div>
                <button onclick="closeInfoModal()" style="color: #1a1a1a; background: rgba(0,0,0,0.1); border: none; width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='rgba(0,0,0,0.2)'" onmouseout="this.style.background='rgba(0,0,0,0.1)'">
                    <i class="fas fa-times" style="font-size: 1.5rem;"></i>
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
                        <div id="infoEmpresa" style="font-size: 1rem; font-weight: 600; color: #111827;"></div>
                    </div>
                    <div style="padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border-left: 4px solid #10b981;">
                        <div style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 0.5rem;">
                            <i class="fas fa-dollar-sign"></i> Precio
                        </div>
                        <div id="infoPrecio" style="font-size: 1.5rem; font-weight: 700; color: #10b981;"></div>
                    </div>
                </div>

                <!-- Descripción -->
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid #FFC107;">
                        <i class="fas fa-align-left"></i> Descripción
                    </h4>
                    <p id="infoDescripcion" style="color: #374151; line-height: 1.6;"></p>
                </div>

                <!-- Características -->
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid #FFC107;">
                        <i class="fas fa-list-check"></i> Características Incluidas
                    </h4>
                    <ul id="infoCaracteristicas" style="list-style: none; padding: 0; margin: 0;"></ul>
                </div>

                <!-- Opciones de Contacto -->
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 1rem; text-align: center;">
                        <i class="fas fa-comments"></i> ¿Necesitas más información?
                    </h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <!-- Chat Interno -->
                        <button onclick="abrirChatInterno()" style="padding: 1.5rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; font-weight: 700; border: none; border-radius: 1rem; cursor: pointer; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4); transition: all 0.3s; display: flex; flex-direction: column; align-items: center; gap: 0.75rem;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px -5px rgba(59, 130, 246, 0.6)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px -5px rgba(59, 130, 246, 0.4)'">
                            <i class="fas fa-comments" style="font-size: 2.5rem;"></i>
                            <span style="font-size: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Chat Interno</span>
                            <span style="font-size: 0.75rem; opacity: 0.9;">Contacta con un asesor en línea</span>
                        </button>

                        <!-- WhatsApp -->
                        <button onclick="abrirWhatsApp()" style="padding: 1.5rem; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); color: white; font-weight: 700; border: none; border-radius: 1rem; cursor: pointer; box-shadow: 0 10px 25px -5px rgba(37, 211, 102, 0.4); transition: all 0.3s; display: flex; flex-direction: column; align-items: center; gap: 0.75rem;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px -5px rgba(37, 211, 102, 0.6)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px -5px rgba(37, 211, 102, 0.4)'">
                            <i class="fab fa-whatsapp" style="font-size: 2.5rem;"></i>
                            <span style="font-size: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">WhatsApp</span>
                            <span style="font-size: 0.75rem; opacity: 0.9;">Contacto directo</span>
                        </button>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div style="display: flex; gap: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <button onclick="closeInfoModal()" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #374151; font-weight: 600; border: none; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        Cerrar
                    </button>
                    <button onclick="solicitarDesdeInfo()" style="flex: 1; padding: 0.75rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: #000; font-weight: 700; border: none; border-radius: 0.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px -4px rgba(248, 184, 0, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                        <i class="fas fa-paper-plane"></i> Solicitar Plan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Wizard de Solicitud -->
    <div id="wizardModal" style="display: none; position: fixed; z-index: 60; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(4px);">
        <div style="background: white; margin: 2% auto; padding: 0; border-radius: 1rem; width: 90%; max-width: 700px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">

            <!-- Paso 1: Confirmación -->
            <div id="wizardStep1" class="wizard-step">
                <div style="background: linear-gradient(135deg, #FFD54F 0%, #FFCA28 100%); padding: 2rem; border-radius: 1rem 1rem 0 0; text-align: center;">
                    <i class="fas fa-clipboard-check" style="font-size: 3rem; color: #1a1a1a; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a;">Confirmar Selección de Plan</h3>
                </div>
                <div style="padding: 2rem;">
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <p style="font-size: 1.125rem; color: #374151; margin-bottom: 0.5rem;">Has seleccionado:</p>
                        <h4 id="wizardPlanNombre" style="font-size: 1.75rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;"></h4>
                        <p id="wizardEmpresa" style="color: #6b7280; font-size: 1rem;"></p>
                    </div>
                    <div style="background: #dbeafe; padding: 1.5rem; border-radius: 0.75rem; border-left: 4px solid #3b82f6; margin-bottom: 2rem;">
                        <p style="color: #1e40af; line-height: 1.6; margin: 0;">
                            <i class="fas fa-info-circle"></i> Tu solicitud será revisada por nuestro equipo y nos pondremos en contacto contigo pronto.
                        </p>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button onclick="document.getElementById('wizardModal').style.display='none'" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #374151; font-weight: 600; border: none; border-radius: 0.5rem; cursor: pointer;">Cancelar</button>
                        <button onclick="siguienteWizard()" style="flex: 1; padding: 0.75rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: #000; font-weight: 700; border: none; border-radius: 0.5rem; cursor: pointer;">Siguiente <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
            </div>

            <!-- Paso 2: Pago (Flow) -->
            <div id="wizardStep2" class="wizard-step" style="display: none;">
                <div style="background: linear-gradient(135deg, #FFD54F 0%, #FFCA28 100%); padding: 2rem; border-radius: 1rem 1rem 0 0; text-align: center;">
                    <i class="fas fa-credit-card" style="font-size: 3rem; color: #1a1a1a; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a;">Proceder al Pago</h3>
                </div>
                <div style="padding: 2rem;">
                    <!-- Resumen del Plan -->
                    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem; border-left: 4px solid #FFC107;">
                        <h4 style="font-size: 1rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">Resumen de tu compra</h4>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: #6b7280;">Plan:</span>
                            <span id="wizardResumenPlan" style="font-weight: 600; color: #111827;"></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: #6b7280;">Empresa:</span>
                            <span id="wizardResumenEmpresa" style="font-weight: 600; color: #111827;"></span>
                        </div>
                        <div style="border-top: 2px solid #e5e7eb; margin: 1rem 0; padding-top: 1rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 1.125rem; font-weight: 700; color: #111827;">Total:</span>
                                <span id="wizardResumenPrecio" style="font-size: 1.5rem; font-weight: 700; color: #FFC107;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Pago -->
                    <div style="background: #dbeafe; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem; border-left: 4px solid #3b82f6;">
                        <p style="color: #1e40af; line-height: 1.6; margin: 0;">
                            <i class="fas fa-shield-alt"></i> Serás redirigido a Flow para completar tu pago de forma segura. Una vez confirmado el pago, podrás configurar tu integración.
                        </p>
                    </div>

                    <!-- Botones -->
                    <div style="display: flex; gap: 1rem;">
                        <button onclick="atrasWizard()" style="flex: 1; padding: 0.75rem; background: #f3f4f6; color: #374151; font-weight: 600; border: none; border-radius: 0.5rem; cursor: pointer;"><i class="fas fa-arrow-left"></i> Atrás</button>
                        <button onclick="procesarPago()" style="flex: 2; padding: 0.75rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; font-weight: 700; border: none; border-radius: 0.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"><i class="fas fa-lock"></i> Pagar con Flow</button>
                    </div>
                </div>
            </div>

            <!-- Paso 3: Éxito -->
            <div id="wizardStep3" class="wizard-step" style="display: none;">
                <div style="padding: 3rem; text-align: center;">
                    <div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                        <i class="fas fa-check" style="font-size: 3rem; color: #065f46;"></i>
                    </div>
                    <h3 style="font-size: 2rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">¡Pago Confirmado!</h3>
                    <p style="font-size: 1.125rem; color: #6b7280; line-height: 1.6; margin-bottom: 2rem;">
                        Tu pago ha sido procesado exitosamente. Ahora puedes configurar tu integración en la sección <strong>"Planes Activos"</strong>.
                    </p>
                    <div style="background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; border-left: 4px solid #f59e0b;">
                        <p style="color: #92400e; margin: 0;">
                            <i class="fas fa-info-circle"></i> Te enviaremos un correo con los próximos pasos para completar la configuración de tu tienda.
                        </p>
                    </div>
                    <button onclick="cerrarWizard()" style="padding: 1rem 2rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: #000; font-weight: 700; border: none; border-radius: 0.75rem; cursor: pointer; font-size: 1rem; text-transform: uppercase;">
                        <i class="fas fa-check"></i> Ir a Planes Activos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let planIdSeleccionado = null;
        let planNombreTemp = '';
        let empresaTemp = '';

        function verInformacion(planId, nombre, descripcion, empresa, precio, caracteristicas) {
            planIdSeleccionado = planId;
            planNombreTemp = nombre;
            empresaTemp = empresa;

            document.getElementById('infoModalTitle').textContent = nombre;
            document.getElementById('infoEmpresa').textContent = empresa;
            document.getElementById('infoPrecio').textContent = '$' + parseFloat(precio).toFixed(2) + ' USD/mes';
            document.getElementById('infoDescripcion').textContent = descripcion;

            // Características
            const caracteristicasList = document.getElementById('infoCaracteristicas');
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

            document.getElementById('infoModal').style.display = 'block';
        }

        function closeInfoModal() {
            document.getElementById('infoModal').style.display = 'none';
        }

        function solicitarDesdeInfo() {
            closeInfoModal();
            solicitarPlan(planIdSeleccionado, planNombreTemp, empresaTemp);
        }

        function abrirChatInterno() {
            closeInfoModal();
            // Crear nuevo chat
            fetch('{{ route("cliente.chats.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        plan_id: planIdSeleccionado
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/chats/' + data.chat_id;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function abrirWhatsApp() {
            const mensaje = `Hola! Me interesa obtener más información sobre el plan "${planNombreTemp}" de ${empresaTemp}.`;
            const numeroWhatsApp = '56912345678'; // TODO: Cambiar por el número real
            const url = `https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensaje)}`;
            window.open(url, '_blank');
        }

        function solicitarPlan(planId, planNombre, empresa) {
            console.log('solicitarPlan llamado con:', planId, planNombre, empresa);
            planIdSeleccionado = planId;
            planNombreTemp = planNombre;
            empresaTemp = empresa;
            document.getElementById('solicitudPlanNombre').textContent = planNombre;
            document.getElementById('solicitudEmpresa').textContent = 'Empresa: ' + empresa;
            document.getElementById('solicitudModal').style.display = 'block';
        }

        function closeSolicitudModal() {
            document.getElementById('solicitudModal').style.display = 'none';
            // NO ponemos planIdSeleccionado = null aquí porque lo necesitamos para el wizard
        }

        function confirmarSolicitud() {
            closeSolicitudModal();
            abrirWizard();
        }

        // Wizard de 3 pasos
        let wizardStep = 1;
        let wizardData = {};

        function abrirWizard() {
            wizardStep = 1;

            // Debug: verificar variables
            console.log('planIdSeleccionado:', planIdSeleccionado);
            console.log('planNombreTemp:', planNombreTemp);
            console.log('empresaTemp:', empresaTemp);

            // Buscar el precio del plan
            const planes = @json($planes);
            console.log('Planes disponibles:', planes);
            const planSeleccionado = planes.find(p => p.id === planIdSeleccionado);
            console.log('Plan encontrado:', planSeleccionado);

            wizardData = {
                plan_id: planIdSeleccionado,
                plan_nombre: planNombreTemp,
                empresa: empresaTemp,
                precio: planSeleccionado ? planSeleccionado.precio : 0
            };

            console.log('wizardData creado:', wizardData);

            document.getElementById('wizardModal').style.display = 'block';
            mostrarPasoWizard(1);
        }

        function mostrarPasoWizard(paso) {
            document.querySelectorAll('.wizard-step').forEach(el => el.style.display = 'none');
            document.getElementById('wizardStep' + paso).style.display = 'block';

            if (paso === 1) {
                document.getElementById('wizardPlanNombre').textContent = wizardData.plan_nombre;
                document.getElementById('wizardEmpresa').textContent = wizardData.empresa;
            } else if (paso === 2) {
                document.getElementById('wizardResumenPlan').textContent = wizardData.plan_nombre;
                document.getElementById('wizardResumenEmpresa').textContent = wizardData.empresa;
                document.getElementById('wizardResumenPrecio').textContent = '$' + wizardData.precio + ' USD/mes';
            }
        }

        function siguienteWizard() {
            if (wizardStep < 3) {
                wizardStep++;
                mostrarPasoWizard(wizardStep);
            }
        }

        function atrasWizard() {
            if (wizardStep > 1) {
                wizardStep--;
                mostrarPasoWizard(wizardStep);
            }
        }

        let procesandoPago = false;

        function procesarPago() {
            if (procesandoPago) return;

            procesandoPago = true;
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

            // Verificar que tenemos el plan_id
            console.log('Plan ID seleccionado:', wizardData.plan_id);

            if (!wizardData.plan_id) {
                alert('Error: No se ha seleccionado un plan válido');
                procesandoPago = false;
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock"></i> Pagar con Flow';
                return;
            }

            // Integración con Flow
            const formData = {
                plan_id: parseInt(wizardData.plan_id), // Asegurar que sea número
            };

            console.log('Enviando datos:', formData);

            fetch('{{ route("flow.create-plan-payment") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    console.log('Respuesta recibida:', response);
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Datos de respuesta:', data);
                    if (data.success && data.redirect_url) {
                        // Redirigir a Flow para procesar el pago
                        window.location.href = data.redirect_url;
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo procesar el pago'));
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    let mensaje = 'Error al procesar el pago';
                    if (error.errors) {
                        mensaje = Object.values(error.errors).flat().join('\n');
                    } else if (error.message) {
                        mensaje = error.message;
                    }
                    alert(mensaje);
                })
                .finally(() => {
                    procesandoPago = false;
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock"></i> Pagar con Flow';
                });
        }

        function cerrarWizard() {
            document.getElementById('wizardModal').style.display = 'none';
            wizardData = {};
            // Redirigir a planes activos
            window.location.href = '{{ route("cliente.planes-activos") }}';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const solicitudModal = document.getElementById('solicitudModal');
            const infoModal = document.getElementById('infoModal');

            if (event.target == solicitudModal) {
                closeSolicitudModal();
            }
            if (event.target == infoModal) {
                closeInfoModal();
            }
        }
    </script>
</x-app-layout>
