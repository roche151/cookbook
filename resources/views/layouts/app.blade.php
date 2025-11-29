<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
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

        /* Recipe card styling */
        .card {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Button improvements */
        .btn {
            font-weight: 500;
            letter-spacing: 0.01em;
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
    <style>
        /* Light theme polish */
        [data-bs-theme="light"] {
            --bs-body-bg: #ffffff; /* cleaner modern white */
            --bs-body-color: #1f2937;
            --bs-border-color: #e5e7eb;
            --bs-secondary-bg: #ffffff; /* avoid gray sections */
            --bs-tertiary-bg: #ffffff; /* ensure tertiary panels are white */
        }
        [data-bs-theme="light"] .bg-body-secondary { background-color: #ffffff !important; }
        [data-bs-theme="light"] .bg-body-tertiary { background-color: #ffffff !important; }
        [data-bs-theme="light"] .bg-light { background-color: #ffffff !important; }
        /* Home hero used bg-dark bg-opacity-10 which appears gray on white */
        [data-bs-theme="light"] .bg-dark.bg-opacity-10 { background-color: transparent !important; }
        /* Common hero wrappers */
        [data-bs-theme="light"] .hero,
        [data-bs-theme="light"] .showcase,
            [data-bs-theme="light"] .hero-section,
            [data-bs-theme="light"] .home-hero,
            [data-bs-theme="light"] .hero-card {
            background-color: #ffffff !important;
        }
        [data-bs-theme="light"] .bg-secondary-subtle { background-color: #ffffff !important; }
        [data-bs-theme="light"] .app-sidebar {
            background: #ffffff;
            border-right: 1px solid var(--bs-border-color);
        }
        [data-bs-theme="light"] .card {
            background: #ffffff;
            border-color: #e5e7eb;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        [data-bs-theme="light"] .card .card-header {
            background: #ffffff;
            border-bottom-color: #e5e7eb;
        }
        [data-bs-theme="light"] .btn-outline-secondary {
            border-color: #9ca3af;
            color: #374151;
        }
        [data-bs-theme="light"] .btn-outline-secondary:hover {
            background: #f3f4f6;
        }
        [data-bs-theme="light"] .badge.bg-secondary.bg-opacity-25 {
            background: #e6f0ff !important; /* higher contrast */
            border-color: #93c5fd !important;
            color: #1e3a8a !important;
        }
        [data-bs-theme="light"] .badge.bg-secondary {
            background: #dde7ff !important;
            color: #1f2937 !important;
            border-color: #93c5fd !important;
        }
        [data-bs-theme="light"] .text-muted { color: #4b5563 !important; }
        [data-bs-theme="light"] .alert-info {
            background: #eff6ff;
            color: #0c4a6e;
        }
        [data-bs-theme="light"] .breadcrumb .breadcrumb-item a {
            color: #2563eb;
        }
        [data-bs-theme="light"] .form-control,
        [data-bs-theme="light"] .form-select {
            background: #ffffff;
            border-color: #e5e7eb;
        }
        [data-bs-theme="light"] .img-thumbnail,
        [data-bs-theme="light"] .card-img-top.d-flex,
        [data-bs-theme="light"] .card .ratio,
        [data-bs-theme="light"] .card .image-wrapper {
            background: transparent;
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
        // Apply saved theme on first paint
        (function() {
            try {
                var saved = localStorage.getItem('culina-theme');
                var theme = saved === 'light' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', theme);
            } catch (e) {}
        })();

        // Initialize Bootstrap tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            // Theme toggle logic
            const toggleBtn = document.getElementById('themeToggle');
            function setIcon(theme) {
                if (!toggleBtn) return;
                const i = toggleBtn.querySelector('i');
                if (!i) return;
                if (theme === 'light') {
                    i.className = 'fa-regular fa-moon';
                } else {
                    i.className = 'fa-regular fa-sun';
                }
            }
            // Initialize icon to current theme
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
            setIcon(currentTheme);
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const now = document.documentElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-bs-theme', now);
                    try { localStorage.setItem('culina-theme', now); } catch (e) {}
                    setIcon(now);
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
