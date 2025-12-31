@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Mis Facturas y Boletas</h1>
        <p class="text-gray-600 mt-2">Documentos tributarios emitidos</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-8">
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Próximamente</h3>
            <p class="text-gray-600">Aquí podrás ver y descargar tus facturas y boletas</p>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('cliente.dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Volver al Dashboard
        </a>
    </div>
</div>
@endsection
