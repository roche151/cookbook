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
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">Menu</button>
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
                <a class="nav-link" href="{{ route('login') }}">Login</a>
                <a class="nav-link" href="{{ route('register') }}">Register</a>
            @else
                <div class="px-3">
                    <strong>{{ Auth::user()->name }}</strong>
                    <div class="mt-2">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('profile.edit') }}">Profile</a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Logout</button>
                        </form>
                    </div>
                </div>
            @endguest
        </nav>
    </div>
    </div>

<!-- Sidebar for md+ screens -->
<aside class="app-sidebar bg-body d-none d-md-block p-3">
    <a class="navbar-brand d-block mb-3" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
    <nav class="nav flex-column">
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
    </nav>
    <hr>
    <div class="mt-2">
        @guest
            <a class="nav-link" href="{{ route('login') }}">Login</a>
            <a class="nav-link" href="{{ route('register') }}">Register</a>
        @else
            <div>
                <strong>{{ Auth::user()->name }}</strong>
                <div class="mt-2">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('profile.edit') }}">Profile</a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Logout</button>
                    </form>
                </div>
            </div>
        @endguest
    </div>
</aside>

<!-- Logout form is no longer needed -->
