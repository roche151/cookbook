<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Your personal recipe collection and cooking companion">
    <meta name="theme-color" content="#0d6efd">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Cookbook">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    {{ $head ?? '' }}
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    @endif
    <style>
        /* Layout structure */
        html, body {
            height: 100%;
            margin: 0;
        }
        
        body {
            display: flex;
            flex-direction: row;
        }
        
        @media (max-width: 767.98px) {
            body {
                flex-direction: column;
                padding-top: 0;
            }
        }
        
        .btn-danger {
            background-color: var(--bs-danger) !important;
            border-color: var(--bs-danger) !important;
            color: #fff !important;
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
    
    @auth
        <x-notifications-offcanvas />
    @endauth

    <div class="app-content-wrapper flex-grow-1 d-flex flex-column">
        <main class="app-content flex-grow-1">
            <div class="container py-4">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
                {{ $slot }}
            </div>
            @auth
                {{-- feedback button fixed in bottom right corner --}}
                <button class="btn btn-primary position-fixed bottom-0 end-0 m-4" data-toggle="tooltip" data-bs-toggle="modal" data-bs-target="#feedbackModal" title="Feedback" style="z-index: 1050;">
                    <i class="fas fa-comments"></i>
                </button>
                {{-- feedback modal --}}
                <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="feedbackModalLabel"><i class="fa-solid fa-comments me-2 text-primary"></i>Feedback</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="{{ route('feedback.store') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="feedbackType" class="form-label">Type</label>
                                        <select name="type" id="feedbackType" class="form-select" required>
                                            <option value="" disabled selected>Please select</option>
                                            <option value="bug">Bug Report</option>
                                            <option value="feature">Feature Request</option>
                                            <option value="general">General Feedback</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="feedbackMessage" class="form-label">Your Feedback</label>
                                        <textarea class="form-control" id="feedbackMessage" name="message" rows="5" required></textarea>
                                    </div>
                                    <div class="mb-3 text-end">
                                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endauth
        </main>

        <footer class="bg-body text-center py-3 border-top mt-auto">
            <div class="container">
                <small class="text-muted">&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</small>
            </div>
        </footer>
    </div>

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

        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle logic
            const toggleBtn = document.getElementById('themeToggle');
            const toggleBtnMobile = document.getElementById('themeToggleMobile');
            
            function setIcon(btn, theme) {
                if (!btn) return;
                const i = btn.querySelector('i');
                if (!i) return;
                if (theme === 'light') {
                    i.className = 'fa-regular fa-moon';
                } else {
                    i.className = 'fa-regular fa-sun';
                }
            }
            
            function toggleTheme() {
                const now = document.documentElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-bs-theme', now);
                try { localStorage.setItem('culina-theme', now); } catch (e) {}
                setIcon(toggleBtn, now);
                setIcon(toggleBtnMobile, now);
            }
            
            // Initialize icon to current theme
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
            setIcon(toggleBtn, currentTheme);
            setIcon(toggleBtnMobile, currentTheme);
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleTheme);
            }
            if (toggleBtnMobile) {
                toggleBtnMobile.addEventListener('click', toggleTheme);
            }
            
            // Toast notification for email verification
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('verified')) {
                const toast = document.createElement('div');
                toast.className = 'toast-notification';
                toast.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: var(--bs-success);
                    color: white;
                    padding: 12px 20px;
                    border-radius: 0.5rem;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    animation: slideInUp 0.3s ease;
                    z-index: 1050;
                `;
                toast.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i>Email verified successfully!';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.animation = 'slideInUp 0.3s ease reverse';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        });

        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then((registration) => {
                        console.log('Service Worker registered:', registration);
                    })
                    .catch((error) => {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const feedbackForm = document.querySelector('#feedbackModal form');
        if (!feedbackForm) return;

        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(feedbackForm);
            fetch(feedbackForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
                if (modal) modal.hide();

                // Show toast
                const toast = document.createElement('div');
                toast.className = 'toast-notification';
                toast.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: var(--bs-success);
                    color: white;
                    padding: 12px 20px;
                    border-radius: 0.5rem;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    animation: slideInUp 0.3s ease;
                    z-index: 1050;
                `;
                toast.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i>' + (data.message || 'Feedback submitted!');
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.animation = 'slideInUp 0.3s ease reverse';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);

                feedbackForm.reset();
            })
            .catch(() => {
                alert('There was an error submitting your feedback.');
            });
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
