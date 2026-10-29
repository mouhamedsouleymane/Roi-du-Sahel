<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('pre-enrollments.index') }}"
                    class="p-2.5 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition">
                    ←
                </a>
                <div>
                    <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                        <span>📝</span>
                        <span>Dossier de Pré-inscription : {{ $preEnrollment->reference }}</span>
                    </h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">
                        Candidat : <span class="text-purple-950 font-extrabold">{{ $preEnrollment->student_first_name }}
                            {{ $preEnrollment->student_last_name }}</span> • Année : <span
                            class="text-purple-900 font-extrabold">{{ $activeYear?->name ?? 'En cours' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pre-enrollments.index') }}"
                    class="px-4 py-2.5 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs rounded-2xl border border-slate-200 shadow-sm transition">
                    ← Retour à la liste
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <!-- Flash Status Message -->
            @if (session('status'))
                <div
                    class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-xs">
                    <span class="text-lg">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-xs font-bold space-y-1 shadow-xs">
                    <div class="flex items-center gap-2 text-sm font-extrabold text-red-900">
                        <span>⚠️</span>
                        <span>Veuillez corriger les erreurs suivantes :</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-red-700">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Hero Banner -->
            <div
                class="bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white p-6 sm:p-8 rounded-3xl shadow-xl border border-purple-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div
                        class="w-16 h-16 rounded-3xl font-black text-2xl flex items-center justify-center shadow-md {{ $preEnrollment->gender === 'F' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ mb_substr($preEnrollment->student_first_name, 0, 1) }}{{ mb_substr($preEnrollment->student_last_name, 0, 1) }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span
                                class="text-xs font-mono font-black px-3 py-1 rounded-xl bg-purple-800 text-amber-300 border border-purple-700">
                                {{ $preEnrollment->reference }}
                            </span>
                            @if ($preEnrollment->status === 'EN_ATTENTE')
                                <span
                                    class="text-[11px] font-black px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    ⏳ En attente de validation
                                </span>
                            @elseif ($preEnrollment->status === 'VALIDEE')
                                <span
                                    class="text-[11px] font-black px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    ✅ Candidature Validée & Inscrite
                                </span>
                            @else
                                <span
                                    class="text-[11px] font-black px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                    🚫 Candidature Rejetée
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black text-white">
                            {{ $preEnrollment->student_first_name }} {{ $preEnrollment->student_last_name }}
                        </h1>
                        <p class="text-xs text-purple-200 font-bold">
                            Demande soumise le {{ $preEnrollment->created_at->format('d/m/Y à H:i') }} • Cycle souhaité
                            : <strong class="text-amber-300">{{ $preEnrollment->requested_cycle }}</strong>
                            @if ($preEnrollment->requested_level)
                                ({{ $preEnrollment->requested_level }})
                            @endif
                        </p>
                    </div>
                </div>

                @if ($preEnrollment->converted_enrollment_id)
                    <div class="flex items-center gap-3">
                        <a href="{{ route('enrollments.show', $preEnrollment->converted_enrollment_id) }}"
                            class="px-5 py-3 bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg transition flex items-center gap-2 transform hover:scale-105">
                            <span>📄</span>
                            <span>Voir le Dossier d'Inscription</span>
                        </a>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Colonne Gauche : Détails du Candidat & Tuteur (2 colonnes) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Identité de l'élève -->
                    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80">
                        <h3
                            class="text-xs font-black text-purple-950 uppercase tracking-widest mb-5 flex items-center gap-2">
                            <span>🎓</span>
                            <span>Informations sur le Candidat</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">Nom
                                    Complet</span>
                                <span
                                    class="font-extrabold text-slate-900 text-sm">{{ $preEnrollment->student_first_name }}
                                    {{ $preEnrollment->student_last_name }}</span>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">Genre
                                    / Sexe</span>
                                <span class="font-extrabold text-slate-900 text-sm">
                                    {{ $preEnrollment->gender === 'F' ? '👧 Féminin (Fille)' : '👦 Masculin (Garçon)' }}
                                </span>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">Date
                                    & Lieu de Naissance</span>
                                @php
                                    $birthDate = $preEnrollment->birth_date;
                                    $age = $birthDate ? $birthDate->age : null;
                                @endphp
                                <span class="font-extrabold text-slate-900 text-sm">
                                    {{ $birthDate ? $birthDate->format('d/m/Y') : 'N/A' }} à
                                    {{ $preEnrollment->birth_place ?? 'Niamey' }}
                                    @if ($age !== null)
                                        <span class="text-xs text-slate-500 font-bold">({{ $age }} ans)</span>
                                    @endif
                                </span>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">Groupe
                                    Sanguin</span>
                                <span class="font-extrabold text-purple-900 font-mono text-sm">
                                    {{ $preEnrollment->blood_group ?? 'Non renseigné' }}
                                </span>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">Souhait
                                    d'Admission</span>
                                <span class="font-extrabold text-purple-950 text-sm">
                                    {{ $preEnrollment->requested_cycle }}
                                    @if ($preEnrollment->requested_level)
                                        • {{ $preEnrollment->requested_level }}
                                    @endif
                                    <span
                                        class="text-xs font-bold text-slate-500">({{ $preEnrollment->type ?? 'NOUVEAU' }})</span>
                                </span>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">École
                                    de Provenance</span>
                                <span class="font-extrabold text-slate-900 text-sm">
                                    {{ $preEnrollment->previous_school ?? 'Non renseignée' }}
                                </span>
                            </div>
                        </div>

                        @if ($preEnrollment->medical_notes)
                            <div class="mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-100 text-xs">
                                <span
                                    class="text-rose-800 font-black block mb-1 uppercase tracking-wider text-[10px]">Notes
                                    Médicales & Allergies</span>
                                <p class="text-rose-950 font-bold">{{ $preEnrollment->medical_notes }}</p>
                            </div>
                        @endif

                        @if ($preEnrollment->notes)
                            <div class="mt-4 p-4 rounded-2xl bg-amber-50 border border-amber-100 text-xs">
                                <span
                                    class="text-amber-800 font-black block mb-1 uppercase tracking-wider text-[10px]">Remarques
                                    / Précisions complémentaires</span>
                                <p class="text-amber-950 font-bold">{{ $preEnrollment->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Coordonnées du Parent / Tuteur Légal -->
                    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80">
                        <h3
                            class="text-xs font-black text-purple-950 uppercase tracking-widest mb-5 flex items-center gap-2">
                            <span>👨‍👩‍👧</span>
                            <span>Coordonnées du Parent / Tuteur Légal</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">Nom
                                    du Tuteur</span>
                                <span
                                    class="font-extrabold text-slate-900 text-sm">{{ $preEnrollment->guardian_first_name }}
                                    {{ $preEnrollment->guardian_last_name }}</span>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">Lien
                                    & Profession</span>
                                <span class="font-extrabold text-slate-900 text-sm">{{ $preEnrollment->relationship }}
                                    • {{ $preEnrollment->guardian_profession ?? 'Non renseignée' }}</span>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">Téléphones</span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span
                                        class="font-mono font-extrabold text-slate-900 text-sm">{{ $preEnrollment->guardian_phone }}</span>
                                    @if ($preEnrollment->guardian_phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $preEnrollment->guardian_phone) }}"
                                            target="_blank"
                                            class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-black rounded-lg text-[10px] flex items-center gap-0.5 transition">
                                            💬 WhatsApp
                                        </a>
                                    @endif
                                </div>
                                @if ($preEnrollment->guardian_phone_secondary)
                                    <div class="text-[11px] font-mono text-slate-400 mt-1">
                                        📱 Sec : {{ $preEnrollment->guardian_phone_secondary }}
                                    </div>
                                @endif
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span
                                    class="text-slate-400 font-bold block mb-1 uppercase tracking-wider text-[10px]">Email
                                    & Adresse</span>
                                <span
                                    class="font-extrabold text-slate-900 text-sm">{{ $preEnrollment->guardian_email ?? 'Non renseigné' }}</span>
                                <div class="text-[11px] text-slate-500 font-medium mt-1">
                                    📍 {{ $preEnrollment->guardian_city ?? 'Niamey' }} @if ($preEnrollment->guardian_address)
                                        — {{ $preEnrollment->guardian_address }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Colonne Droite : Panneau d'Action & Conversion (1 colonne) -->
                <div class="space-y-6" id="conversion">

                    @if ($preEnrollment->status === 'EN_ATTENTE')
                        <!-- Boîte de Conversion Interactive avec Alpine.js -->
                        @php
                            $cyclesJson = $cycles
                                ->map(
                                    fn($c) => [
                                        'id' => $c->id,
                                        'code' => $c->code,
                                        'name' => $c->name,
                                    ],
                                )
                                ->values()
                                ->toJson();

                            $levelsJson = $levels
                                ->map(
                                    fn($l) => [
                                        'id' => $l->id,
                                        'cycle_id' => $l->cycle_id,
                                        'code' => $l->code,
                                        'name' => $l->name,
                                    ],
                                )
                                ->values()
                                ->toJson();

                            $classesJson = $classes
                                ->map(
                                    fn($cls) => [
                                        'id' => $cls->id,
                                        'level_id' => $cls->level_id,
                                        'name' => $cls->name,
                                        'capacity' => $cls->capacity,
                                    ],
                                )
                                ->values()
                                ->toJson();

                            $feeMapJson = json_encode($feeMap);
                        @endphp

                        <div class="bg-white rounded-3xl p-6 shadow-xl border-2 border-purple-900/40 space-y-5"
                            x-data="{
                                allCycles: {{ $cyclesJson }},
                                allLevels: {{ $levelsJson }},
                                allClasses: {{ $classesJson }},
                                feeMap: {{ $feeMapJson }},
                                selectedCycle: '{{ $initialCycleId ?? '' }}',
                                selectedLevel: '{{ $initialLevelId ?? '' }}',
                                selectedClass: '',
                                amountPaid: 0,
                                paymentMethod: 'ESPECES',
                            
                                get filteredLevels() {
                                    if (!this.selectedCycle) return [];
                                    return this.allLevels.filter(l => l.cycle_id == this.selectedCycle);
                                },
                            
                                get filteredClasses() {
                                    if (!this.selectedLevel) return [];
                                    return this.allClasses.filter(c => c.level_id == this.selectedLevel);
                                },
                            
                                get currentFees() {
                                    if (!this.selectedLevel || !this.feeMap[this.selectedLevel]) {
                                        return { inscription_amount: 0, tuition_amount: 0 };
                                    }
                                    return this.feeMap[this.selectedLevel];
                                },
                            
                                onCycleChange() {
                                    const levels = this.filteredLevels;
                                    this.selectedLevel = levels.length > 0 ? levels[0].id : '';
                                    this.onLevelChange();
                                },
                            
                                onLevelChange() {
                                    const classes = this.filteredClasses;
                                    this.selectedClass = classes.length > 0 ? classes[0].id : '';
                                    this.amountPaid = this.currentFees.inscription_amount;
                                },
                            
                                init() {
                                    if (this.selectedCycle) {
                                        if (!this.selectedLevel && this.filteredLevels.length > 0) {
                                            this.selectedLevel = this.filteredLevels[0].id;
                                        }
                                        if (this.filteredClasses.length > 0) {
                                            this.selectedClass = this.filteredClasses[0].id;
                                        }
                                        this.amountPaid = this.currentFees.inscription_amount;
                                    }
                                }
                            }">

                            <div class="border-b border-slate-100 pb-3">
                                <h3
                                    class="text-xs font-black text-purple-950 uppercase tracking-widest flex items-center gap-2">
                                    <span>⚡</span>
                                    <span>Valider & Inscrire l'Élève</span>
                                </h3>
                                <p class="text-xs font-bold text-slate-500 mt-1">
                                    Sélectionnez le cycle, le niveau et la classe pour finaliser l'admission.
                                </p>
                            </div>

                            <form method="POST" action="{{ route('pre-enrollments.convert', $preEnrollment) }}"
                                class="space-y-4" data-confirm="Confirmer l'inscription officielle de cet élève ?">
                                @csrf

                                <!-- 1. Choix du Cycle -->
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                        1. Cycle d'enseignement *
                                    </label>
                                    <select x-model="selectedCycle" @change="onCycleChange()" required
                                        class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">-- Sélectionner le cycle --</option>
                                        <template x-for="cyc in allCycles" :key="cyc.id">
                                            <option :value="cyc.id" x-text="cyc.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- 2. Choix du Niveau -->
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                        2. Niveau d'études *
                                    </label>
                                    <select x-model="selectedLevel" @change="onLevelChange()" required
                                        :disabled="!selectedCycle"
                                        class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 disabled:bg-slate-100">
                                        <option value="">-- Sélectionner le niveau --</option>
                                        <template x-for="lvl in filteredLevels" :key="lvl.id">
                                            <option :value="lvl.id" x-text="lvl.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- 3. Choix de la Classe -->
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                        3. Classe d'affectation *
                                    </label>
                                    <select name="class_id" x-model="selectedClass" required
                                        :disabled="!selectedLevel"
                                        class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 disabled:bg-slate-100">
                                        <option value="">-- Choisir la classe --</option>
                                        <template x-for="cls in filteredClasses" :key="cls.id">
                                            <option :value="cls.id"
                                                x-text="cls.name + ' (Capacité: ' + cls.capacity + ')'"></option>
                                        </template>
                                    </select>
                                    @error('class_id')
                                        <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Récapitulatif Tarifaire Dynamique -->
                                <div
                                    class="p-3.5 bg-purple-50 rounded-2xl border border-purple-100 space-y-1.5 text-xs font-bold text-purple-950">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-600">Frais d'inscription obligatoires :</span>
                                        <span class="text-sm font-black text-purple-950"
                                            x-text="Number(currentFees.inscription_amount).toLocaleString('fr-FR') + ' F'"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                                        <span>Scolarité annuelle indicative :</span>
                                        <span class="font-black text-slate-700"
                                            x-text="Number(currentFees.tuition_amount).toLocaleString('fr-FR') + ' F'"></span>
                                    </div>
                                </div>

                                <!-- 4. Mode de Règlement -->
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                        Mode de Règlement *
                                    </label>
                                    <select name="payment_method" x-model="paymentMethod" required
                                        class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                        <option value="ESPECES">💵 Espèces</option>
                                        <option value="MOBILE_MONEY">📱 Mobile Money (Airtel / Moov / Zamani)</option>
                                        <option value="VIREMENT">🏦 Virement bancaire</option>
                                        <option value="CHEQUE">📝 Chèque</option>
                                    </select>
                                </div>

                                <!-- 5. Montant Versé -->
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                        Montant Versé à l'Inscription (FCFA)
                                    </label>
                                    <input type="number" name="amount_paid" x-model="amountPaid" min="0"
                                        step="500" required
                                        class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                    <span class="text-[10px] text-slate-400 font-semibold mt-1 block">
                                        💡 Tout surplus sera automatiquement crédité sur la 1ère tranche de scolarité.
                                    </span>
                                </div>

                                <!-- 6. Lieu de Naissance -->
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                        Lieu de Naissance
                                    </label>
                                    <input type="text" name="birth_place"
                                        value="{{ $preEnrollment->birth_place ?? 'Niamey' }}"
                                        class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                </div>

                                <!-- Bouton de Validation -->
                                <button type="submit"
                                    class="w-full py-3.5 bg-gradient-to-r from-purple-900 to-indigo-900 hover:from-purple-800 hover:to-indigo-800 text-amber-300 font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg hover:shadow-xl transition transform hover:scale-[1.02] flex items-center justify-center gap-2 cursor-pointer mt-2">
                                    <span>🚀</span>
                                    <span>Inscrire Définitivement</span>
                                </button>
                            </form>

                            <!-- Option de Rejet -->
                            <div class="border-t border-slate-100 pt-3">
                                <form method="POST" action="{{ route('pre-enrollments.reject', $preEnrollment) }}"
                                    data-confirm="Êtes-vous sûr de vouloir marquer cette candidature comme rejetée ?">
                                    @csrf
                                    <button type="submit"
                                        class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-black uppercase tracking-wider rounded-xl transition">
                                        🚫 Rejeter la candidature
                                    </button>
                                </form>
                            </div>
                        </div>
                    @elseif ($preEnrollment->status === 'VALIDEE')
                        <!-- Statut VALIDÉE -->
                        <div class="bg-white rounded-3xl p-6 shadow-xl border border-emerald-200 space-y-4">
                            <div class="flex items-center gap-2.5 text-emerald-800">
                                <span class="text-xl">✅</span>
                                <h3 class="text-xs font-black uppercase tracking-widest">
                                    Candidature Validée
                                </h3>
                            </div>
                            <div
                                class="text-xs font-bold text-emerald-950 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl space-y-2">
                                <p>Cette demande a été convertie en inscription officielle.</p>
                                @if ($preEnrollment->convertedEnrollment)
                                    <div
                                        class="pt-2 border-t border-emerald-200/60 text-slate-700 font-semibold space-y-1">
                                        <div>Classe : <strong
                                                class="text-purple-950">{{ $preEnrollment->convertedEnrollment->schoolClass?->name }}</strong>
                                        </div>
                                        <div>Matricule : <span
                                                class="font-mono font-bold text-indigo-700">{{ $preEnrollment->convertedEnrollment->student?->matricule }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @if ($preEnrollment->processed_at)
                                <div class="text-[11px] text-slate-400 font-medium">
                                    Validée le {{ $preEnrollment->processed_at->format('d/m/Y à H:i') }}
                                    @if ($preEnrollment->processor)
                                        par <strong
                                            class="text-slate-600">{{ $preEnrollment->processor->name }}</strong>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Statut REJETÉE -->
                        <div class="bg-white rounded-3xl p-6 shadow-xl border border-rose-200 space-y-4">
                            <div class="flex items-center gap-2.5 text-rose-800">
                                <span class="text-xl">🚫</span>
                                <h3 class="text-xs font-black uppercase tracking-widest">
                                    Candidature Rejetée
                                </h3>
                            </div>
                            <div
                                class="text-xs font-bold text-rose-950 p-4 bg-rose-50 border border-rose-100 rounded-2xl">
                                Cette candidature a été marquée comme rejetée.
                            </div>
                            @if ($preEnrollment->processed_at)
                                <div class="text-[11px] text-slate-400 font-medium">
                                    Rejetée le {{ $preEnrollment->processed_at->format('d/m/Y à H:i') }}
                                    @if ($preEnrollment->processor)
                                        par <strong
                                            class="text-slate-600">{{ $preEnrollment->processor->name }}</strong>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Suppression du dossier -->
                    <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80">
                        <form method="POST" action="{{ route('pre-enrollments.destroy', $preEnrollment) }}"
                            data-confirm="Supprimer définitivement cette demande de pré-inscription ?">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full py-2.5 bg-slate-100 hover:bg-rose-50 hover:text-rose-700 text-slate-600 text-xs font-bold rounded-xl transition">
                                🗑️ Supprimer le dossier
                            </button>
                        </form>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
