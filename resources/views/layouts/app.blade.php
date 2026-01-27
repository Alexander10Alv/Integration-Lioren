<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <style>
        body {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }
    </style>
    <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    @auth
    @if(auth()->user()->hasRole('admin'))
    <script>
        // Actualizar contador de mensajes sin leer
        function updateUnreadCount() {
            fetch('{{ route("admin.chats.unreadCount") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('unreadBadge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Actualizar al cargar la página
        updateUnreadCount();

        // Actualizar cada 2 minutos (120000 ms)
        setInterval(updateUnreadCount, 120000);
    </script>
    @endif
    @endauth
</body>

</html>
