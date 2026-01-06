<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago Fallido - Flow</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-red-600 mb-4">Pago No Completado</h1>

                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <p class="text-sm text-red-800">
                        El pago no pudo ser procesado correctamente.
                    </p>
                    @if(isset($payment['status']))
                    <p class="text-sm text-red-800 mt-2">
                        <strong>Estado:</strong>
                        @switch($payment['status'])
                        @case(1)
                        Pendiente
                        @break
                        @case(3)
                        Rechazado
                        @break
                        @case(4)
                        Anulado
                        @break
                        @default
                        Desconocido
                        @endswitch
                    </p>
                    @endif
                </div>

                <div class="space-y-3">
                    <a href="/flow/payment-form" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Intentar nuevamente
                    </a>

                    <br>

                    <a href="/" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
