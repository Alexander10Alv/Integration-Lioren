<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mi Suscripción y Pagos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Suscripción Activa -->
            @if($suscripcionActiva)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Suscripción Activa</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Plan</p>
                                <p class="font-semibold">{{ $suscripcionActiva->plan->nombre }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">Estado</p>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ ucfirst($suscripcionActiva->estado) }}
                                </span>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">Fecha de Inicio</p>
                                <p class="font-semibold">{{ $suscripcionActiva->fecha_inicio->format('d/m/Y') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">Próximo Pago</p>
                                <p class="font-semibold">{{ $suscripcionActiva->proximo_pago->format('d/m/Y') }}</p>
                                @if($suscripcionActiva->diasRestantes() <= 7 && $suscripcionActiva->diasRestantes() >= 0)
                                    <p class="text-sm text-orange-600">Vence en {{ $suscripcionActiva->diasRestantes() }} días</p>
                                @elseif($suscripcionActiva->diasRestantes() < 0)
                                    <p class="text-sm text-red-600">Vencido hace {{ abs($suscripcionActiva->diasRestantes()) }} días</p>
                                @endif
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600">Precio</p>
                                <p class="font-semibold">{{ number_format($suscripcionActiva->plan->precio, 0, ',', '.') }} {{ $suscripcionActiva->plan->moneda }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <a href="{{ route('suscripciones.renovar', $suscripcionActiva) }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                Renovar Ahora
                            </a>
                            
                            <form action="{{ route('suscripciones.cancelar', $suscripcionActiva) }}" method="POST" 
                                  onsubmit="return confirm('¿Estás seguro de cancelar tu suscripción?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                                    Cancelar Suscripción
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <p class="text-yellow-800">No tienes una suscripción activa.</p>
                    <a href="{{ route('cliente.planes') }}" class="text-blue-600 hover:underline">Ver planes disponibles</a>
                </div>
            @endif

            <!-- Historial de Pagos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Historial de Pagos</h3>
                    
                    @if($historialPagos->isEmpty())
                        <p class="text-gray-500">No hay pagos registrados.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($historialPagos as $pago)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ $pago->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ $pago->suscripcion->plan->nombre ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($pago->periodo_inicio && $pago->periodo_fin)
                                                    {{ $pago->periodo_inicio->format('d/m/Y') }} - {{ $pago->periodo_fin->format('d/m/Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                                {{ number_format($pago->amount, 0, ',', '.') }} {{ $pago->currency }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($pago->isPaid())
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        Pagado
                                                    </span>
                                                @elseif($pago->isPending())
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pendiente
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        Fallido
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $historialPagos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
