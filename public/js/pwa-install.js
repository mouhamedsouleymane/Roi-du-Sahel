(function () {
    'use strict';

    // Service worker: HTTPS ou localhost uniquement.
    if ('serviceWorker' in navigator && (location.protocol === 'https:' || ['localhost', '127.0.0.1'].indexOf(location.hostname) !== -1)) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () {});
        });
    }

    var SNOOZE_KEY = 'rds-pwa-install-dismissed-at';
    var SNOOZE_MS = 7 * 24 * 60 * 60 * 1000;
    var deferredPrompt = null;

    function isStandalone() {
        return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
            || window.navigator.standalone === true
            || (document.referrer && document.referrer.indexOf('android-app://') === 0);
    }

    function isSnoozed() {
        var at = parseInt(localStorage.getItem(SNOOZE_KEY) || '0', 10);
        return Date.now() - at < SNOOZE_MS;
    }

    function isIos() {
        return /iphone|ipad|ipod/i.test(navigator.userAgent) && !('BeforeInstallPromptEvent' in window);
    }

    function banner() {
        return document.getElementById('pwa-install-banner');
    }

    function showBanner() {
        var el = banner();
        if (!el || isStandalone() || isSnoozed()) {
            return;
        }
        if (isIos()) {
            var androidInfo = el.querySelector('[data-pwa-android]');
            if (androidInfo) {
                androidInfo.hidden = true;
            }
            var iosInfo = el.querySelector('[data-pwa-ios]');
            if (iosInfo) {
                iosInfo.hidden = false;
            }
            var installBtn = el.querySelector('[data-pwa-install]');
            if (installBtn) {
                installBtn.hidden = true;
            }
        }
        el.style.display = 'flex';
    }

    function hideBanner(snooze) {
        var el = banner();
        if (el) {
            el.style.display = 'none';
        }
        if (snooze) {
            try {
                localStorage.setItem(SNOOZE_KEY, String(Date.now()));
            } catch (err) {
                // Ignore localStorage errors
            }
        }
    }

    // Diagnostic : ?pwa-debug=1 force l'affichage (hors snooze) pour vérifier
    // que la bannière et le JS sont bien déployés, sans attendre le
    // beforeinstallprompt du navigateur.
    function showBannerDebug() {
        try {
            localStorage.removeItem(SNOOZE_KEY);
        } catch (err) {
            // Ignore localStorage errors
        }
        showBanner();
    }

    try {
        if (/(?:\?|&)pwa-debug=1/.test(location.search)) {
            window.addEventListener('load', function () {
                setTimeout(showBannerDebug, 500);
            });
        }
    } catch (err) {
        // Ignore location errors
    }

    // Chrome/Edge: propose l installation des que le navigateur l autorise.
    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;
        showBanner();
    });

    // Safari iOS: afficher le mode d emploi apres un court delai.
    if (isIos()) {
        window.addEventListener('load', function () {
            setTimeout(showBanner, 2500);
        });
    }

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        hideBanner(false);
    });

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!target) {
            return;
        }
        var installBtn = target.closest ? target.closest('[data-pwa-install]') : null;
        if (installBtn) {
            if (!deferredPrompt) {
                return;
            }
            deferredPrompt.prompt();
            if (deferredPrompt.userChoice && typeof deferredPrompt.userChoice.then === 'function') {
                deferredPrompt.userChoice.then(function () {
                    deferredPrompt = null;
                    hideBanner(true);
                }).catch(function () {
                    deferredPrompt = null;
                    hideBanner(true);
                });
            } else {
                deferredPrompt = null;
                hideBanner(true);
            }
            return;
        }
        var dismissBtn = target.closest ? target.closest('[data-pwa-dismiss]') : null;
        if (dismissBtn) {
            hideBanner(true);
        }
    });
})();
