<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    @endif
    <style>
        /* Sidebar styles */
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

        /* Typography improvements */
        h1, h2, h3, h4, h5, h6 {
            letter-spacing: -0.02em;
            font-weight: 600;
        }
        body {
            line-height: 1.6;
        }
        .lead {
            font-size: 1.15rem;
            line-height: 1.7;
        }

        /* Recipe card hover effects */
        .card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3) !important;
            border-color: rgba(255, 255, 255, 0.15);
        }
        .card img.card-img-top {
            transition: transform 0.3s ease;
        }
        .card:hover img.card-img-top {
            transform: scale(1.05);
        }

        /* Button improvements */
        .btn {
            transition: all 0.2s ease;
            font-weight: 500;
            letter-spacing: 0.01em;
        }
        .btn:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-primary {
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
        }
        .btn-primary:hover {
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.4);
        }

        /* Link improvements */
        a {
            transition: color 0.2s ease;
        }

        /* Form improvements */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Badge improvements */
        .badge {
            font-weight: 500;
            letter-spacing: 0.02em;
            padding: 0.35em 0.65em;
        }

        /* Alert improvements */
        .alert {
            border-radius: 0.5rem;
            border: none;
        }

        /* Breadcrumb improvements */
        .breadcrumb-item + .breadcrumb-item::before {
            opacity: 0.5;
        }
        .breadcrumb-item.active {
            font-weight: 500;
        }

        /* Focus improvements for accessibility */
        *:focus-visible {
            outline: 2px solid var(--bs-primary);
            outline-offset: 2px;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Pagination improvements */
        .pagination {
            gap: 0.25rem;
        }
        .page-link {
            transition: all 0.2s ease;
            border-radius: 0.375rem;
            margin: 0 0.125rem;
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
    <script>
        // Initialize Bootstrap tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        });
    </script>
    @stack('scripts')
</body>
</html>
