{{-- Mobile Sidebar - navigation drawer for small screens --}}

{{-- Header --}}
<div class="px-4 py-4 bg-purple-950/90 border-b border-purple-800/50 flex items-center justify-between flex-shrink-0">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0" @click="sidebarOpen = false">
        <div class="w-10 h-10 rounded-full overflow-hidden bg-white shadow-md ring-2 ring-amber-400/60 flex-shrink-0">
            <img src="{{ asset('images/logo_roi.jpeg') }}" alt="Logo CSP Les Rois du Sahel"
                class="w-full h-full object-cover">
        </div>
        <div class="truncate">
            <h1 class="font-extrabold text-base tracking-wide text-white leading-tight truncate">ROIS DU SAHEL</h1>
            <span class="text-[10px] text-amber-400 font-extrabold tracking-widest uppercase block truncate">Complexe
                Scolaire Privé</span>
        </div>
    </a>
    {{-- Close Button --}}
    <button @click="sidebarOpen = false"
        class="p-2 rounded-xl bg-purple-900/80 hover:bg-amber-400 hover:text-purple-950 text-amber-300 transition flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

{{-- Motto --}}
<div class="mx-4 mt-3 px-2.5 py-1 rounded-lg bg-amber-400/10 border border-amber-400/30 text-center flex-shrink-0">
    <span class="text-[9px] font-extrabold tracking-widest text-amber-300 uppercase">
        DISCIPLINE • TRAVAIL • SUCCÈS
    </span>
</div>

{{-- Navigation --}}
<nav class="p-3 space-y-4 text-xs font-semibold overflow-y-auto flex-1 pb-8" @click="sidebarOpen = false">

    {{-- Vue d'ensemble --}}
    <div>
        <div class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
            Vue d'ensemble
        </div>
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">📊</span>
                <span class="truncate">Tableau de Bord</span>
            </a>
            <a href="{{ route('stats.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('stats.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">📈</span>
                <span class="truncate">Statistiques</span>
            </a>
        </div>
    </div>

    {{-- Structure Scolaire --}}
    <div>
        <div class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
            Structure Scolaire
        </div>
        <div class="space-y-1">
            <a href="{{ route('academic-years.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('academic-years.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">📅</span>
                <span class="truncate">Années Scolaires</span>
            </a>
            <a href="{{ route('cycles.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('cycles.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">🏫</span>
                <span class="truncate">Cycles & Niveaux</span>
            </a>
            <a href="{{ route('classes.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('classes.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">🚪</span>
                <span class="truncate">Classes</span>
            </a>
            <a href="{{ route('subjects.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('subjects.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">📚</span>
                <span class="truncate">Matières</span>
            </a>
        </div>
    </div>

    {{-- Élèves & Acteurs --}}
    <div>
        <div class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
            Élèves & Acteurs
        </div>
        <div class="space-y-1">
            <a href="{{ route('students.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('students.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">🎓</span>
                <span class="truncate">Élèves</span>
            </a>
            <a href="{{ route('enrollments.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('enrollments.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">📋</span>
                <span class="truncate">Inscriptions</span>
            </a>
            <a href="{{ route('pre-enrollments.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('pre-enrollments.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">🌐</span>
                <span class="truncate">Pré-inscriptions Web</span>
            </a>
            <a href="{{ route('guardians.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('guardians.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">👨‍👩‍👧</span>
                <span class="truncate">Parents & Tuteurs</span>
            </a>
            <a href="{{ route('teachers.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('teachers.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">👨‍🏫</span>
                <span class="truncate">Enseignants</span>
            </a>
        </div>
    </div>

    {{-- Pédagogie & Résultats --}}
    <div>
        <div class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
            Pédagogie & Résultats
        </div>
        <div class="space-y-1">
            <a href="{{ route('assignments.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('assignments.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">🔗</span>
                <span class="truncate">Affectations Cours</span>
            </a>
            <a href="{{ route('schedules.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('schedules.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">⏱️</span>
                <span class="truncate">Emploi du Temps</span>
            </a>
            <a href="{{ route('evaluations.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('evaluations.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">📝</span>
                <span class="truncate">Évaluations & Notes</span>
            </a>
            <a href="{{ route('report-cards.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('report-cards.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">📄</span>
                <span class="truncate">Bulletins de Notes</span>
            </a>
            <a href="{{ route('attendances.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('attendances.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">✔️</span>
                <span class="truncate">Présences & Absences</span>
            </a>
        </div>
    </div>

    {{-- Comptabilité --}}
    <div>
        <div class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
            Comptabilité
        </div>
        <div class="space-y-1">
            <a href="{{ route('invoices.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('invoices.*') || request()->routeIs('payments.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">💳</span>
                <span class="truncate">Factures & Paiements</span>
            </a>
            <a href="{{ route('expenses.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">💸</span>
                <span class="truncate">Dépenses & Sorties</span>
            </a>
        </div>
    </div>

    {{-- Portails --}}
    <div>
        <div class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
            Portails Spécifiques
        </div>
        <div class="space-y-1">
            <a href="{{ route('portal.parent') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('portal.parent') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">👨‍👩‍👧‍👦</span>
                <span class="truncate">Espace Parent</span>
            </a>
            <a href="{{ route('portal.student') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('portal.student') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">🎓</span>
                <span class="truncate">Espace Élève</span>
            </a>
        </div>
    </div>

    {{-- Administration --}}
    <div>
        <div class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
            Administration
        </div>
        <div class="space-y-1">
            <a href="{{ route('users.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">👥</span>
                <span class="truncate">Utilisateurs</span>
            </a>
            <a href="{{ route('roles.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('roles.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">🛡️</span>
                <span class="truncate">Rôles & Habilitations</span>
            </a>
            <a href="{{ route('settings.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('settings.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                <span class="text-lg">⚙️</span>
                <span class="truncate">Paramètres</span>
            </a>
        </div>
    </div>

</nav>

{{-- Footer utilisateur --}}
<div class="p-4 bg-purple-950/80 border-t border-purple-800/60 flex items-center justify-between gap-3 flex-shrink-0">
    <div class="flex items-center gap-3 overflow-hidden">
        <div
            class="w-9 h-9 rounded-full bg-gradient-to-tr from-amber-400 to-purple-600 text-white font-extrabold flex items-center justify-center text-xs shadow-md flex-shrink-0 ring-2 ring-amber-400/30">
            {{ mb_substr(Auth::user()->name ?? 'U', 0, 1) }}
        </div>
        <div class="truncate">
            <div class="text-xs font-bold text-white truncate">{{ Auth::user()->name ?? 'Utilisateur' }}</div>
            <div class="text-[10px] text-amber-300/80 truncate">{{ Auth::user()->email ?? '' }}</div>
        </div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" title="Déconnexion"
            class="p-2 text-purple-300 hover:text-amber-400 transition-colors flex-shrink-0">
            🚪
        </button>
    </form>
</div>
