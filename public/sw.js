const CACHE_NAME = 'erms-v1.0.2';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/favicon.png',
    '/icon-192.png',
    '/icon-512.png',
];

// ─── Install Event ─────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker v1.0.2...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
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
    console.log('[SW] Activating service worker v1.0.2...');
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

    // ── STRICT BYPASS: Never touch ANY /admin, /poll, /events or live monitoring requests ──
    if (
        url.pathname.startsWith('/admin') ||
        url.pathname.includes('/live') ||
        url.pathname.includes('/poll') ||
        url.pathname.includes('/events') ||
        url.pathname.includes('/webrtc') ||
        url.pathname.includes('/debugger')
    ) {
        return; // Always bypass SW completely and go straight to network
    }

    // Skip non-GET requests (POST, PUT, DELETE etc. go straight to network)
    if (request.method !== 'GET') return;

    // Skip cross-origin requests (CDN, APIs, etc.)
    if (url.origin !== location.origin) return;

    // Skip Vite HMR & development endpoints
    if (url.pathname.startsWith('/@') || url.pathname.startsWith('/vite')) return;

    // ── CRITICAL: Never cache API / JSON / XHR / poll / admin dynamic routes ──
    // These include live monitor polls, WebRTC signals, AJAX endpoints.
    const isApiRequest =
        url.pathname.includes('/poll') ||
        url.pathname.includes('/signals') ||
        url.pathname.includes('/signal') ||
        url.pathname.includes('/live/') ||
        url.pathname.includes('/debugger') ||
        url.pathname.includes('/api/') ||
        url.pathname.startsWith('/admin/') ||
        request.headers.get('accept')?.includes('application/json') ||
        request.headers.get('x-requested-with') === 'XMLHttpRequest';

    if (isApiRequest) {
        // Network-only for all admin/API/AJAX/JSON requests — never cache these
        return;
    }

    // Strategy: Network-first for HTML pages, Cache-first for static assets only
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
                        return caches.match('/');
                    });
                })
        );
    } else {
        // Cache-first ONLY for static assets (CSS, JS, fonts, images)
        // Only cache files with a known static extension
        const isStaticAsset = /\.(css|js|woff2?|ttf|eot|png|jpg|jpeg|gif|svg|ico|webp)$/i.test(url.pathname);

        if (!isStaticAsset) {
            // Unknown type — use network-only to be safe
            return;
        }

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
                }).catch(() => {
                    return new Response('', { status: 404, statusText: 'Not Found' });
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
