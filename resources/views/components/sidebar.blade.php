@php
    $sidebarLinks = config('sidebar.links', []);
    $resolveHref = function ($link) {
        if (!empty($link['route'])) {
            try {
                return route($link['route']);
            } catch (\Exception $e) {}
        }
        return url($link['href'] ?? '#');
    };
    $isActiveLink = function ($link, $href) {
        if (!empty($link['route'])) {
            try {
                if (\Illuminate\Support\Facades\Route::currentRouteName() === $link['route']) {
                    return true;
                }
            } catch (\Exception $e) {}
        }
        $currentPath = parse_url(url()->current(), PHP_URL_PATH) ?: '/';
        $linkPath = parse_url($href, PHP_URL_PATH) ?: '/';
        return rtrim($currentPath, '/') === rtrim($linkPath, '/');
    };
@endphp

<!-- Mobile Header -->
<header class="mobile-header d-md-none bg-body border-bottom">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ asset('favicon.svg') }}" alt="Culina" width="32" height="32" class="me-2" style="border-radius: 50%;" />
            <span class="fs-5 fw-bold">{{ config('app.name') }}</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            @auth
                @php
                    $unreadCount = 0;
                    try {
                        $unreadCount = auth()->user()->unreadNotifications()->count();
                    } catch (\Exception $e) {}
                @endphp
                <button class="btn btn-sm btn-ghost position-relative" 
                        type="button" 
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#notificationsOffcanvas">
                    <i class="fa-solid fa-bell fs-5"></i>
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.25em 0.4em;">{{ $unreadCount }}</span>
                    @endif
                </button>
            @endauth
            <button class="btn btn-sm btn-ghost" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                <i class="fa-solid fa-bars fs-5"></i>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Offcanvas -->
<div class="offcanvas offcanvas-start mobile-offcanvas" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset('favicon.svg') }}" alt="Culina" width="32" height="32" style="border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" />
            <h5 class="offcanvas-title mb-0 fw-bold">{{ config('app.name') }}</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    
    <div class="offcanvas-body p-0 d-flex flex-column">
        @auth
            <div class="mobile-user-section">
                <a href="{{ route('profile.edit') }}" class="mobile-user-info" data-bs-dismiss="offcanvas">
                    <div class="avatar-circle bg-primary">
                        <span class="text-white fw-bold fs-5">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-email">{{ Auth::user()->email }}</div>
                    </div>
                    <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                </a>
            </div>
        @endauth
        
        <nav class="mobile-nav">
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
                @php
                    $href = $resolveHref($link);
                    $active = $isActiveLink($link, $href);
                @endphp
                <a class="mobile-nav-item {{ $active ? 'active' : '' }}" href="{{ $href }}">
                    @if(!empty($link['icon']))
                        <span class="nav-icon">{!! $link['icon'] !!}</span>
                    @elseif(!empty($link['icon_class']))
                        <i class="{{ $link['icon_class'] }} nav-icon"></i>
                    @endif
                    <span class="nav-text">{{ $link['label'] }}</span>
                    @if($active)
                        <span class="ms-auto active-indicator"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mobile-footer">
            @guest
                <div class="d-grid gap-2">
                    <a href="{{ route('login') }}" class="btn btn-primary" data-bs-dismiss="offcanvas">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
                        <i class="fa-solid fa-user-plus me-2"></i>Register
                    </a>
                </div>
            @else
                <button id="themeToggleMobile" class="mobile-action-btn" type="button">
                    <i class="fa-regular fa-sun"></i>
                    <span>Theme</span>
                </button>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="mobile-action-btn logout-btn w-100">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            @endguest
        </div>
    </div>
</div>

<!-- Desktop Sidebar -->
<aside class="app-sidebar d-none d-md-flex flex-column">
    <!-- Header -->
    <div class="sidebar-header">
        <a href="{{ url('/') }}" class="sidebar-brand">
            <img src="{{ asset('favicon.svg') }}" alt="Culina" width="32" height="32" />
            <span class="brand-text">{{ config('app.name') }}</span>
        </a>
    </div>

    <!-- Quick Actions -->
    <div class="sidebar-actions">
        <button id="themeToggle" class="action-btn" type="button" title="Toggle theme">
            <i class="fa-regular fa-sun"></i>
            <span class="action-text">Theme</span>
        </button>
        @auth
            @php
                $unreadCount = 0;
                try {
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                } catch (\Exception $e) {}
            @endphp
                <button class="action-btn position-relative" 
                        type="button" 
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#notificationsOffcanvas"
                        title="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    @if($unreadCount > 0)
                        <span class="notification-badge">{{ $unreadCount }}</span>
                    @endif
                    <span class="action-text">Alerts</span>
                </button>
            @else
                <a class="action-btn position-relative text-decoration-none" 
                        type="button"
                        title="Notifications"
                        href="{{ route('login') }}">
                    <i class="fa-solid fa-bell"></i>
                    <span class="action-text">Alerts</span>
                </a>
        @endauth
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
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
            @php
                $href = $resolveHref($link);
                $active = $isActiveLink($link, $href);
            @endphp
            <a class="nav-item {{ $active ? 'active' : '' }}" href="{{ $href }}">
                @if(!empty($link['icon']))
                    <span class="nav-icon">{!! $link['icon'] !!}</span>
                @elseif(!empty($link['icon_class']))
                    <i class="{{ $link['icon_class'] }} nav-icon"></i>
                @endif
                <span class="nav-text">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <!-- User Section -->
    <div class="sidebar-footer">
        @guest
            <div class="d-grid gap-2">
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                </a>
                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="fa-solid fa-user-plus me-2"></i>Register
                </a>
            </div>
        @else
            <a href="{{ route('profile.edit') }}" class="user-info">
                <div class="avatar-circle bg-primary">
                    <span class="text-white fw-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
                <div class="user-details">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-email">{{ Auth::user()->email }}</div>
                </div>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                    <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                </button>
            </form>
        @endguest
    </div>
</aside>

<style>
/* Mobile Header */
.mobile-header {
    z-index: 1020;
    position: sticky;
    top: 0;
}

.mobile-header .container-fluid {
    padding: 0.75rem 1rem;
}

.btn-ghost {
    background: transparent;
    border: none;
    color: var(--bs-body-color);
    padding: 0.5rem;
}

.btn-ghost:hover {
    background-color: var(--bs-secondary-bg);
    color: var(--bs-body-color);
}

/* Desktop Sidebar */
.app-sidebar {
    width: 280px;
    min-width: 280px;
    max-height: 100vh;
    background: var(--bs-body-bg);
    border-right: 1px solid var(--bs-border-color);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-header {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--bs-body-color);
    transition: opacity 0.2s;
}

.sidebar-brand:hover {
    opacity: 0.8;
}

.sidebar-brand img {
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.brand-text {
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: -0.02em;
}

/* Quick Actions */
.sidebar-actions {
    display: flex;
    gap: 0.5rem;
    padding: 1rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.action-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: var(--bs-secondary-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 0.5rem;
    color: var(--bs-body-color);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    cursor: pointer;
}

.action-btn:hover {
    background: var(--bs-tertiary-bg);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.action-btn i {
    font-size: 1rem;
}

.notification-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: var(--bs-danger);
    color: white;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.15em 0.4em;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}

/* Navigation */
.sidebar-nav {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    text-decoration: none;
    color: var(--bs-body-color);
    font-weight: 500;
    font-size: 0.9375rem;
    transition: all 0.15s;
    position: relative;
}

.nav-item:hover {
    background: var(--bs-secondary-bg);
    color: var(--bs-primary);
}

.nav-item.active {
    background: var(--bs-primary);
    color: white;
    box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.3);
}

.nav-item.active:hover {
    color: white;
}

.nav-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

/* Footer */
.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid var(--bs-border-color);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 0.5rem;
    text-decoration: none;
    color: var(--bs-body-color);
    transition: background 0.2s;
    margin-bottom: 0.5rem;
}

.user-info:hover {
    background: var(--bs-secondary-bg);
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.user-details {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    font-size: 0.9375rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-email {
    font-size: 0.75rem;
    color: var(--bs-secondary-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Mobile Offcanvas Styles */
.mobile-offcanvas {
    width: 320px !important;
    max-width: 85vw;
}

.mobile-offcanvas .offcanvas-header {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.mobile-user-section {
    padding: 1rem;
    border-bottom: 1px solid var(--bs-border-color);
    background: var(--bs-secondary-bg);
}

.mobile-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 0.75rem;
    text-decoration: none;
    color: var(--bs-body-color);
    transition: all 0.2s;
    background: var(--bs-body-bg);
}

.mobile-user-info:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.mobile-nav {
    flex: 1;
    padding: 0.5rem;
    overflow-y: auto;
}

.mobile-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    margin-bottom: 0.25rem;
    border-radius: 0.625rem;
    text-decoration: none;
    color: var(--bs-body-color);
    font-weight: 500;
    font-size: 0.9375rem;
    transition: all 0.2s;
    position: relative;
}

.mobile-nav-item:hover {
    background: var(--bs-secondary-bg);
    color: var(--bs-primary);
    transform: translateX(4px);
}

.mobile-nav-item.active {
    background: linear-gradient(135deg, var(--bs-primary) 0%, color-mix(in srgb, var(--bs-primary) 85%, black) 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.4);
}

.mobile-nav-item.active:hover {
    color: white;
    transform: translateX(2px);
}

.mobile-nav-item .nav-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
}

.mobile-nav-item .active-indicator {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: white;
    box-shadow: 0 0 8px rgba(255,255,255,0.6);
}

.mobile-footer {
    padding: 1rem;
    border-top: 1px solid var(--bs-border-color);
    background: var(--bs-secondary-bg);
}

.mobile-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 0.625rem;
    color: var(--bs-body-color);
    font-weight: 500;
    font-size: 0.9375rem;
    transition: all 0.2s;
    cursor: pointer;
}

.mobile-action-btn:hover {
    background: var(--bs-tertiary-bg);
    border-color: var(--bs-primary);
    color: var(--bs-primary);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.mobile-action-btn.logout-btn {
    border-color: var(--bs-danger);
    color: var(--bs-danger);
}

.mobile-action-btn.logout-btn:hover {
    background: var(--bs-danger);
    color: white;
    border-color: var(--bs-danger);
}

.mobile-action-btn i {
    font-size: 1rem;
}

.hover-bg {
    transition: background 0.2s;
}

.hover-bg:hover {
    background: var(--bs-secondary-bg);
}

/* Scrollbar */
.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: var(--bs-border-color);
    border-radius: 10px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: var(--bs-secondary-color);
}

/* Main content area */
.app-content-wrapper {
    min-height: 100vh;
    overflow-y: auto;
}

.app-content {
    flex: 1 0 auto;
}

@media (max-width: 767.98px) {
    .app-content-wrapper {
        min-height: calc(100vh - 60px);
    }
}
</style>

</style>