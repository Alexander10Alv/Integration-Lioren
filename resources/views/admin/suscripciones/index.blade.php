<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Suscripciones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Estadísticas -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-600">Activas</p>
                    <p class="text-3xl font-bold text-green-600">{{ $estadisticas['activas'] }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-600">Vencidas</p>
                    <p class="text-3xl font-bold text-red-600">{{ $estadisticas['vencidas'] }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-600">Canceladas</p>
                    <p class="text-3xl font-bold text-gray-600">{{ $estadisticas['canceladas'] }}</p>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-600">Próximas a Vencer (7 días)</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $estadisticas['proximas_vencer'] }}</p>
                </div>
            </div>

            <!-- Tabla de Suscripciones -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Todas las Suscripciones</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inicio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Próximo Pago</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Días Restantes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($suscripciones as $suscripcion)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            #{{ $suscripcion->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $suscripcion->user->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $suscripcion->plan->nombre }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($suscripcion->estado === 'activa')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Activa
                                                </span>
                                            @elseif($suscripcion->estado === 'vencida')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    Vencida
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Cancelada
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $suscripcion->fecha_inicio->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $suscripcion->proximo_pago->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $dias = $suscripcion->diasRestantes();
                                            @endphp
                                            @if($dias > 7)
                                                <span class="text-green-600">{{ $dias }} días</span>
                                            @elseif($dias >= 0)
                                                <span class="text-orange-600 font-semibold">{{ $dias }} días</span>
                                            @else
                                                <span class="text-red-600 font-semibold">Vencido ({{ abs($dias) }} días)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $suscripciones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
