<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-purple-700 font-bold uppercase tracking-wider mb-1">
                    <a href="{{ route('invoices.index') }}" class="hover:underline flex items-center gap-1">
                        💳 Facturation & Comptabilité
                    </a>
                    <span>/</span>
                    <span class="text-slate-500">Paramètres</span>
                </div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    ⚙️ Paramètres des Frais & Grille Tarifaire
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Gérez la nomenclature des frais scolaires, les tarifs par niveau/cycle et la génération en masse pour <span class="font-bold text-purple-900">{{ $activeYear?->name ?? 'Aucune année active' }}</span>.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('invoices.index') }}"
                   class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-xs">
                    ← Retour aux Factures
                </a>
                <a href="{{ route('invoices.create') }}"
                   class="px-3.5 py-2 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-purple-950 font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5">
                    ➕ Émettre une Facture
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen bg-slate-50" x-data="{
        activeTab: 'tarifs',
        editTypeModalOpen: false,
        editStructureModalOpen: false,
        selectedType: { id: '', code: '', name: '', description: '', is_recurring: false, is_active: true },
        selectedStructure: { id: '', fee_type_id: '', cycle_id: '', level_id: '', amount: '' },
        allLevels: {{ Js::from($levels) }},
        selectedFormCycleId: '',
        editFormCycleId: '',

        openEditType(type) {
            this.selectedType = {
                id: type.id,
                code: type.code,
                name: type.name,
                description: type.description || '',
                is_recurring: Boolean(type.is_recurring),
                is_active: Boolean(type.is_active)
            };
            this.editTypeModalOpen = true;
        },

        openEditStructure(struct) {
            this.selectedStructure = {
                id: struct.id,
                fee_type_id: struct.fee_type_id,
                cycle_id: struct.cycle_id ? String(struct.cycle_id) : '',
                level_id: struct.level_id ? String(struct.level_id) : '',
                amount: Math.round(struct.amount)
            };
            this.editFormCycleId = struct.cycle_id ? String(struct.cycle_id) : '';
            this.editStructureModalOpen = true;
        }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('status'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 p-4 rounded-xl shadow-xs flex items-center justify-between">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <span>✅</span>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-900 p-4 rounded-xl shadow-xs">
                    <p class="font-black text-xs uppercase tracking-wider mb-1">⚠️ Des erreurs ont été détectées :</p>
                    <ul class="list-disc list-inside text-xs font-semibold space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Stat KPI Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center font-black text-lg">
                        🏷️
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Types de Frais</p>
                        <p class="text-xl font-black text-purple-950">{{ $totalFeeTypesCount }} <span class="text-xs font-bold text-emerald-600">({{ $activeFeeTypesCount }} actifs)</span></p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center font-black text-lg">
                        💰
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tarifs Grille</p>
                        <p class="text-xl font-black text-slate-900">{{ $totalStructuresCount }} <span class="text-xs font-normal text-slate-400">lignes</span></p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center font-black text-lg">
                        🏫
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cycles & Niveaux</p>
                        <p class="text-xl font-black text-slate-900">{{ $cycles->count() }} <span class="text-xs font-bold text-slate-500">cycles / {{ $levels->count() }} niv.</span></p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-900 flex items-center justify-center font-black text-lg">
                        👥
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Élèves Inscrits</p>
                        <p class="text-xl font-black text-emerald-700">{{ $activeStudentsCount }} <span class="text-xs font-bold text-slate-500">actifs</span></p>
                    </div>
                </div>
            </div>

            {{-- Tabs Navigation --}}
            <div class="flex border-b border-slate-200 gap-2">
                <button @click="activeTab = 'tarifs'"
                        :class="activeTab === 'tarifs' ? 'border-purple-900 text-purple-950 font-black bg-white shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold'"
                        class="px-5 py-3 border-b-2 rounded-t-xl text-xs uppercase tracking-wider transition flex items-center gap-2">
                    <span>💰</span>
                    <span>Grille Tarifaire ({{ $structures->count() }})</span>
                </button>
                <button @click="activeTab = 'types'"
                        :class="activeTab === 'types' ? 'border-purple-900 text-purple-950 font-black bg-white shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold'"
                        class="px-5 py-3 border-b-2 rounded-t-xl text-xs uppercase tracking-wider transition flex items-center gap-2">
                    <span>🏷️</span>
                    <span>Types de Frais ({{ $feeTypes->count() }})</span>
                </button>
                <button @click="activeTab = 'generation'"
                        :class="activeTab === 'generation' ? 'border-purple-900 text-purple-950 font-black bg-white shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold'"
                        class="px-5 py-3 border-b-2 rounded-t-xl text-xs uppercase tracking-wider transition flex items-center gap-2">
                    <span>⚡</span>
                    <span>Facturation en Masse</span>
                </button>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 1: GRILLE TARIFAIRE (FEE STRUCTURES)                  --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'tarifs'" x-transition class="space-y-6">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Form: Ajouter un tarif --}}
                    <div class="lg:col-span-1 bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                            <span class="p-1.5 rounded-lg bg-amber-100 text-amber-900 text-sm">💰</span>
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-wide">
                                Ajouter / Fixer un Tarif
                            </h3>
                        </div>

                        <form method="POST" action="{{ route('invoices.fee-structures.store') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    Type de Frais <span class="text-red-500">*</span>
                                </label>
                                <select name="fee_type_id" required
                                        class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">
                                    <option value="">-- Choisir le type de frais --</option>
                                    @foreach ($feeTypes as $ft)
                                        <option value="{{ $ft->id }}">
                                            [{{ $ft->code }}] {{ $ft->name }} {{ $ft->is_recurring ? '(Récurrent)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    Cycle (Optionnel)
                                </label>
                                <select name="cycle_id" x-model="selectedFormCycleId"
                                        class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">
                                    <option value="">— Tous les cycles (Tarif global) —</option>
                                    @foreach ($cycles as $cycle)
                                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-slate-400 mt-1">S'applique à tout le cycle si aucun niveau n'est choisi.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    Niveau Spécifique (Optionnel)
                                </label>
                                <select name="level_id"
                                        class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">
                                    <option value="">— Tous les niveaux du cycle —</option>
                                    <template x-for="lvl in allLevels.filter(l => !selectedFormCycleId || String(l.cycle_id) === String(selectedFormCycleId))" :key="lvl.id">
                                        <option :value="lvl.id" x-text="(lvl.cycle ? lvl.cycle.name + ' — ' : '') + lvl.name"></option>
                                    </template>
                                </select>
                                <p class="text-[10px] text-slate-400 mt-1">Prévaut sur le tarif général du cycle.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    Montant (FCFA) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="amount" min="1" step="1" required placeholder="Ex: 50000"
                                           class="w-full text-xs font-black rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs pr-14" />
                                    <span class="absolute right-3 top-2.5 text-[10px] font-black text-slate-400 pointer-events-none">FCFA</span>
                                </div>
                            </div>

                            <button type="submit"
                                    class="w-full py-2.5 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                                💾 Enregistrer le Tarif
                            </button>
                        </form>
                    </div>

                    {{-- Affichage: Grille Tarifaire Active Regroupée par Type de Frais --}}
                    <div class="lg:col-span-2 space-y-5">
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100">
                                <div>
                                    <h3 class="text-sm font-black text-purple-950 uppercase tracking-wide flex items-center gap-2">
                                        <span>💰</span> Grille Tarifaire Active par Type de Frais
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5">Année scolaire : <strong class="text-purple-950">{{ $activeYear?->name ?? 'Non définie' }}</strong> • {{ $structures->count() }} tarif(s) au total</p>
                                </div>

                                {{-- Filters --}}
                                <form method="GET" action="{{ route('invoices.settings') }}" class="flex flex-wrap items-center gap-2">
                                    <select name="fee_type_id" onchange="this.form.submit()"
                                            class="text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 py-1.5 shadow-2xs">
                                        <option value="">Tous les frais</option>
                                        @foreach ($feeTypes as $ft)
                                            <option value="{{ $ft->id }}" {{ request('fee_type_id') == $ft->id ? 'selected' : '' }}>
                                                {{ $ft->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select name="cycle_id" onchange="this.form.submit()"
                                            class="text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 py-1.5 shadow-2xs">
                                        <option value="">Tous les cycles</option>
                                        @foreach ($cycles as $c)
                                            <option value="{{ $c->id }}" {{ request('cycle_id') == $c->id ? 'selected' : '' }}>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if (request('fee_type_id') || request('cycle_id'))
                                        <a href="{{ route('invoices.settings') }}" class="text-xs font-black text-purple-900 hover:text-amber-600 underline ml-1">
                                            Réinitialiser
                                        </a>
                                    @endif
                                </form>
                            </div>
                        </div>

                        {{-- Blocs par Type de Frais --}}
                        @forelse ($structuresByFeeType as $feeTypeId => $cycleGroups)
                            @php
                                $currentFeeType = $cycleGroups->flatten()->first()?->feeType ?? $feeTypes->firstWhere('id', $feeTypeId);
                                $totalCount = $cycleGroups->flatten()->count();
                            @endphp
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                                {{-- En-tête du Type de Frais --}}
                                <div class="px-5 py-4 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-9 h-9 rounded-xl bg-amber-400 text-purple-950 font-black text-base flex items-center justify-center shadow-xs shrink-0">
                                            🏷️
                                        </span>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-sm font-black text-white leading-tight">
                                                    {{ $currentFeeType?->name ?? 'Frais' }}
                                                </h4>
                                                <span class="px-2 py-0.5 rounded-md bg-white/10 border border-white/20 font-mono text-[10px] font-black text-amber-300">
                                                    {{ $currentFeeType?->code }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-1 text-[11px] text-purple-200">
                                                @if ($currentFeeType?->is_recurring)
                                                    <span class="inline-flex items-center gap-1 text-emerald-300 font-bold">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Récurrent (Mensuel / Échéance)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-amber-300 font-bold">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Ponctuel (Paiement unique)
                                                    </span>
                                                @endif
                                                @if ($currentFeeType?->description)
                                                    <span class="text-purple-300">• {{ $currentFeeType->description }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 bg-white/10 backdrop-blur-xs text-amber-300 text-xs font-black rounded-full border border-white/20 self-start sm:self-auto">
                                        {{ $totalCount }} tarif(s) configuré(s)
                                    </span>
                                </div>

                                {{-- Sous-blocs par Cycle --}}
                                @foreach ($cycleGroups as $cycleId => $cycleStructures)
                                    @php $currentCycle = $cycleStructures->first()?->cycle; @endphp

                                    {{-- Bandeau Cycle --}}
                                    <div class="px-5 py-2.5 bg-blue-50 border-b border-blue-100 flex items-center gap-2">
                                        @if ($currentCycle)
                                            <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                            <span class="text-xs font-black text-blue-900 uppercase tracking-wider">{{ $currentCycle->name }}</span>
                                            <span class="text-[10px] text-blue-500 font-bold">({{ $cycleStructures->count() }} tarif(s))</span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0"></span>
                                            <span class="text-xs font-black text-slate-500 italic">— Tarif global (tous cycles) —</span>
                                        @endif
                                    </div>

                                    {{-- Table des niveaux pour ce cycle --}}
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead>
                                                <tr class="bg-slate-50 text-slate-500 font-black uppercase text-[10px] tracking-wider border-b border-slate-200">
                                                    <th class="py-2.5 px-5">Niveau d'Étude</th>
                                                    <th class="py-2.5 px-5 text-right">Montant Tarifaire</th>
                                                    <th class="py-2.5 px-5 text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 font-medium text-slate-800 bg-white">
                                                @foreach ($cycleStructures as $s)
                                                    <tr class="hover:bg-purple-50/25 transition">
                                                        {{-- Niveau --}}
                                                        <td class="py-3 px-5 whitespace-nowrap">
                                                            @if ($s->level)
                                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 text-purple-900 border border-purple-200 rounded-full font-extrabold text-[11px]">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                                                    {{ $s->level->name }}
                                                                </span>
                                                            @else
                                                                <span class="text-slate-400 italic text-[11px] font-bold">— Tous les niveaux du cycle —</span>
                                                            @endif
                                                        </td>

                                                        {{-- Montant --}}
                                                        <td class="py-3 px-5 text-right whitespace-nowrap font-black text-emerald-700 text-sm">
                                                            {{ number_format($s->amount, 0, ',', ' ') }} <span class="text-[10px] text-slate-400 font-bold">FCFA</span>
                                                        </td>

                                                        {{-- Actions --}}
                                                        <td class="py-3 px-5 text-center whitespace-nowrap">
                                                            <div class="flex items-center justify-center gap-1.5">
                                                                <button type="button" @click="openEditStructure({{ Js::from($s) }})"
                                                                        title="Modifier le tarif"
                                                                        class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg transition font-bold text-xs shadow-2xs">
                                                                    ✏️
                                                                </button>
                                                                <form method="POST" action="{{ route('invoices.fee-structures.destroy', $s) }}"
                                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce tarif ?');"
                                                                      class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" title="Supprimer le tarif"
                                                                            class="p-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg transition font-bold text-xs shadow-2xs">
                                                                        🗑️
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <div class="bg-white rounded-2xl p-10 text-center border border-slate-200 shadow-sm space-y-3">
                                <div class="w-12 h-12 bg-purple-50 text-purple-900 rounded-full flex items-center justify-center text-2xl mx-auto">
                                    💰
                                </div>
                                <h4 class="text-sm font-black text-purple-950">Aucun tarif configuré</h4>
                                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                                    Aucun tarif ne correspond aux critères sélectionnés pour l'année scolaire active. Utilisez le formulaire à gauche pour en ajouter.
                                </p>
                            </div>
                        @endforelse
                    </div>

                </div>

            </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: TYPES DE FRAIS (FEE TYPES CRUD)                    --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'types'" x-transition class="space-y-6" style="display: none;">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Form: Créer un Type de Frais --}}
                    <div class="lg:col-span-1 bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                            <span class="p-1.5 rounded-lg bg-purple-100 text-purple-900 text-sm">🏷️</span>
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-wide">
                                Nouveau Type de Frais
                            </h3>
                        </div>

                        <form method="POST" action="{{ route('invoices.fee-types.store') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    Code Unique <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="code" required placeholder="Ex: CANTINE, TENUE..."
                                       class="w-full text-xs font-mono uppercase font-black rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                                <p class="text-[10px] text-slate-400 mt-1">Identifiant en majuscules (ex: INSCRIPT, SCOLARITE_T1).</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    Intitulé Public <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required placeholder="Ex: Frais de Restauration Scolaire"
                                       class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    Description (Optionnelle)
                                </label>
                                <textarea name="description" rows="2" placeholder="Ex: Payable au début de chaque trimestre scolaire..."
                                          class="w-full text-xs rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs"></textarea>
                            </div>

                            <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                                    <input type="checkbox" name="is_recurring" value="1"
                                           class="rounded border-slate-300 text-purple-900 focus:ring-purple-900 shadow-xs" />
                                    <span>Frais Récurrent (ex: par trimestre/mois)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                                    <input type="checkbox" name="is_active" value="1" checked
                                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600 shadow-xs" />
                                    <span>Type de frais actif pour la facturation</span>
                                </label>
                            </div>

                            <button type="submit"
                                    class="w-full py-2.5 bg-gradient-to-r from-purple-950 to-purple-900 hover:from-purple-900 hover:to-purple-800 text-white font-black text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                                ➕ Créer le Type de Frais
                            </button>
                        </form>
                    </div>

                    {{-- Table: Liste des Types de Frais --}}
                    <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                        <div class="pb-3 mb-4 border-b border-slate-100">
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-wide">
                                Nomenclature des Frais Scolaires
                            </h3>
                            <p class="text-xs text-slate-400">Types de frais disponibles pour la tarification et l'émission des factures.</p>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-purple-950 text-white font-black uppercase text-[10px] tracking-wider">
                                        <th class="p-3">Code & Intitulé</th>
                                        <th class="p-3">Nature</th>
                                        <th class="p-3 text-center">Tarifs / Factures</th>
                                        <th class="p-3 text-center">Statut</th>
                                        <th class="p-3 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                                    @foreach ($feeTypes as $ft)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="p-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-950 font-mono text-[10px] font-black rounded">
                                                        {{ $ft->code }}
                                                    </span>
                                                    <span class="font-extrabold text-slate-900">{{ $ft->name }}</span>
                                                </div>
                                                @if ($ft->description)
                                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $ft->description }}</p>
                                                @endif
                                            </td>
                                            <td class="p-3">
                                                @if ($ft->is_recurring)
                                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full font-extrabold text-[10px]">
                                                        🔄 Récurrent
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full font-bold text-[10px]">
                                                        📌 Ponctuel
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-center">
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-900 rounded font-bold text-[10px]">
                                                    {{ $ft->structures_count }} tarif(s)
                                                </span>
                                                <span class="px-2 py-0.5 bg-purple-50 text-purple-900 rounded font-bold text-[10px] ml-1">
                                                    {{ $ft->invoices_count }} facture(s)
                                                </span>
                                            </td>
                                            <td class="p-3 text-center">
                                                <form method="POST" action="{{ route('invoices.fee-types.toggle-active', $ft) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" title="Cliquer pour changer le statut"
                                                            class="px-2 py-0.5 rounded-full font-black text-[10px] transition {{ $ft->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                                        {{ $ft->is_active ? '● Actif' : '○ Inactif' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="p-3 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <button type="button" @click="openEditType({{ Js::from($ft) }})"
                                                            title="Modifier le type de frais"
                                                            class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg transition font-bold text-xs">
                                                        ✏️
                                                    </button>
                                                    <form method="POST" action="{{ route('invoices.fee-types.destroy', $ft) }}"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce type de frais ?');"
                                                          class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" title="Supprimer le type de frais"
                                                                {{ $ft->invoices_count > 0 ? 'disabled' : '' }}
                                                                class="p-1.5 rounded-lg transition font-bold text-xs {{ $ft->invoices_count > 0 ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-red-50 hover:bg-red-100 text-red-700' }}">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

            {{-- ======================================================== --}}
            {{-- TAB 3: FACTURATION EN MASSE                               --}}
            {{-- ======================================================== --}}
            <div x-show="activeTab === 'generation'" x-transition class="space-y-6" style="display: none;">

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 max-w-2xl">
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                        <span class="p-2 rounded-xl bg-purple-100 text-purple-900 text-lg">⚡</span>
                        <div>
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-wide">
                                Génération Automatique de Factures en Masse
                            </h3>
                            <p class="text-xs text-slate-500">
                                Émet une facture pour tous les élèves inscrits actifs de l'année scolaire en cours selon la grille tarifaire.
                            </p>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 font-medium mb-5">
                        <p class="font-bold mb-1">ℹ️ Règle anti-doublon :</p>
                        <p>Les élèves ayant déjà une facture émise pour ce type de frais et cette année scolaire seront automatiquement ignorés.</p>
                    </div>

                    <form method="POST" action="{{ route('invoices.generate') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Choisir le Type de Frais à Facturer <span class="text-red-500">*</span>
                            </label>
                            <select name="fee_type_id" required
                                    class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">
                                <option value="">-- Sélectionner un type de frais --</option>
                                @foreach ($feeTypes->where('is_active', true) as $ft)
                                    <option value="{{ $ft->id }}">
                                        {{ $ft->name }} ({{ $ft->code }}) — {{ $ft->is_recurring ? 'Récurrent' : 'Ponctuel' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full py-3 bg-gradient-to-r from-purple-950 to-purple-900 hover:from-purple-900 hover:to-purple-800 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                                🚀 Lancer la Génération Globale
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>

        {{-- ======================================================== --}}
        {{-- MODAL EDIT FEE TYPE                                       --}}
        {{-- ======================================================== --}}
        <div x-show="editTypeModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-slate-950/60 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200"
                 @click.away="editTypeModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="font-black text-sm text-purple-950 uppercase tracking-wide">
                        ✏️ Modifier le Type de Frais
                    </h3>
                    <button type="button" @click="editTypeModalOpen = false" class="text-slate-400 hover:text-slate-600 font-black text-lg">
                        &times;
                    </button>
                </div>

                <form :action="'{{ url('/invoices/fee-types') }}/' + selectedType.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Code</label>
                        <input type="text" name="code" x-model="selectedType.code" required
                               class="w-full text-xs font-mono uppercase font-black rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Intitulé</label>
                        <input type="text" name="name" x-model="selectedType.name" required
                               class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                        <textarea name="description" x-model="selectedType.description" rows="2"
                                  class="w-full text-xs rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs"></textarea>
                    </div>

                    <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input type="checkbox" name="is_recurring" value="1" x-model="selectedType.is_recurring"
                                   class="rounded border-slate-300 text-purple-900 focus:ring-purple-900 shadow-xs" />
                            <span>Frais Récurrent</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input type="checkbox" name="is_active" value="1" x-model="selectedType.is_active"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600 shadow-xs" />
                            <span>Type de frais actif</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="editTypeModalOpen = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl shadow-md transition">
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL EDIT FEE STRUCTURE                                  --}}
        {{-- ======================================================== --}}
        <div x-show="editStructureModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-slate-950/60 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200"
                 @click.away="editStructureModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="font-black text-sm text-purple-950 uppercase tracking-wide">
                        ✏️ Modifier le Tarif
                    </h3>
                    <button type="button" @click="editStructureModalOpen = false" class="text-slate-400 hover:text-slate-600 font-black text-lg">
                        &times;
                    </button>
                </div>

                <form :action="'{{ url('/invoices/fee-structures') }}/' + selectedStructure.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Type de Frais</label>
                        <select name="fee_type_id" x-model="selectedStructure.fee_type_id" required
                                class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">
                            @foreach ($feeTypes as $ft)
                                <option value="{{ $ft->id }}">{{ $ft->name }} ({{ $ft->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cycle</label>
                        <select name="cycle_id" x-model="editFormCycleId"
                                class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">
                            <option value="">— Tous les cycles (Global) —</option>
                            @foreach ($cycles as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Niveau Spécifique</label>
                        <select name="level_id" x-model="selectedStructure.level_id"
                                class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">
                            <option value="">— Tous les niveaux du cycle —</option>
                            <template x-for="lvl in allLevels.filter(l => !editFormCycleId || String(l.cycle_id) === String(editFormCycleId))" :key="lvl.id">
                                <option :value="lvl.id" x-text="(lvl.cycle ? lvl.cycle.name + ' — ' : '') + lvl.name" :selected="String(lvl.id) === String(selectedStructure.level_id)"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Montant (FCFA)</label>
                        <div class="relative">
                            <input type="number" name="amount" x-model="selectedStructure.amount" min="1" step="1" required
                                   class="w-full text-xs font-black rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs pr-14" />
                            <span class="absolute right-3 top-2.5 text-[10px] font-black text-slate-400 pointer-events-none">FCFA</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="editStructureModalOpen = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl shadow-md transition">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
