<div x-data="{
    open: false,
    title: '',
    message: '',
    confirmText: 'Confirmer',
    cancelText: 'Annuler',
    variant: 'danger',
    targetForm: null,

    init() {
        window.addEventListener('show-confirm-modal', (e) => {
            this.title = e.detail.title || 'Confirmation';
            this.message = e.detail.message || 'Êtes-vous sûr de vouloir effectuer cette action ?';
            this.confirmText = e.detail.confirmText || 'Confirmer';
            this.cancelText = e.detail.cancelText || 'Annuler';
            this.variant = e.detail.variant || 'danger';
            this.targetForm = e.detail.form || null;
            this.open = true;
        });

        // Interception globale universelle de toutes les soumissions de formulaire (Création, Modification, Suppression)
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form || form.dataset.confirmed === 'true') {
                return true;
            }

            // Exclure les formulaires de recherche / filtres (GET) et la déconnexion
            const formMethod = (form.getAttribute('method') || 'GET').toUpperCase();
            const actionUrl = form.getAttribute('action') || '';
            const isNoConfirm = form.dataset.noConfirm === 'true' || form.hasAttribute('data-no-confirm');

            if (formMethod === 'GET' || actionUrl.includes('logout') || isNoConfirm) {
                return true;
            }

            // Vérifier la méthode réelle (méthode de simulation Laravel _method)
            const methodInput = form.querySelector('input[name=\'_method\']');
            const realMethod = (methodInput ? methodInput.value : formMethod).toUpperCase();

            // Attributs de confirmation personnalisés si présents
            const dataConfirm = form.dataset.confirm || form.getAttribute('data-confirm');
            const submitBtn = e.submitter;
            const btnConfirm = submitBtn ? (submitBtn.dataset.confirm || submitBtn.getAttribute('data-confirm')) : null;

            e.preventDefault();
            e.stopPropagation();

            let title = 'Confirmation requise';
            let message = 'Êtes-vous sûr de vouloir valider cette opération ?';
            let confirmText = 'Oui, confirmer';
            let variant = 'primary';

            if (realMethod === 'DELETE') {
                title = '🗑️ Confirmation de Suppression';
                message = dataConfirm || btnConfirm || 'Êtes-vous absolument sûr de vouloir supprimer cet élément ? Cette action est irréversible et supprimera définitivement les données associées.';
                confirmText = 'Oui, supprimer définitivement';
                variant = 'danger';
            } else if (realMethod === 'PUT' || realMethod === 'PATCH') {
                title = '✏️ Confirmation de Modification';
                message = dataConfirm || btnConfirm || 'Voulez-vous enregistrer les modifications apportées à cet élément ?';
                confirmText = 'Oui, enregistrer les modifications';
                variant = 'warning';
            } else {
                // Création (POST)
                title = '➕ Confirmation de Création';
                message = dataConfirm || btnConfirm || 'Êtes-vous sûr de vouloir procéder à l\'enregistrement de cette nouvelle information ?';
                confirmText = 'Oui, procéder à la création';
                variant = 'primary';
            }

            this.title = title;
            this.message = message;
            this.confirmText = confirmText;
            this.variant = variant;
            this.targetForm = form;
            this.open = true;
        }, true);
    },

    confirm() {
        this.open = false;
        if (this.targetForm) {
            this.targetForm.dataset.confirmed = 'true';
            this.targetForm.submit();
        }
    }
}" 
x-show="open" 
x-cloak
class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4 sm:p-6" 
style="display: none;">

    <!-- Overlay backdrop -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false" 
         class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-40"></div>

    <!-- Modal Box (Strictement centrée verticalement & horizontalement) -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative z-50 w-full max-w-md my-auto transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-100">
        
        <!-- Header with colored accent -->
        <div :class="{
            'bg-red-50 border-b border-red-100 text-red-950': variant === 'danger',
            'bg-amber-50 border-b border-amber-100 text-amber-950': variant === 'warning',
            'bg-purple-50 border-b border-purple-100 text-purple-950': variant === 'primary'
        }" class="px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-black flex items-center gap-2" x-text="title"></h3>
            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition">
                ✕
            </button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <p class="text-sm font-medium text-slate-600 leading-relaxed" x-text="message"></p>
        </div>

        <!-- Footer Actions -->
        <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <button type="button" @click="open = false"
                    class="px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-100 transition shadow-xs">
                Annuler
            </button>
            <button type="button" @click="confirm()"
                    :class="{
                        'bg-red-600 hover:bg-red-700 text-white shadow-red-600/20': variant === 'danger',
                        'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-600/20': variant === 'warning',
                        'bg-purple-900 hover:bg-purple-950 text-white shadow-purple-900/20': variant === 'primary'
                    }"
                    class="px-5 py-2 text-xs font-black rounded-xl shadow-md transition flex items-center gap-1.5" x-text="confirmText">
            </button>
        </div>
    </div>
</div>
