<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago Exitoso - Flow</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-green-600 mb-4">¡Pago Exitoso!</h1>

                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <p class="text-sm text-green-800">
                        <strong>Número de orden Flow:</strong> {{ $payment['flowOrder'] ?? 'N/A' }}
                    </p>
                    <p class="text-sm text-green-800">
                        <strong>Monto:</strong> ${{ number_format($payment['amount'] ?? 0, 0, ',', '.') }} CLP
                    </p>
                    <p class="text-sm text-green-800">
                        <strong>Estado:</strong> Pagado
                    </p>
                    <p class="text-sm text-green-800">
                        <strong>Token:</strong> {{ $payment['token'] ?? 'N/A' }}
                    </p>
                </div>

                <a href="/" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>
</body>

</html>
