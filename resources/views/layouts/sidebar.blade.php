<aside :class="sidebarCollapsed ? 'w-16' : 'w-64'"
    class="bg-gradient-to-b from-purple-950 via-purple-900 to-slate-950 text-slate-100 fixed inset-y-0 left-0 h-screen hidden md:flex md:flex-col justify-between border-r border-purple-800/40 shadow-2xl z-30 transition-all duration-300 ease-in-out">
    <div class="flex flex-col h-full overflow-hidden">

        <!-- Brand Header with Toggle Collapse Button -->
        <div
            class="px-4 py-4 bg-purple-950/90 border-b border-purple-800/50 flex flex-col gap-2 flex-shrink-0 transition-all duration-300">
            <div class="flex items-center justify-between gap-2">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                    <div
                        class="w-11 h-11 rounded-full overflow-hidden bg-white shadow-lg shadow-amber-500/20 ring-2 ring-amber-400/40 animate-float flex-shrink-0">
                        <img src="{{ asset('images/logo_roi.jpeg') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="truncate">
                        <h1 class="font-extrabold text-base tracking-wide text-white leading-tight truncate">ROIS DU
                            SAHEL</h1>
                        <span
                            class="text-[10px] text-amber-400 font-extrabold tracking-widest uppercase block truncate">Complexe
                            Scolaire Privé</span>
                    </div>
                </a>

                <!-- Sidebar Toggle Collapse Button -->
                <button @click="toggleCollapse()"
                    :title="sidebarCollapsed ? 'Agrandir la barre latérale' : 'Réduire la barre latérale en mini-barre'"
                    class="p-2 rounded-xl bg-purple-900/80 hover:bg-amber-400 hover:text-purple-950 text-amber-300 transition duration-200 flex-shrink-0 shadow-md">
                    <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- School Motto Banner (Hidden when collapsed) -->
            <div x-show="!sidebarCollapsed" x-transition
                class="mt-1 px-2.5 py-1 rounded-lg bg-amber-400/10 border border-amber-400/30 text-center">
                <span class="text-[9px] font-extrabold tracking-widest text-amber-300 uppercase">
                    DISCIPLINE • TRAVAIL • SUCCÈS
                </span>
            </div>
        </div>

        <!-- Navigation Links Grouped -->
        <nav class="p-3 space-y-4 text-xs font-semibold overflow-y-auto flex-1 scrollbar-thin">

            <!-- Vue d'ensemble -->
            <div>
                <div x-show="!sidebarCollapsed"
                    class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
                    Vue d'ensemble
                </div>
                <div x-show="sidebarCollapsed" class="my-2 border-t border-purple-800/40"></div>

                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" title="Tableau de Bord"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">📊</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Tableau de Bord</span>
                    </a>
                    <a href="{{ route('stats.index') }}" title="Statistiques & Analyses"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('stats.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">📈</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Statistiques</span>
                    </a>
                </div>
            </div>

            <!-- Structure & Organisation -->
            <div>
                <div x-show="!sidebarCollapsed"
                    class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
                    Structure Scolaire
                </div>
                <div x-show="sidebarCollapsed" class="my-2 border-t border-purple-800/40"></div>

                <div class="space-y-1">
                    <a href="{{ route('academic-years.index') }}" title="Années Scolaires"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('academic-years.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">📅</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Années Scolaires</span>
                    </a>
                    <a href="{{ route('cycles.index') }}" title="Cycles & Niveaux"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('cycles.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">🏫</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Cycles & Niveaux</span>
                    </a>
                    <a href="{{ route('classes.index') }}" title="Classes"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('classes.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">🚪</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Classes</span>
                    </a>
                    <a href="{{ route('subjects.index') }}" title="Matières Enseignées"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('subjects.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">📚</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Matières</span>
                    </a>
                </div>
            </div>

            <!-- Élèves & Personnels -->
            <div>
                <div x-show="!sidebarCollapsed"
                    class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
                    Élèves & Acteurs
                </div>
                <div x-show="sidebarCollapsed" class="my-2 border-t border-purple-800/40"></div>

                <div class="space-y-1">
                    <a href="{{ route('students.index') }}" title="Répertoire Élèves"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('students.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">🎓</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Élèves</span>
                    </a>
                    <a href="{{ route('enrollments.index') }}" title="Gestion des Inscriptions"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('enrollments.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">📋</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Inscriptions</span>
                    </a>
                    <a href="{{ route('guardians.index') }}" title="Parents & Tuteurs Légaux"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('guardians.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">👨‍👩‍👧</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Parents & Tuteurs</span>
                    </a>
                    <a href="{{ route('teachers.index') }}" title="Enseignants & Professeurs"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('teachers.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">👨‍🏫</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Enseignants</span>
                    </a>
                </div>
            </div>

            <!-- Pédagogie & Notes -->
            <div>
                <div x-show="!sidebarCollapsed"
                    class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
                    Pédagogie & Résultats
                </div>
                <div x-show="sidebarCollapsed" class="my-2 border-t border-purple-800/40"></div>

                <div class="space-y-1">
                    <a href="{{ route('assignments.index') }}" title="Affectations Cours"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('assignments.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">🔗</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Affectations Cours</span>
                    </a>
                    <a href="{{ route('schedules.index') }}" title="Emploi du Temps"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('schedules.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">⏱️</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Emploi du Temps</span>
                    </a>
                    <a href="{{ route('evaluations.index') }}" title="Évaluations & Notes"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('evaluations.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">📝</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Évaluations & Notes</span>
                    </a>
                    <a href="{{ route('report-cards.index') }}" title="Bulletins de Notes"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('report-cards.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">📄</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Bulletins de Notes</span>
                    </a>
                    <a href="{{ route('attendances.index') }}" title="Présences & Absences"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('attendances.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">✔️</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Présences & Absences</span>
                    </a>
                </div>
            </div>

            <!-- Finance -->
            <div>
                <div x-show="!sidebarCollapsed"
                    class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
                    Comptabilité
                </div>
                <div x-show="sidebarCollapsed" class="my-2 border-t border-purple-800/40"></div>

                <div class="space-y-1">
                    <a href="{{ route('invoices.index') }}" title="Factures & Paiements"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('invoices.*') || request()->routeIs('payments.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">💳</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Factures & Paiements</span>
                    </a>
                </div>
            </div>

            <!-- Portails -->
            <div>
                <div x-show="!sidebarCollapsed"
                    class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
                    Portails Spécifiques
                </div>
                <div x-show="sidebarCollapsed" class="my-2 border-t border-purple-800/40"></div>

                <div class="space-y-1">
                    <a href="{{ route('portal.parent') }}" title="Espace Parent"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('portal.parent') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">👨‍👩‍👧‍👦</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Espace Parent</span>
                    </a>
                    <a href="{{ route('portal.student') }}" title="Espace Élève"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('portal.student') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">🎓</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Espace Élève</span>
                    </a>
                </div>
            </div>

            <!-- Système -->
            <div>
                <div x-show="!sidebarCollapsed"
                    class="px-3 mb-1.5 text-[10px] font-extrabold tracking-widest text-amber-400/80 uppercase">
                    Administration
                </div>
                <div x-show="sidebarCollapsed" class="my-2 border-t border-purple-800/40"></div>

                <div class="space-y-1">
                    <a href="{{ route('users.index') }}" title="Utilisateurs & Rôles"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('users.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">🛡️</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Utilisateurs & Rôles</span>
                    </a>
                    <a href="{{ route('settings.index') }}" title="Paramètres Établissement"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : 'gap-3 px-3 py-2.5'"
                        class="flex items-center rounded-xl transition-all duration-300 group {{ request()->routeIs('settings.*') ? 'bg-gradient-to-r from-amber-500 via-purple-700 to-purple-800 text-white font-extrabold shadow-lg ring-1 ring-amber-400/50' : 'text-purple-100/80 hover:bg-white/10 hover:text-white' }}">
                        <span class="text-lg transition-transform group-hover:scale-110">⚙️</span>
                        <span x-show="!sidebarCollapsed" class="truncate">Paramètres</span>
                    </a>
                </div>
            </div>

        </nav>
    </div>

    <!-- User Profile Footer -->
    <div :class="sidebarCollapsed ? 'justify-center px-2 py-4' : 'justify-between p-4'"
        class="bg-purple-950 border-t border-purple-800/60 flex items-center transition-all duration-300">
        <div class="flex items-center gap-3 overflow-hidden">
            <div title="{{ Auth::user()->name ?? 'Utilisateur' }}"
                class="w-9 h-9 rounded-full bg-gradient-to-tr from-amber-400 to-purple-600 text-white font-extrabold flex items-center justify-center text-xs shadow-md flex-shrink-0 ring-2 ring-amber-400/30">
                {{ mb_substr(Auth::user()->name ?? 'U', 0, 1) }}
            </div>
            <div x-show="!sidebarCollapsed" class="truncate">
                <div class="text-xs font-bold text-white truncate">{{ Auth::user()->name ?? 'Utilisateur' }}</div>
                <div class="text-[10px] text-amber-300/80 truncate">{{ Auth::user()->email ?? '' }}</div>
            </div>
        </div>
        <form x-show="!sidebarCollapsed" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Déconnexion"
                class="p-2 text-purple-300 hover:text-amber-400 transition-colors">
                🚪
            </button>
        </form>
    </div>
</aside>
