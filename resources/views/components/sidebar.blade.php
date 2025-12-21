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
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                            <span class="text-white fw-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-truncate">{{ Auth::user()->name }}</div>
                            <small class="text-muted">{{ Auth::user()->email }}</small>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button id="themeToggleMobile" class="btn btn-sm btn-outline-secondary" type="button" aria-label="Toggle theme" title="Toggle dark/light">
                            <i class="fa-regular fa-sun"></i> Theme
                        </button>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('profile.edit') }}">
                            <i class="fa-solid fa-user me-1"></i> Profile
                        </a>
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
    <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
        <a class="d-flex align-items-center text-decoration-none" href="{{ url('/') }}">
            <div class="d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                <img src="{{ asset('favicon.svg') }}" alt="Culina" width="28" height="28" style="display:block; border-radius:50%; box-shadow: 0 2px 6px rgba(0,0,0,0.25);" />
            </div>
            <span class="fs-5 fw-bold">{{ config('app.name', 'Laravel') }}</span>
        </a>
        <button id="themeToggle" class="btn btn-sm btn-outline-secondary" type="button" aria-label="Toggle theme" title="Toggle dark/light">
            <i class="fa-regular fa-sun"></i>
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
                @php($href = $resolveHref($link))
                @php($active = $isActiveLink($link, $href))
                <a class="nav-link rounded px-3 py-2 d-flex align-items-center{{ $active ? ' active bg-primary text-white' : ' text-body' }}" 
                   href="{{ $href }}" 
                   aria-current="{{ $active ? 'page' : '' }}"
                   style="transition: all 0.2s ease;">
                    @if(!empty($link['icon']))
                        <span class="me-2" style="width: 20px; text-align: center;">{!! $link['icon'] !!}</span>
                    @elseif(!empty($link['icon_class']))
                        <i class="{{ $link['icon_class'] }} me-2" style="width: 20px; text-align: center;" aria-hidden="true"></i>
                    @endif
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <!-- User Section -->
    <div class="p-3 border-top mt-auto">
        @guest
            <div class="d-grid gap-2">
                <a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                </a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('register') }}">
                    <i class="fa-solid fa-user-plus me-2"></i>Register
                </a>
            </div>
        @else
            <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0 me-2" style="width: 40px; height: 40px;">
                    <span class="text-white fw-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate small">{{ Auth::user()->name }}</div>
                    <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">{{ Auth::user()->email }}</small>
                </div>
            </div>
            <div class="d-grid gap-2">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('profile.edit') }}">
                    <i class="fa-solid fa-user me-1"></i> Profile
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                    </button>
                </form>
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
</style>