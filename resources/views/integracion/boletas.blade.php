<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📄 Boletas Electrónicas Emitidas
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('integracion.boletas-form') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    + Emitir Boleta
                </a>
                <a href="{{ route('integracion.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    ← Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if($boletas->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folio</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receptor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($boletas as $boleta)
                                        <tr class="{{ $boleta->status === 'error' ? 'bg-red-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($boleta->folio)
                                                    <span class="text-lg font-bold text-indigo-600">#{{ $boleta->folio }}</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $boleta->fecha->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                @if($boleta->receptor_nombre)
                                                    <strong>{{ $boleta->receptor_nombre }}</strong><br>
                                                    @if($boleta->receptor_rut)
                                                        <span class="text-gray-500">{{ $boleta->receptor_rut }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400">Sin receptor</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                ${{ number_format($boleta->monto_total, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($boleta->status === 'emitida')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        ✓ Emitida
                                                    </span>
                                                @elseif($boleta->status === 'error')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        ✗ Error
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ $boleta->status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($boleta->status === 'emitida')
                                                    <div class="flex gap-2">
                                                        @if($boleta->pdf_path || $boleta->pdf_base64)
                                                            <a href="{{ route('boletas.pdf', $boleta->id) }}" target="_blank"
                                                                class="text-red-600 hover:text-red-900 font-semibold">
                                                                📄 PDF
                                                            </a>
                                                        @endif
                                                        @if($boleta->xml_base64)
                                                            <a href="{{ route('boletas.xml', $boleta->id) }}"
                                                                class="text-blue-600 hover:text-blue-900 font-semibold">
                                                                📋 XML
                                                            </a>
                                                        @endif
                                                    </div>
                                                @else
                                                    <button onclick="alert('{{ $boleta->error_message }}')" 
                                                        class="text-red-600 hover:text-red-900 font-semibold">
                                                        Ver Error
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($boleta->observaciones)
                                            <tr class="{{ $boleta->status === 'error' ? 'bg-red-50' : 'bg-gray-50' }}">
                                                <td colspan="6" class="px-6 py-2 text-sm text-gray-600">
                                                    <strong>Obs:</strong> {{ $boleta->observaciones }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $boletas->links() }}
                        </div>

                        <div class="mt-6 p-4 bg-blue-50 rounded">
                            <h3 class="font-bold text-blue-900 mb-2">💡 Información</h3>
                            <ul class="text-blue-800 text-sm space-y-1">
                                <li>• Las boletas se emiten directamente en el SII a través de Lioren</li>
                                <li>• Costo: ~$7,5 CLP por boleta (0.0002 UF)</li>
                                <li>• Los PDF y XML quedan guardados en tu base de datos</li>
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay boletas emitidas</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Comienza emitiendo tu primera boleta electrónica
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('integracion.boletas-form') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    + Emitir Boleta
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
