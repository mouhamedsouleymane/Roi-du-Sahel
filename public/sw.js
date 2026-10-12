/* Service Worker — Les Rois du Sahel (PWA).
 *
 * Stratégie volontairement prudente pour une application authentifiée :
 * - navigations : réseau d'abord, repli sur la page /offline (puis cache) ;
 * - assets statiques (build, images, icônes, polices) : cache d'abord, réactualisé en arrière-plan ;
 * - tout le reste (POST, API/XHR, origines tierces) : jamais intercepté.
 *
 * Incrémenter PWA_CACHE_VERSION à chaque déploiement modifiant le shell statique.
 */
const PWA_CACHE_VERSION = 'rds-pwa-v2';
const STATIC_CACHE = 'rds-static-' + PWA_CACHE_VERSION;
const OFFLINE_URL = '/offline';
const PRECACHE_URLS = [OFFLINE_URL, '/images/logo_roi.jpeg', '/icons/icon-192x192.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(STATIC_CACHE)
            // Mise en cache URL par URL : un fichier manquant ou une page de
            // challenge anti-bot ne doit jamais faire échouer l'installation.
            .then((cache) => Promise.all(PRECACHE_URLS.map((url) => cache.add(url).catch(() => {}))))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== STATIC_CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

function isStaticAsset(url) {
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') || url.pathname.startsWith('/images/')) {
        return true;
    }

    return /\.(?:css|js|png|jpe?g|webp|svg|ico|woff2?|ttf|eot)$/i.test(url.pathname);
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    // Navigations : réseau d'abord pour ne jamais servir de page authentifiée périmée.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy)).catch(() => {});
                    return response;
                })
                .catch(() => caches.match(request).then((hit) => hit || caches.match(OFFLINE_URL)))
        );
        return;
    }

    // Assets statiques : réponse immédiate depuis le cache, réactualisation en arrière-plan.
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(request).then((hit) => {
                const network = fetch(request)
                    .then((response) => {
                        if (response && response.ok) {
                            const copy = response.clone();
                            caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy)).catch(() => {});
                        }
                        return response;
                    })
                    .catch(() => hit);
                return hit || network;
            })
        );
    }
});
