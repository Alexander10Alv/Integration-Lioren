@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Mis Pedidos</h1>
        <p class="text-gray-600 mt-2">Historial de pedidos realizados</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-8">
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Próximamente</h3>
            <p class="text-gray-600">Aquí podrás ver el historial de tus pedidos</p>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('cliente.dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Volver al Dashboard
        </a>
    </div>
</div>
@endsection
