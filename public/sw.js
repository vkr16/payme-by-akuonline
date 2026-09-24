const CACHE_NAME = 'payme-cache-v1';
const STATIC_ASSETS = [
    '/vendor/fontawesome/css/all.css',
    '/images/scan-qr-code.svg',
    '/images/qris-saya.svg',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/manifest.webmanifest'
];

// Install Event: Pre-cache core shell assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate Event: Cleanup stale caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch Event: Network-first for dynamic navigation, Cache-first for static icons & vendor css
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Only handle GET requests and skip browser extensions or analytics
    if (event.request.method !== 'GET' || !url.protocol.startsWith('http')) {
        return;
    }

    // Static assets (FontAwesome, images, icons): Cache First, fallback to Network
    if (
        url.pathname.startsWith('/vendor/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.webmanifest')
    ) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // Dynamic pages & API (HTML navigation, dynamic data): Network First, fallback to cache
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});
