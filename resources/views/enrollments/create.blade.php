<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('enrollments.index') }}"
                    class="p-2.5 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition">
                    ←
                </a>
                <div>
                    <h2 class="font-black text-2xl text-purple-950 leading-tight">
                        ➕ Formulaire d'Inscription Scolaire
                    </h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">
                        Admission d'un élève, affectation de classe par niveau et encaissement des frais d'inscription
                    </p>
                </div>
            </div>
            <a href="{{ route('enrollments.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                Retour au registre
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-6 sm:p-8 border border-slate-200/80" x-data="{
                cycles: {{ Js::from($cycles) }},
                levels: {{ Js::from($levels) }},
                classes: {{ Js::from($classes) }},
                feeMap: {{ Js::from($feeMap) }},
            
                selectedCycleId: '{{ old('cycle_id', '') }}',
                selectedLevelId: '{{ old('level_id', '') }}',
                selectedClassId: '{{ old('class_id', $preselectedClassId ?? '') }}',
            
                amountPaid: '{{ old('amount_paid', '') }}',
                minInscriptionFee: 0,
                scolariteAnnuelle: 0,
                tranche1Fee: 0,
            
                init() {
                    if (this.selectedClassId) {
                        const cls = this.classes.find(c => String(c.id) === String(this.selectedClassId));
                        if (cls) {
                            this.selectedLevelId = String(cls.level_id);
                            if (cls.level) {
                                this.selectedCycleId = String(cls.level.cycle_id);
                            }
                        }
                    } else if (this.selectedLevelId) {
                        const lvl = this.levels.find(l => String(l.id) === String(this.selectedLevelId));
                        if (lvl) {
                            this.selectedCycleId = String(lvl.cycle_id);
                        }
                    }
                    this.updateFees(false);
                },
            
                get availableLevels() {
                    if (!this.selectedCycleId) return [];
                    return this.levels.filter(l => String(l.cycle_id) === String(this.selectedCycleId));
                },
            
                get availableClasses() {
                    if (!this.selectedLevelId) return [];
                    return this.classes.filter(c => String(c.level_id) === String(this.selectedLevelId));
                },
            
                onCycleChange() {
                    this.selectedLevelId = '';
                    this.selectedClassId = '';
                    this.updateFees(true);
                },
            
                onLevelChange() {
                    this.selectedClassId = '';
                    this.updateFees(true);
                },
            
                onClassChange() {
                    this.updateFees(false);
                },
            
                updateFees(autoFill = true) {
                    let inscript = 0;
                    let scol = 0;
                    let t1 = 0;
            
                    // 1. Priorité Tarif Niveau
                    if (this.selectedLevelId && this.feeMap.by_level && this.feeMap.by_level[this.selectedLevelId]) {
                        const lf = this.feeMap.by_level[this.selectedLevelId];
                        inscript = lf.inscript ?? 0;
                        scol = lf.scol ?? 0;
                        t1 = lf.t1 ?? 0;
                    }
            
                    // 2. Priorité Tarif Cycle si pas trouvé au niveau
                    if (!inscript && this.selectedCycleId && this.feeMap.by_cycle && this.feeMap.by_cycle[this.selectedCycleId]) {
                        const cf = this.feeMap.by_cycle[this.selectedCycleId];
                        inscript = cf.inscript ?? 0;
                        if (!scol) scol = cf.scol ?? 0;
                        if (!t1) t1 = cf.t1 ?? 0;
                    }
            
                    // 3. Tarif Global
                    if (!inscript && this.feeMap.global) {
                        inscript = this.feeMap.global.inscript ?? 0;
                    }
                    if (!scol && this.feeMap.global) {
                        scol = this.feeMap.global.scol ?? 0;
                    }
                    if (!t1 && this.feeMap.global) {
                        t1 = this.feeMap.global.t1 ?? 0;
                    }
            
                    this.minInscriptionFee = inscript;
                    this.scolariteAnnuelle = scol;
                    this.tranche1Fee = t1;
            
                    if (autoFill || !this.amountPaid || this.amountPaid === '0' || this.amountPaid === '30000') {
                        if (inscript > 0) {
                            this.amountPaid = inscript;
                        }
                    }
                },
            
                applyExactInscription() {
                    if (this.minInscriptionFee > 0) {
                        this.amountPaid = this.minInscriptionFee;
                    }
                }
            }">
                <form method="POST" action="{{ route('enrollments.store') }}" class="space-y-8">
                    @csrf

                    <!-- 1. État Civil & Identité de l'Élève -->
                    <div>
                        <h3
                            class="text-xs font-black text-purple-950 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-full bg-purple-100 text-purple-900 flex items-center justify-center text-xs">1</span>
                            Identité & État Civil de l'Élève
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="last_name"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nom
                                    de Famille *</label>
                                <input type="text" id="last_name" name="last_name" required
                                    value="{{ old('last_name') }}" placeholder="ex: Moussa"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('last_name')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="first_name"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Prénom(s)
                                    *</label>
                                <input type="text" id="first_name" name="first_name" required
                                    value="{{ old('first_name') }}" placeholder="ex: Souleymane"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('first_name')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="gender"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Genre
                                    / Sexe *</label>
                                <select id="gender" name="gender" required
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="M" {{ old('gender', 'M') === 'M' ? 'selected' : '' }}>Masculin
                                        (Garçon)</option>
                                    <option value="F" {{ old('gender') === 'F' ? 'selected' : '' }}>Féminin
                                        (Fille)</option>
                                </select>
                                @error('gender')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="birth_date"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Date
                                    de Naissance *</label>
                                <input type="date" id="birth_date" name="birth_date" required
                                    value="{{ old('birth_date') }}"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('birth_date')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="birth_place"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Lieu
                                    de Naissance *</label>
                                <input type="text" id="birth_place" name="birth_place" required
                                    value="{{ old('birth_place', 'Niamey') }}" placeholder="ex: Niamey, Commune 1"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('birth_place')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="blood_group"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Groupe
                                    Sanguin</label>
                                <select id="blood_group" name="blood_group"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">-- Non renseigné --</option>
                                    @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}"
                                            {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="previous_school"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">École
                                    de provenance</label>
                                <input type="text" id="previous_school" name="previous_school"
                                    value="{{ old('previous_school') }}" placeholder="ex: CSP Les Champions"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>

                            <div class="md:col-span-2">
                                <label for="medical_notes"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Notes
                                    Médicales & Allergies (Optionnel)</label>
                                <textarea id="medical_notes" name="medical_notes" rows="2"
                                    placeholder="Asthme, allergies alimentaires, restriction sportive..."
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">{{ old('medical_notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Parent / Tuteur Légal -->
                    <div class="border-t border-slate-100 pt-6">
                        <h3
                            class="text-xs font-black text-purple-950 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-full bg-blue-100 text-blue-900 flex items-center justify-center text-xs">2</span>
                            Parent / Tuteur Légal Responsable
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="guardian_last_name"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nom
                                    du Tuteur *</label>
                                <input type="text" id="guardian_last_name" name="guardian_last_name" required
                                    value="{{ old('guardian_last_name') }}" placeholder="ex: Souley"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('guardian_last_name')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="guardian_first_name"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Prénom(s)
                                    du Tuteur *</label>
                                <input type="text" id="guardian_first_name" name="guardian_first_name" required
                                    value="{{ old('guardian_first_name') }}" placeholder="ex: Moussa"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('guardian_first_name')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="relationship"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Lien
                                    de Parenté *</label>
                                <select id="relationship" name="relationship" required
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="PERE" {{ old('relationship') === 'PERE' ? 'selected' : '' }}>Père
                                    </option>
                                    <option value="MERE" {{ old('relationship') === 'MERE' ? 'selected' : '' }}>Mère
                                    </option>
                                    <option value="TUTEUR_LEGAL"
                                        {{ old('relationship') === 'TUTEUR_LEGAL' ? 'selected' : '' }}>Tuteur Légal
                                    </option>
                                    <option value="ONCLE" {{ old('relationship') === 'ONCLE' ? 'selected' : '' }}>
                                        Oncle / Tante</option>
                                    <option value="AUTRE" {{ old('relationship') === 'AUTRE' ? 'selected' : '' }}>
                                        Autre proche</option>
                                </select>
                            </div>

                            <div>
                                <label for="guardian_profession"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Profession
                                    du Tuteur</label>
                                <input type="text" id="guardian_profession" name="guardian_profession"
                                    value="{{ old('guardian_profession') }}"
                                    placeholder="ex: Commerçant, Enseignant, Cadre…"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>

                            <div>
                                <label for="phone_primary"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Téléphone
                                    Principal (WhatsApp) *</label>
                                <input type="text" id="phone_primary" name="phone_primary" required
                                    value="{{ old('phone_primary') }}" placeholder="+227 90 12 34 56"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('phone_primary')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="phone_secondary"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Téléphone
                                    Secondaire</label>
                                <input type="text" id="phone_secondary" name="phone_secondary"
                                    value="{{ old('phone_secondary') }}" placeholder="+227 96 12 34 56"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>

                            <div>
                                <label for="guardian_email"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Email
                                    du Tuteur</label>
                                <input type="email" id="guardian_email" name="guardian_email"
                                    value="{{ old('guardian_email') }}" placeholder="parent@example.com"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>

                            <div>
                                <label for="guardian_city"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Ville
                                    de Résidence</label>
                                <input type="text" id="guardian_city" name="guardian_city"
                                    value="{{ old('guardian_city', 'Niamey') }}" placeholder="Niamey"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>

                            <div class="md:col-span-2">
                                <label for="address"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Adresse
                                    Complète / Quartier</label>
                                <input type="text" id="address" name="address" value="{{ old('address') }}"
                                    placeholder="Quartier Plateau, Rue..."
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                        </div>
                    </div>

                    <!-- 3. Affectation Scolaire (Cycle -> Niveau -> Classe) -->
                    <div class="border-t border-slate-100 pt-6">
                        <h3
                            class="text-xs font-black text-purple-950 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-900 flex items-center justify-center text-xs">3</span>
                            Affectation de Classe & Scolarité
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- 3.1 Choix du Cycle -->
                            <div>
                                <label for="cycle_id"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                    1. Cycle d'Enseignement *
                                </label>
                                <select id="cycle_id" name="cycle_id" x-model="selectedCycleId"
                                    @change="onCycleChange()" required
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">-- Choisir le cycle --</option>
                                    <template x-for="c in cycles" :key="c.id">
                                        <option :value="String(c.id)" x-text="c.name"
                                            :selected="String(c.id) === selectedCycleId"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- 3.2 Choix du Niveau -->
                            <div>
                                <label for="level_id"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                    2. Niveau d'Études *
                                </label>
                                <select id="level_id" name="level_id" x-model="selectedLevelId"
                                    @change="onLevelChange()" required :disabled="!selectedCycleId"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">-- Choisir le niveau --</option>
                                    <template x-for="lvl in availableLevels" :key="lvl.id">
                                        <option :value="String(lvl.id)" x-text="lvl.name"
                                            :selected="String(lvl.id) === selectedLevelId"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- 3.3 Choix de la Classe -->
                            <div>
                                <label for="class_id"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                    3. Classe d'Affectation *
                                </label>
                                <select id="class_id" name="class_id" x-model="selectedClassId"
                                    @change="onClassChange()" required :disabled="!selectedLevelId"
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">-- Choisir la classe --</option>
                                    <template x-for="cls in availableClasses" :key="cls.id">
                                        <option :value="String(cls.id)" x-text="cls.name"
                                            :selected="String(cls.id) === selectedClassId"></option>
                                    </template>
                                </select>
                                @error('class_id')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 3.4 Type d'inscription -->
                            <div>
                                <label for="type"
                                    class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Type
                                    d'Inscription *</label>
                                <select id="type" name="type" required
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="NOUVEAU" {{ old('type') === 'NOUVEAU' ? 'selected' : '' }}>Nouvelle
                                        Inscription</option>
                                    <option value="REINSCRIPTION"
                                        {{ old('type') === 'REINSCRIPTION' ? 'selected' : '' }}>Réinscription</option>
                                    <option value="TRANSFERT" {{ old('type') === 'TRANSFERT' ? 'selected' : '' }}>
                                        Transfert Entrant</option>
                                </select>
                            </div>

                            <!-- 3.5 Redoublant -->
                            <div class="flex items-center pt-2 md:pt-8 md:col-span-2">
                                <label class="relative flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" id="is_repeater" name="is_repeater" value="1"
                                        {{ old('is_repeater') ? 'checked' : '' }}
                                        class="w-5 h-5 rounded-lg border-purple-300 text-purple-900 focus:ring-purple-500" />
                                    <span class="text-xs font-extrabold text-slate-700 uppercase">Élève
                                        Redoublant(e)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Frais d'Inscription & Règlement -->
                    <div
                        class="p-6 bg-gradient-to-br from-amber-50 to-amber-100/50 border border-amber-300 rounded-3xl space-y-5">
                        <div
                            class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-amber-200/80 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="p-2 bg-amber-400 text-purple-950 rounded-xl text-lg">💳</span>
                                <div>
                                    <h3 class="text-xs font-black text-amber-950 uppercase tracking-widest">
                                        4. Règlement des Frais d'Inscription
                                    </h3>
                                    <p class="text-[11px] font-bold text-amber-800">
                                        Frais obligatoires calculés selon le niveau choisi • Reçu officiel généré
                                        immédiatement
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-3 py-1 bg-purple-950 text-amber-300 font-black text-xs rounded-full shadow-xs flex items-center gap-1.5">
                                    <span>Tarif Inscription :</span>
                                    <span
                                        x-text="minInscriptionFee ? Number(minInscriptionFee).toLocaleString('fr-FR') + ' F' : 'Choisir un niveau'"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Récapitulatif Tarifaire Dynamique -->
                        <div x-show="selectedLevelId || selectedCycleId" x-cloak
                            class="bg-white/80 border border-amber-200 rounded-2xl p-4 shadow-xs">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div class="p-2.5 bg-amber-50/60 rounded-xl border border-amber-200/60">
                                    <span class="text-[10px] font-black uppercase text-amber-800 block">Frais
                                        d'Inscription Dûs</span>
                                    <span class="text-base font-black text-purple-950"
                                        x-text="Number(minInscriptionFee).toLocaleString('fr-FR') + ' FCFA'"></span>
                                </div>
                                <div class="p-2.5 bg-purple-50/50 rounded-xl border border-purple-100"
                                    x-show="scolariteAnnuelle > 0">
                                    <span class="text-[10px] font-black uppercase text-purple-700 block">Scolarité
                                        Annuelle</span>
                                    <span class="text-sm font-black text-purple-950"
                                        x-text="Number(scolariteAnnuelle).toLocaleString('fr-FR') + ' FCFA'"></span>
                                </div>
                                <div class="p-2.5 bg-blue-50/50 rounded-xl border border-blue-100"
                                    x-show="tranche1Fee > 0">
                                    <span class="text-[10px] font-black uppercase text-blue-700 block">1ère Tranche
                                        (50%)</span>
                                    <span class="text-sm font-black text-blue-950"
                                        x-text="Number(tranche1Fee).toLocaleString('fr-FR') + ' FCFA'"></span>
                                </div>
                            </div>
                            <p class="text-[11px] text-amber-900 font-semibold mt-2.5 flex items-center gap-1">
                                <span>ℹ️</span>
                                <span>Si le parent verse un montant supérieur à l'inscription, le surplus sera
                                    automatiquement imputé sur la 1ère tranche de scolarité.</span>
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label for="payment_method"
                                    class="block text-xs font-black text-amber-950 uppercase tracking-wider mb-2">Mode
                                    de Règlement *</label>
                                <select id="payment_method" name="payment_method" required
                                    class="w-full text-xs font-black border-amber-300 rounded-2xl bg-white shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="ESPECES"
                                        {{ old('payment_method') === 'ESPECES' ? 'selected' : '' }}>💵 Espèces
                                        (Comptant)</option>
                                    <option value="MOBILE_MONEY"
                                        {{ old('payment_method') === 'MOBILE_MONEY' ? 'selected' : '' }}>📱 Mobile
                                        Money (Airtel / Moov / Flooz)</option>
                                    <option value="VIREMENT"
                                        {{ old('payment_method') === 'VIREMENT' ? 'selected' : '' }}>🏦 Virement
                                        Bancaire</option>
                                    <option value="CHEQUE" {{ old('payment_method') === 'CHEQUE' ? 'selected' : '' }}>
                                        📝 Chèque Certifié</option>
                                </select>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="amount_paid"
                                        class="block text-xs font-black text-amber-950 uppercase tracking-wider">Montant
                                        perçu (FCFA) *</label>
                                    <button type="button" @click="applyExactInscription()"
                                        x-show="minInscriptionFee > 0"
                                        class="text-[10px] font-black text-purple-900 hover:text-purple-700 underline">
                                        Appliquer tarif exact
                                    </button>
                                </div>
                                <input type="number" id="amount_paid" name="amount_paid" required
                                    x-model="amountPaid" min="0" step="500"
                                    placeholder="ex: 1000 ou 3000"
                                    class="w-full text-sm font-black border-amber-300 rounded-2xl bg-white shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                <span class="text-[10px] text-amber-800 font-bold block mt-1"
                                    x-show="minInscriptionFee > 0">
                                    Tarif officiel du niveau : <strong
                                        x-text="Number(minInscriptionFee).toLocaleString('fr-FR') + ' FCFA'"></strong>
                                </span>
                                @error('amount_paid')
                                    <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="payment_reference"
                                    class="block text-xs font-black text-amber-950 uppercase tracking-wider mb-2">N°
                                    Transaction / Référence</label>
                                <input type="text" id="payment_reference" name="payment_reference"
                                    value="{{ old('payment_reference') }}" placeholder="ex: TXN-908123 / CHQ-4029"
                                    class="w-full text-xs font-bold border-amber-300 rounded-2xl bg-white shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end items-center gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('enrollments.index') }}"
                            class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black uppercase tracking-wider rounded-2xl transition">
                            Annuler
                        </a>
                        <button type="submit"
                            class="px-6 py-3 bg-purple-900 hover:bg-purple-800 text-amber-300 text-xs font-black uppercase tracking-wider rounded-2xl shadow-lg hover:shadow-xl transition flex items-center gap-2">
                            <span>💾</span>
                            <span>Valider l'Inscription & Générer le Reçu</span>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
