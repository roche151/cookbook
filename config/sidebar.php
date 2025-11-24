<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sidebar Links
    |--------------------------------------------------------------------------
    |
    | Provide an array of links for the sidebar. Each link may contain:
    |  - label: Text to show
    |  - href:  URL path (passed to url())
    |  - route: optional named route (will be used instead of href)
    |  - auth:  null | 'auth' | 'guest'  (null shows to everyone)
    |
    */

    'links' => [
        // Example entries. Add 'icon' (raw HTML) or 'icon_class' for an <i> tag.
        // These use Font Awesome classes (bundled via Vite).
        ['label' => 'Home',    'href' => '/',        'auth' => null, 'icon_class' => 'fa-solid fa-house'],
        ['label' => 'About',   'href' => '/about',   'auth' => null, 'icon_class' => 'fa-solid fa-circle-info'],
        ['label' => 'Recipes', 'href' => '/recipes', 'auth' => null, 'icon_class' => 'fa-solid fa-book-open'],
        // Named route example (only for authenticated users)
        // ['label' => 'Dashboard', 'route' => 'dashboard', 'auth' => 'auth', 'icon_class' => 'fa-solid fa-gauge'],
    ],

];
