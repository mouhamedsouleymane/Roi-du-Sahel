{{-- PWA — Bannière de proposition d’installation (affichée automatiquement par public/js/pwa-install.js).
     Masquée par défaut (display:none) : le JS passe en display:flex quand le navigateur autorise l’installation. --}}
<div id="pwa-install-banner" style="display: none;"
    class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-6 sm:bottom-6 sm:max-w-sm z-[100] bg-purple-950 text-white rounded-2xl shadow-2xl border border-amber-400/40 p-4 items-start gap-3">
    <img src="{{ asset('icons/icon-192x192.png') }}" alt="Les Rois du Sahel" class="w-12 h-12 rounded-xl object-cover bg-white flex-shrink-0">
    <div class="flex-1 min-w-0">
        <p class="text-sm font-black">📲 Installer l’application</p>
        <p class="text-xs text-purple-200 font-semibold mt-0.5" data-pwa-android>
            Accédez aux Rois du Sahel en 1 clic, même hors connexion.
        </p>
        <p class="text-xs text-purple-200 font-semibold mt-0.5" data-pwa-ios hidden>
            Sur iPhone : touchez <span class="font-black text-amber-300">Partager ⬆️</span> puis
            <span class="font-black text-amber-300">« Sur l’écran d’accueil »</span>.
        </p>
        <div class="flex items-center gap-2 mt-2.5">
            <button type="button" data-pwa-install
                class="px-3 py-1.5 text-xs font-black bg-amber-400 text-purple-950 rounded-xl hover:bg-amber-300 transition">
                Installer
            </button>
            <button type="button" data-pwa-dismiss
                class="px-3 py-1.5 text-xs font-bold text-purple-200 hover:text-white transition">
                Plus tard
            </button>
        </div>
    </div>
</div>
