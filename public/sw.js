const CACHE_NAME = 'erms-v1.0.0';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/favicon.png',
    '/icon-192.png',
    '/icon-512.png',
];

// ─── Install Event ─────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[SW] Caching static assets');
            // Cache assets individually to avoid install failure on missing files
            return Promise.allSettled(
                STATIC_ASSETS.map(url =>
                    cache.add(url).catch(err => console.warn('[SW] Failed to cache:', url, err))
                )
            );
        })
    );
    self.skipWaiting();
});

// ─── Activate Event ────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        })
    );
    self.clients.claim();
});

// ─── Fetch Event ───────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip cross-origin requests (CDN, APIs, etc.)
    if (url.origin !== location.origin) return;

    // Skip Vite HMR & development endpoints
    if (url.pathname.startsWith('/@') || url.pathname.startsWith('/vite')) return;

    // Strategy: Network-first for HTML pages, Cache-first for assets
    if (request.headers.get('accept')?.includes('text/html')) {
        // Network-first for HTML pages
        event.respondWith(
            fetch(request)
                .then(response => {
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request).then(cached => {
                        if (cached) return cached;
                        // Fallback to root page
                        return caches.match('/');
                    });
                })
        );
    } else {
        // Cache-first for static assets (CSS, JS, images, fonts)
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;

                return fetch(request).then(response => {
                    if (!response || response.status !== 200 || response.type === 'opaque') {
                        return response;
                    }
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    return response;
                });
            })
        );
    }
});

// ─── Background Sync (optional) ────────────────────────────────────────────
self.addEventListener('sync', (event) => {
    if (event.tag === 'background-sync') {
        console.log('[SW] Background sync triggered');
    }
});

// ─── Push Notifications (optional, ready for future use) ──────────────────
self.addEventListener('push', (event) => {
    if (!event.data) return;

    const data = event.data.json();
    const options = {
        body: data.body || 'New notification from ERMS',
        icon: '/icon-192.png',
        badge: '/favicon.png',
        vibrate: [100, 50, 100],
        data: { url: data.url || '/' },
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'ERMS Notification', options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data?.url || '/')
    );
});
