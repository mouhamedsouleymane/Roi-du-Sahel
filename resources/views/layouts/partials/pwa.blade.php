{{-- PWA — Les Rois du Sahel : manifest, icônes, service worker + bannière d’installation.
     Inclus dans chaque <head> (app, guest, login, welcome) pour une installabilité à 100 %. --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#4c1d95">
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="Les Rois du Sahel">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Rois du Sahel">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon-180x180.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32.png') }}">
<script src="{{ asset('js/pwa-install.js') }}" defer></script>
