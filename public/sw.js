const CACHE_NAME = 'barakah-wallet-v1';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/images/logo.png',
    '/images/logo-icon.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
    '/offline.html'
];

// Install Event — cache app shell assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('[Barakah SW] Pre-caching partial failure:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate Event — cleanup old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event — Network-first for dynamic navigation, Cache-first for static assets
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests and cross-origin requests unless it's static CDN
    if (request.method !== 'GET') {
        return;
    }

    // Static assets (images, fonts, build assets) -> Cache first with network fallback
    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(() => cachedResponse);
            })
        );
        return;
    }

    // HTML / Page navigations -> Network first with offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match('/offline.html').then((offlineRes) => {
                    if (offlineRes) return offlineRes;
                    return new Response(
                        `<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Offline — Barakah</title><style>body{font-family:sans-serif;background:#f8fafc;color:#0f172a;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;text-align:center}.card{background:#fff;border-radius:16px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,0.05);max-width:420px}.btn{display:inline-block;margin-top:20px;padding:10px 24px;background:#0d9488;color:#fff;border-radius:10px;text-decoration:none;font-weight:600}</style></head><body><div class="card"><div style="font-size:48px;margin-bottom:12px">📶</div><h2 style="margin:0 0 8px;font-size:20px">You are currently offline</h2><p style="color:#64748b;font-size:14px;margin:0">Please check your internet connection to access your Barakah financial wallet.</p><a href="/" class="btn" onclick="window.location.reload();return false;">Retry Connection</a></div></body></html>`,
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                });
            })
        );
    }
});

// Push Notification Event
self.addEventListener('push', (event) => {
    let data = {
        title: 'Barakah Wallet',
        body: 'New financial transaction notification.',
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/icon-72x72.png',
        url: '/transactions'
    };

    if (event.data) {
        try {
            const json = event.data.json();
            data.title = json.notification?.title || json.title || data.title;
            data.body = json.notification?.body || json.body || data.body;
            data.url = json.data?.click_action || json.data?.url || json.url || data.url;
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        vibrate: [100, 50, 100],
        data: {
            url: data.url
        },
        actions: [
            { action: 'open', title: 'View Receipt' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification Click Handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/transactions';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url.includes(targetUrl) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

