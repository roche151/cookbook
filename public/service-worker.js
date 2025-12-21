// Cookbook PWA Service Worker
// Strategy:
// - Do NOT cache HTML/documents; use network-first with offline fallback
// - Cache static assets (CSS/JS/fonts/images) with cache-first
// - Purge cache entries when server returns 404/410

const APP_CACHE = 'cookbook-app-v2';
const ASSET_CACHE = 'cookbook-assets-v2';

self.addEventListener('install', (event) => {
  event.waitUntil(
    (async () => {
      // Pre-cache only the offline page; everything else is cached on demand
      try {
        const cache = await caches.open(APP_CACHE);
        await cache.add('/offline.html');
      } catch (e) {
        // Ignore failures
        console.log('Offline page not cached at install:', e);
      }
    })()
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      const keys = await caches.keys();
      await Promise.all(
        keys.map((key) => {
          if (key !== APP_CACHE && key !== ASSET_CACHE) {
            return caches.delete(key);
          }
        })
      );
      await self.clients.claim();
    })()
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Only handle same-origin requests
  const url = new URL(req.url);
  const isSameOrigin = url.origin === self.location.origin;
  if (!isSameOrigin) return;

  // Network-first for navigations/documents (HTML)
  const isDocument = req.mode === 'navigate' || req.destination === 'document';
  if (isDocument) {
    event.respondWith(
      fetch(req)
        .then(async (resp) => {
          // If server says 404/410, ensure any stale cache entry is removed
          if (resp && (resp.status === 404 || resp.status === 410)) {
            try { const cache = await caches.open(APP_CACHE); await cache.delete(req); } catch {}
          }
          return resp;
        })
        .catch(async () => {
          // Offline fallback
          const cachedOffline = await caches.match('/offline.html');
          return cachedOffline || new Response('Offline', { status: 503, statusText: 'Offline' });
        })
    );
    return;
  }

  // Cache-first for static assets
  const assetDestinations = ['style', 'script', 'image', 'font'];
  if (assetDestinations.includes(req.destination)) {
    event.respondWith(
      caches.match(req).then((cached) => {
        if (cached) return cached;
        return fetch(req)
          .then(async (resp) => {
            if (resp && resp.status === 200) {
              try {
                const cache = await caches.open(ASSET_CACHE);
                await cache.put(req, resp.clone());
              } catch {}
            }
            return resp;
          })
          .catch(() => {
            // If asset missing and offline, try offline page as a last resort
            return caches.match('/offline.html');
          });
      })
    );
    return;
  }

  // Default: pass-through network with offline fallback
  event.respondWith(
    fetch(req).catch(() => caches.match('/offline.html'))
  );
});
