<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ $appConfig->nombre_empresa ?? 'TPV Panadería' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        @include('partials.sidebar')

        <div class="main-content">
            @include('partials.topbar')

            <main class="page-content">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>{{ session('warning') }}</div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('.topbar-toggle');
            const sidebar = document.querySelector('.sidebar');
            toggle?.addEventListener('click', () => sidebar.classList.toggle('show'));

            // Auto-hide alerts
            document.querySelectorAll('.alert').forEach(a => {
                setTimeout(() => { a.style.opacity = 0; setTimeout(() => a.remove(), 300); }, 5000);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
