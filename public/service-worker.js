const CACHE_NAME = 'cookbook-v1';

// Install event - cache only essential files
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        // Try to cache the manifest, but don't fail if it doesn't exist
        return cache.add('/manifest.json').catch(() => {
          console.log('Could not cache manifest.json');
        });
      })
      .catch((error) => {
        console.log('Cache installation completed with notice:', error);
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Return cached response if available
        if (response) {
          return response;
        }

        // Otherwise fetch from network
        return fetch(event.request)
          .then((response) => {
            // Don't cache non-successful responses
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Cache successful responses
            const responseToCache = response.clone();
            caches.open(CACHE_NAME)
              .then((cache) => {
                cache.put(event.request, responseToCache).catch(() => {
                  // Silently fail if caching doesn't work
                });
              });

            return response;
          })
          .catch(() => {
            // Return offline page on network error
            return caches.match('/offline.html')
              .then((response) => response || new Response('Offline'));
          });
      })
  );
});
