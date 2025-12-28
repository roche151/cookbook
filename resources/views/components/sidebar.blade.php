<!-- Sidebar component: renders mobile topbar, offcanvas, and md+ sidebar -->
{{-- Usage: <x-sidebar /> --}} 
@php
    $sidebarLinks = config('sidebar.links', []);
    /**
     * Helper to resolve link href. Supports 'route' (named route) or 'href' (path)
     */
    $resolveHref = function ($link) {
        if (!empty($link['route'])) {
            try {
                return route($link['route']);
            } catch (\Exception $e) {
                // fall through to href if route not found
            }
        }
        return url($link['href'] ?? '#');
    };

    $isActiveLink = function ($link, $href) {
        // If route provided, compare route names first
        if (!empty($link['route'])) {
            try {
                if (\Illuminate\Support\Facades\Route::currentRouteName() === $link['route']) {
                    return true;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Compare path portions to avoid scheme/host differences
        $currentPath = parse_url(url()->current(), PHP_URL_PATH) ?: '/';
        $linkPath = parse_url($href, PHP_URL_PATH) ?: '/';

        return rtrim($currentPath, '/') === rtrim($linkPath, '/');
    };
@endphp

<!-- Mobile topbar (only visible on small screens) -->
<nav class="navbar navbar-dark bg-body d-md-none">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar"><i class="fa fa-bars" aria-hidden="true"></i></button>
    </div>
</nav>

<!-- Offcanvas sidebar for small screens -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarLabel">{{ config('app.name', 'Laravel') }}</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="nav flex-column p-3">
            @foreach ($sidebarLinks as $link)
                @if(isset($link['auth']) && $link['auth'] === 'auth' && auth()->guest())
                    @continue
                @endif
                @if(isset($link['auth']) && $link['auth'] === 'guest' && auth()->check())
                    @continue
                @endif
                @if(isset($link['auth']) && $link['auth'] === 'admin' && (!auth()->check() || !auth()->user()->is_admin))
                    @continue
                @endif
                @php($href = $resolveHref($link))
                @php($active = $isActiveLink($link, $href))
                <a class="nav-link{{ $active ? ' active text-white bg-primary rounded' : '' }}" href="{{ $href }}" aria-current="{{ $active ? 'page' : '' }}">
                    @if(!empty($link['icon']))
                        {!! $link['icon'] !!}
                    @elseif(!empty($link['icon_class']))
                        <i class="{{ $link['icon_class'] }} me-2" aria-hidden="true"></i>
                    @endif
                    {{ $link['label'] }}
                </a>
            @endforeach

            <hr>

            @guest
                <a class="nav-link" href="{{ route('login') }}">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                </a>
                <a class="nav-link" href="{{ route('register') }}">
                    <i class="fa-solid fa-user-plus me-2"></i>Register
                </a>
            @else
                <div class="px-2 py-3">
                    <a href="{{ route('profile.edit') }}" class="d-flex align-items-center mb-3 text-decoration-none text-body" style="transition: background 0.2s ease; padding: 0.5rem; margin: -0.5rem; border-radius: 0.375rem;">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                            <span class="text-white fw-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-truncate">{{ Auth::user()->name }}</div>
                            <small class="text-muted">{{ Auth::user()->email }}</small>
                        </div>
                    </a>
                    <div class="d-grid gap-2">
                        <button id="themeToggleMobile" class="btn btn-sm btn-outline-secondary" type="button" aria-label="Toggle theme" title="Toggle dark/light">
                            <i class="fa-regular fa-sun"></i> Theme
                        </button>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            @endguest
        </nav>
    </div>
    </div>

<!-- Sidebar for md+ screens -->
<aside class="app-sidebar bg-body d-none d-md-flex flex-column p-0" style="border-right: 1px solid var(--bs-border-color);">
    <!-- Brand/Logo Section -->
    <div class="sidebar-header p-3 border-bottom d-flex align-items-center">
        <a class="sidebar-brand d-flex align-items-center text-decoration-none" href="{{ url('/') }}">
            <div class="d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <img src="{{ asset('favicon.svg') }}" alt="Culina" width="28" height="28" style="display:block; border-radius:50%; box-shadow: 0 2px 6px rgba(0,0,0,0.25);" />
            </div>
            <span class="sidebar-text fs-5 fw-bold ms-3">{{ config('app.name', 'Laravel') }}</span>
        </a>
    </div>
    
    <!-- Theme Toggle (separate row when expanded) -->
    <div class="sidebar-theme-row px-3 pt-3 pb-2">
        <button id="themeToggle" class="btn btn-sm btn-outline-secondary w-100 d-flex align-items-center justify-content-center" type="button" aria-label="Toggle theme" title="Toggle dark/light">
            <i class="fa-regular fa-sun"></i><span class="sidebar-text ms-2">Toggle Theme</span>
        </button>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-grow-1 p-3 overflow-auto">
        <div class="nav flex-column gap-1">
            @foreach ($sidebarLinks as $link)
                @if(isset($link['auth']) && $link['auth'] === 'auth' && auth()->guest())
                    @continue
                @endif
                @if(isset($link['auth']) && $link['auth'] === 'guest' && auth()->check())
                    @continue
                @endif
                @if(isset($link['auth']) && $link['auth'] === 'admin' && (!auth()->check() || !auth()->user()->is_admin))
                    @continue
                @endif
                @php($href = $resolveHref($link))
                @php($active = $isActiveLink($link, $href))
                <a class="nav-link rounded d-flex align-items-center{{ $active ? ' active bg-primary text-white' : ' text-body' }}" 
                   href="{{ $href }}" 
                   aria-current="{{ $active ? 'page' : '' }}"
                   data-bs-toggle="tooltip"
                   data-bs-placement="right"
                   title="{{ $link['label'] }}"
                   style="transition: all 0.2s ease; padding: 0.625rem 0.75rem;">
                    @if(!empty($link['icon']))
                        <span class="sidebar-icon d-flex align-items-center justify-content-center" style="width: 24px;">{!! $link['icon'] !!}</span>
                    @elseif(!empty($link['icon_class']))
                        <i class="{{ $link['icon_class'] }} sidebar-icon d-flex align-items-center justify-content-center" style="width: 24px;" aria-hidden="true"></i>
                    @endif
                    <span class="sidebar-text ms-3">{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <!-- User Section -->
    <div class="sidebar-footer p-3 border-top mt-auto">
        @guest
            <div class="d-grid gap-2">
                <a class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" href="{{ route('login') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Login">
                    <i class="fa-solid fa-right-to-bracket sidebar-icon"></i><span class="sidebar-text ms-2">Login</span>
                </a>
                <a class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" href="{{ route('register') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Register">
                    <i class="fa-solid fa-user-plus sidebar-icon"></i><span class="sidebar-text ms-2">Register</span>
                </a>
            </div>
        @else
            <div class="sidebar-user-section">
                <a href="{{ route('profile.edit') }}" class="sidebar-user-info mb-3 d-flex align-items-center text-decoration-none text-body" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ Auth::user()->name }}" style="transition: background 0.2s ease; padding: 0.5rem; margin: -0.5rem; border-radius: 0.375rem;">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <span class="text-white fw-bold fs-5">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    </div>
                    <div class="sidebar-text flex-grow-1 overflow-hidden ms-3">
                        <div class="fw-semibold text-truncate">{{ Auth::user()->name }}</div>
                        <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">{{ Auth::user()->email }}</small>
                    </div>
                </a>
                <div class="sidebar-user-actions d-grid gap-2">
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary w-100 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="right" title="Logout">
                            <i class="fa-solid fa-right-from-bracket sidebar-icon" style="width: 24px;"></i><span class="sidebar-text ms-2">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        @endguest
    </div>
</aside>

<style>
    .app-sidebar .nav-link:not(.active):hover {
        background-color: var(--bs-secondary-bg);
    }
    
    .app-sidebar .nav-link {
        font-weight: 500;
    }
    
    
    .sidebar-user-info:hover {
        background-color: var(--bs-secondary-bg) !important;
    }
</style>

</style>