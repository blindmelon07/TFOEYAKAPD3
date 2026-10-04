/*
 * District III service worker.
 *
 * Caches only the app shell (hashed build assets, fonts, icons) so the
 * installed app starts fast. Pages and Inertia responses always come from the
 * network because they contain private member data; when the network is
 * unavailable, page navigations fall back to /offline.html.
 *
 * Bump CACHE_VERSION to discard old caches after changing this file.
 */
const CACHE_VERSION = 'district3-v2';
const SHELL_CACHE = `${CACHE_VERSION}-shell`;
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/manifest.json',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(SHELL_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => !key.startsWith(CACHE_VERSION))
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

/**
 * Serve from cache when possible, otherwise fetch and remember the response.
 */
async function cacheFirst(request) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);

    if (response.ok) {
        const cache = await caches.open(SHELL_CACHE);
        cache.put(request, response.clone());
    }

    return response;
}

/**
 * Always try the network for pages; show the offline page if it fails.
 */
async function networkWithOfflineFallback(request) {
    try {
        return await fetch(request);
    } catch {
        return (await caches.match(OFFLINE_URL)) ?? Response.error();
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Inertia page visits carry private data: never cache them.
    if (request.headers.has('X-Inertia')) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkWithOfflineFallback(request));

        return;
    }

    // Vite build output is content-hashed, so cached copies never go stale.
    if (url.pathname.startsWith('/build/assets/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(cacheFirst(request));
    }
});
