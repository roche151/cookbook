<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    @endif
    <style>
        .app-sidebar { width: 250px; }
        .btn-danger {
            background-color: var(--bs-danger) !important;
            border-color: var(--bs-danger) !important;
            color: #fff !important;
        }
        @media (min-width: 768px) {
            body { padding-left: 250px; }
            .app-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                overflow-y: auto;
                z-index: 1030;
                box-shadow: 2px 0 6px rgba(0,0,0,0.04);
                background: var(--bs-body-bg, #000);
            }
        }
        @media (max-width: 767.98px) {
            .app-sidebar { display: none; }
        }
    </style>
    @stack('head')
</head>
<body>
    <x-sidebar />

    <main class="app-content container mt-4">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        {{ $slot }}
    </main>

    <footer class="bg-body text-center py-4 mt-5">
        <div class="container">
            <small>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</small>
        </div>
    </footer>

    @if (!(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))))
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    @endif
    @stack('scripts')
</body>
</html>
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
