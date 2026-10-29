<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('students.index') }}"
                    class="p-2.5 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition">
                    ←
                </a>
                <div>
                    <h2 class="font-black text-2xl text-purple-950 leading-tight">
                        Dossier Élève : {{ $student->last_name }} {{ $student->first_name }}
                    </h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">
                        Matricule : <span class="font-mono font-bold text-purple-900">{{ $student->matricule }}</span> •
                        Créé le {{ $student->created_at->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('students.edit', $student) }}"
                    class="px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs uppercase tracking-wider rounded-2xl shadow-md transition flex items-center gap-2">
                    <span>✏️</span>
                    <span>Modifier</span>
                </a>
                <a href="{{ route('enrollments.create') }}"
                    class="px-5 py-2.5 bg-purple-900 hover:bg-purple-800 text-amber-300 font-black text-xs uppercase tracking-wider rounded-2xl shadow-md transition flex items-center gap-2">
                    <span>➕</span>
                    <span>Inscrire / Réinscrire</span>
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

            <!-- Hero Profile Card -->
            <div
                class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                <div class="flex items-center gap-5">
                    <div
                        class="w-20 h-20 rounded-3xl font-black text-2xl flex items-center justify-center shadow-md {{ $student->gender === 'F' ? 'bg-pink-100 text-pink-700 border-2 border-pink-200' : 'bg-blue-100 text-blue-700 border-2 border-blue-200' }}">
                        {{ mb_substr($student->first_name, 0, 1) }}{{ mb_substr($student->last_name, 0, 1) }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="text-xs font-mono font-black px-3 py-1 rounded-xl bg-purple-100 text-purple-900 border border-purple-200">
                                {{ $student->matricule }}
                            </span>
                            @if ($student->status === 'ACTIF')
                                <span
                                    class="text-[11px] font-black px-3 py-1 rounded-full bg-emerald-100 text-emerald-800">
                                    ● Dossier Actif
                                </span>
                            @else
                                <span class="text-[11px] font-black px-3 py-1 rounded-full bg-amber-100 text-amber-800">
                                    ● {{ $student->status }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl font-black text-slate-900">
                            {{ $student->last_name }} {{ $student->first_name }}
                        </h1>
                        <p class="text-xs font-bold text-slate-500">
                            {{ $student->gender === 'F' ? 'Fille' : 'Garçon' }} • Né(e) le
                            {{ $student->birth_date ? $student->birth_date->format('d/m/Y') : 'N/A' }}
                            ({{ $student->birth_date ? $student->birth_date->age . ' ans' : '' }}) à
                            {{ $student->birth_place }}
                        </p>
                    </div>
                </div>

                <!-- Carte Classe Actuelle -->
                <div
                    class="bg-purple-950 text-white p-5 rounded-3xl shadow-lg border border-purple-900 min-w-[240px] text-right md:text-right w-full md:w-auto">
                    <div class="text-[11px] font-black text-purple-300 uppercase tracking-widest">Classe Active</div>
                    <div class="text-xl font-black text-amber-300 mt-1">
                        {{ $student->currentEnrollment?->schoolClass?->name ?? 'Aucune classe' }}
                    </div>
                    <div class="text-xs font-bold text-purple-200 mt-0.5">
                        {{ $student->currentEnrollment?->schoolClass?->level->cycle->name ?? 'Non inscrit cette année' }}
                    </div>
                </div>
            </div>

            <!-- Grille 2 Colonnes : Identité & Santé / Tuteurs Légaux -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- 1. Identité & Renseignements Médicaux -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="p-2 bg-purple-100 text-purple-900 rounded-xl text-lg">📋</span>
                        <div>
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-widest">
                                Identité & Santé
                            </h3>
                            <p class="text-xs text-slate-400 font-bold">Informations administratives et médicales</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Date de
                                Naissance</div>
                            <div class="text-sm font-bold text-slate-900 mt-1">
                                {{ $student->birth_date ? $student->birth_date->format('d F Y') : 'N/A' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Lieu de
                                Naissance</div>
                            <div class="text-sm font-bold text-slate-900 mt-1">
                                {{ $student->birth_place ?? 'Non renseigné' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Groupe Sanguin
                            </div>
                            <div class="text-sm font-bold text-purple-900 font-mono mt-1">
                                {{ $student->blood_group ?? 'Non renseigné' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">École Précédente
                            </div>
                            <div class="text-sm font-bold text-slate-900 mt-1">
                                {{ $student->previous_school ?? 'Première inscription' }}
                            </div>
                        </div>
                    </div>

                    <!-- Notes médicales -->
                    <div
                        class="p-5 rounded-2xl border {{ $student->medical_notes ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-100' }}">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm">{{ $student->medical_notes ? '⚠️' : '🩺' }}</span>
                            <span
                                class="text-xs font-black uppercase tracking-wider {{ $student->medical_notes ? 'text-amber-900' : 'text-slate-500' }}">
                                Notes Médicales & Allergies
                            </span>
                        </div>
                        <p
                            class="text-xs font-bold {{ $student->medical_notes ? 'text-amber-950' : 'text-slate-400 italic' }}">
                            {{ $student->medical_notes ?? 'Aucune note ou restriction médicale signalée.' }}
                        </p>
                    </div>
                </div>

                <!-- 2. Parents & Tuteurs Légaux -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="p-2 bg-blue-100 text-blue-900 rounded-xl text-lg">👨‍👩‍👧</span>
                        <div>
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-widest">
                                Parents & Tuteurs Légaux
                            </h3>
                            <p class="text-xs text-slate-400 font-bold">Contacts d'urgence et responsables</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse ($student->guardians as $guardian)
                            <div
                                class="p-5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3 hover:border-purple-200 transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-black text-slate-900">
                                            {{ $guardian->first_name }} {{ $guardian->last_name }}
                                        </div>
                                        <div class="text-xs font-bold text-slate-400">
                                            {{ $guardian->profession ?? 'Profession non renseignée' }}
                                        </div>
                                    </div>
                                    <span
                                        class="px-3 py-1 text-xs font-extrabold rounded-full bg-blue-100 text-blue-800">
                                        {{ $guardian->relationship }}
                                    </span>
                                </div>

                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-200/60 text-xs">
                                    <!-- Téléphone Principal -->
                                    <div
                                        class="flex items-center justify-between bg-white px-3 py-2 rounded-xl border border-slate-200/80">
                                        <div class="flex items-center gap-1.5 font-mono font-bold text-slate-800">
                                            <span>📞</span>
                                            <span>{{ $guardian->phone_primary }}</span>
                                        </div>
                                        @if ($guardian->phone_primary)
                                            <div class="flex items-center gap-1">
                                                <a href="tel:{{ $guardian->phone_primary }}"
                                                    class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[10px]">
                                                    Appel
                                                </a>
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $guardian->phone_primary) }}"
                                                    target="_blank"
                                                    class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold rounded-lg text-[10px]">
                                                    WhatsApp
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Téléphone Secondaire -->
                                    @if ($guardian->phone_secondary)
                                        <div
                                            class="flex items-center justify-between bg-white px-3 py-2 rounded-xl border border-slate-200/80">
                                            <div class="flex items-center gap-1.5 font-mono font-bold text-slate-600">
                                                <span>📱</span>
                                                <span>{{ $guardian->phone_secondary }}</span>
                                            </div>
                                            <a href="tel:{{ $guardian->phone_secondary }}"
                                                class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[10px]">
                                                Appel
                                            </a>
                                        </div>
                                    @endif

                                    <!-- Email -->
                                    @if ($guardian->email)
                                        <div class="sm:col-span-2 flex items-center gap-1.5 text-slate-600">
                                            <span>✉️</span>
                                            <span class="font-bold">{{ $guardian->email }}</span>
                                        </div>
                                    @endif

                                    <!-- Adresse -->
                                    @if ($guardian->address || $guardian->city)
                                        <div class="sm:col-span-2 flex items-center gap-1.5 text-slate-500 text-[11px]">
                                            <span>📍</span>
                                            <span>{{ $guardian->address }}{{ $guardian->city ? ', ' . $guardian->city : '' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div
                                class="p-6 text-center text-xs text-slate-400 italic bg-slate-50 rounded-2xl border border-slate-100">
                                Aucun tuteur ou parent n'est actuellement associé à cet élève.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- 3. Évaluations, Notes & Performances Académiques -->
            <div x-data="{
                selectedPeriod: '',
                selectedSubject: '',
                selectedType: '',
            }"
                class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-6">

                <!-- En-tête de section -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 bg-amber-100 text-amber-950 rounded-2xl text-xl shadow-xs">📊</span>
                        <div>
                            <h3 class="text-base font-black text-purple-950 uppercase tracking-widest">
                                Évaluations & Notes de l'Élève
                            </h3>
                            <p class="text-xs text-slate-400 font-bold">
                                Suivi continu des devoirs, compositions et résultats scolaires (Barème :
                                /{{ $scale }})
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('evaluations.index') }}"
                            class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-950 font-black text-xs rounded-xl transition flex items-center gap-1.5 shadow-xs">
                            <span>📝</span>
                            <span>Toutes les évaluations</span>
                        </a>
                    </div>
                </div>

                <!-- Indicateurs de Synthèse (KPIs) -->
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
                    <!-- 1. Moyenne Générale -->
                    <div
                        class="p-4 bg-gradient-to-br from-purple-50 to-indigo-50/60 rounded-2xl border border-purple-100/80 flex flex-col justify-between">
                        <div class="text-[11px] font-black text-purple-800 uppercase tracking-wider">Moyenne Estimée
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            @if ($generalAverage !== null)
                                <span
                                    class="text-2xl font-black text-purple-950 font-mono">{{ number_format($generalAverage, 2, ',', ' ') }}</span>
                                <span class="text-xs font-bold text-purple-600">/{{ $scale }}</span>
                            @else
                                <span class="text-lg font-black text-slate-400 italic">N/A</span>
                            @endif
                        </div>
                        <div class="mt-2">
                            @if ($generalAverage !== null)
                                @php
                                    $passingLimit = $isOutOfTen ? 5.0 : 10.0;
                                    $isPassing = $generalAverage >= $passingLimit;
                                @endphp
                                <span
                                    class="text-[10px] font-black px-2 py-0.5 rounded-full {{ $isPassing ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $isPassing ? 'Admis(e)' : 'En difficulté' }}
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-slate-400">Aucune note</span>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Meilleure Note -->
                    <div
                        class="p-4 bg-emerald-50/60 rounded-2xl border border-emerald-100 flex flex-col justify-between">
                        <div class="text-[11px] font-black text-emerald-800 uppercase tracking-wider">Meilleure Note
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            @if ($highestGrade !== null)
                                <span
                                    class="text-2xl font-black text-emerald-900 font-mono">{{ number_format((float) $highestGrade, 2, ',', ' ') }}</span>
                            @else
                                <span class="text-lg font-black text-slate-400 italic">N/A</span>
                            @endif
                        </div>
                        <div class="mt-2 text-[10px] font-bold text-emerald-700">Performance max</div>
                    </div>

                    <!-- 3. Note la plus Basse -->
                    <div class="p-4 bg-rose-50/60 rounded-2xl border border-rose-100 flex flex-col justify-between">
                        <div class="text-[11px] font-black text-rose-800 uppercase tracking-wider">Note Mini</div>
                        <div class="mt-2 flex items-baseline gap-1">
                            @if ($lowestGrade !== null)
                                <span
                                    class="text-2xl font-black text-rose-900 font-mono">{{ number_format((float) $lowestGrade, 2, ',', ' ') }}</span>
                            @else
                                <span class="text-lg font-black text-slate-400 italic">N/A</span>
                            @endif
                        </div>
                        <div class="mt-2 text-[10px] font-bold text-rose-700">Seuil plancher</div>
                    </div>

                    <!-- 4. Total Évaluations -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                        <div class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Évaluations</div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-black text-slate-900 font-mono">{{ $totalEvaluations }}</span>
                            <span class="text-xs font-bold text-slate-400">enregistrée(s)</span>
                        </div>
                        <div class="mt-2 text-[10px] font-bold text-slate-500">Total dossiers</div>
                    </div>

                    <!-- 5. Absences aux Évaluations -->
                    <div
                        class="p-4 {{ $absencesCount > 0 ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200/80' }} rounded-2xl border flex flex-col justify-between">
                        <div
                            class="text-[11px] font-black {{ $absencesCount > 0 ? 'text-amber-900' : 'text-slate-500' }} uppercase tracking-wider">
                            Absences</div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span
                                class="text-2xl font-black {{ $absencesCount > 0 ? 'text-amber-900' : 'text-slate-900' }} font-mono">{{ $absencesCount }}</span>
                            <span
                                class="text-xs font-bold {{ $absencesCount > 0 ? 'text-amber-700' : 'text-slate-400' }}">évaluation(s)</span>
                        </div>
                        <div
                            class="mt-2 text-[10px] font-bold {{ $absencesCount > 0 ? 'text-amber-800' : 'text-emerald-700' }}">
                            {{ $absencesCount > 0 ? 'Absence(s) notée(s)' : '100% Assiduité' }}
                        </div>
                    </div>
                </div>

                <!-- Relevés de Bulletins Trimestriels / Semestriels (si générés) -->
                @if ($reportCards->isNotEmpty())
                    <div
                        class="p-5 bg-gradient-to-r from-purple-900 to-indigo-950 text-white rounded-2xl space-y-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="text-lg">📜</span>
                                <h4 class="text-xs font-black uppercase tracking-widest text-amber-300">
                                    Bulletins Officiels Disponibles
                                </h4>
                            </div>
                            <span class="text-[11px] text-purple-200 font-bold">
                                {{ $reportCards->count() }} bulletin(s) officiel(s)
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                            @foreach ($reportCards as $reportCard)
                                <div
                                    class="bg-white/10 backdrop-blur-xs rounded-xl p-3.5 border border-white/10 hover:border-amber-400/50 transition space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="text-xs font-black text-amber-300">{{ $reportCard->period?->name ?? 'Période' }}</span>
                                        @if ($reportCard->rank)
                                            <span
                                                class="text-[10px] font-black px-2 py-0.5 rounded-full bg-amber-400 text-purple-950 font-mono">
                                                {{ $reportCard->rank }}<sup>{{ $reportCard->rank === 1 ? 'er' : 'ème' }}</sup>
                                                / {{ $reportCard->total_students ?? '-' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-baseline gap-1.5">
                                        <span
                                            class="text-xl font-black font-mono text-white">{{ number_format((float) $reportCard->general_average, 2, ',', ' ') }}</span>
                                        <span class="text-xs text-purple-300">/20</span>
                                        <span
                                            class="text-[11px] text-emerald-300 font-bold ml-auto">{{ $reportCard->appreciation_label }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 pt-2 border-t border-white/10">
                                        <a href="{{ route('report-cards.show', $reportCard) }}"
                                            class="flex-1 text-center py-1 bg-white/20 hover:bg-white/30 text-white text-[11px] font-bold rounded-lg transition">
                                            Consulter
                                        </a>
                                        <a href="{{ route('report-cards.pdf', $reportCard) }}"
                                            class="px-2.5 py-1 bg-amber-400 hover:bg-amber-300 text-purple-950 text-[11px] font-black rounded-lg transition">
                                            PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Filtres Interactifs (Alpine.js) -->
                @if ($grades->isNotEmpty())
                    <div
                        class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Filtre Période -->
                            <div>
                                <select x-model="selectedPeriod"
                                    class="text-xs font-bold border-slate-200 bg-white rounded-xl shadow-2xs focus:ring-purple-500 focus:border-purple-500 text-slate-800">
                                    <option value="">Toutes les périodes ({{ $periods->count() }})</option>
                                    @foreach ($periods as $period)
                                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filtre Matière -->
                            <div>
                                <select x-model="selectedSubject"
                                    class="text-xs font-bold border-slate-200 bg-white rounded-xl shadow-2xs focus:ring-purple-500 focus:border-purple-500 text-slate-800">
                                    <option value="">Toutes les matières ({{ $subjects->count() }})</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filtre Type -->
                            <div>
                                <select x-model="selectedType"
                                    class="text-xs font-bold border-slate-200 bg-white rounded-xl shadow-2xs focus:ring-purple-500 focus:border-purple-500 text-slate-800">
                                    <option value="">Tous les types d'évaluation</option>
                                    <option value="DEVOIR">Devoirs</option>
                                    <option value="COMPOSITION">Compositions</option>
                                    <option value="CONTROLE">Contrôles</option>
                                    <option value="EXAMEN">Examens</option>
                                </select>
                            </div>
                        </div>

                        <!-- Reset Filtres -->
                        <div class="text-right">
                            <button type="button" x-show="selectedPeriod || selectedSubject || selectedType" x-cloak
                                @click="selectedPeriod = ''; selectedSubject = ''; selectedType = '';"
                                class="text-xs font-bold text-purple-900 hover:text-purple-700 underline cursor-pointer">
                                ↺ Réinitialiser les filtres
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Tableau détaillé des notes -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50/80 text-[11px] font-black text-slate-500 uppercase tracking-wider">
                                <th class="px-5 py-3.5 text-left">Date & Période</th>
                                <th class="px-5 py-3.5 text-left">Matière</th>
                                <th class="px-5 py-3.5 text-left">Évaluation & Coeff.</th>
                                <th class="px-5 py-3.5 text-left">Enseignant</th>
                                <th class="px-5 py-3.5 text-center">Note / Barème</th>
                                <th class="px-5 py-3.5 text-left">Remarque</th>
                                <th class="px-5 py-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($grades as $grade)
                                @php
                                    $eval = $grade->evaluation;
                                    $maxScore = (float) ($eval?->max_score ?? 20);
                                    $score = $grade->score !== null ? (float) $grade->score : null;
                                    $normalized = $grade->normalized_score;

                                    // Barème et mention
                                    $scoreBadgeColor = 'bg-slate-100 text-slate-800';
                                    $mentionLabel = 'Passable';

                                    if ($grade->is_absent) {
                                        $mentionLabel = 'Absent(e)';
                                        $scoreBadgeColor = 'bg-rose-100 text-rose-800 border border-rose-200';
                                    } elseif ($normalized !== null) {
                                        if ($normalized >= 16) {
                                            $mentionLabel = 'Très Bien';
                                            $scoreBadgeColor =
                                                'bg-emerald-100 text-emerald-800 border border-emerald-200';
                                        } elseif ($normalized >= 14) {
                                            $mentionLabel = 'Bien';
                                            $scoreBadgeColor = 'bg-green-100 text-green-800 border border-green-200';
                                        } elseif ($normalized >= 12) {
                                            $mentionLabel = 'Assez Bien';
                                            $scoreBadgeColor = 'bg-blue-100 text-blue-800 border border-blue-200';
                                        } elseif ($normalized >= 10) {
                                            $mentionLabel = 'Passable';
                                            $scoreBadgeColor = 'bg-amber-100 text-amber-800 border border-amber-200';
                                        } else {
                                            $mentionLabel = 'Insuffisant';
                                            $scoreBadgeColor = 'bg-red-100 text-red-800 border border-red-200';
                                        }
                                    }
                                @endphp
                                <tr x-show="(!selectedPeriod || '{{ $eval?->period_id }}' === selectedPeriod) &&
                                            (!selectedSubject || '{{ $eval?->subject_id }}' === selectedSubject)
&&
                                            (!selectedType || '{{ $eval?->type }}' === selectedType)"
                                    class="hover:bg-slate-50/60 transition">

                                    <!-- 1. Date & Période -->
                                    <td class="px-5 py-4 whitespace-nowrap text-xs">
                                        <div class="font-bold text-slate-800">
                                            {{ $eval?->evaluation_date ? $eval->evaluation_date->format('d/m/Y') : ($grade->created_at ? $grade->created_at->format('d/m/Y') : '–') }}
                                        </div>
                                        <div class="text-[11px] font-semibold text-purple-900 mt-0.5">
                                            {{ $eval?->period?->name ?? 'Période indéfinie' }}
                                        </div>
                                    </td>

                                    <!-- 2. Matière -->
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-black rounded-xl bg-purple-50 text-purple-950 border border-purple-100">
                                            <span>📚</span>
                                            <span>{{ $eval?->subject?->name ?? 'Matière' }}</span>
                                        </span>
                                    </td>

                                    <!-- 3. Évaluation & Type -->
                                    <td class="px-5 py-4 whitespace-nowrap text-xs">
                                        <div class="font-black text-slate-900 flex items-center gap-1.5">
                                            <span>{{ $eval?->title ?? 'Évaluation' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span
                                                class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                                {{ $eval?->type ?? 'DEVOIR' }}
                                            </span>
                                            <span class="text-[10px] font-mono font-bold text-slate-500">
                                                Coeff. {{ $eval?->coefficient ?? 1 }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- 4. Enseignant -->
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-600">
                                        {{ $eval?->teacher ? $eval->teacher->first_name . ' ' . $eval->teacher->last_name : '–' }}
                                    </td>

                                    <!-- 5. Note / Barème -->
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        @if ($grade->is_absent)
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800">
                                                <span>⚠️</span>
                                                <span>Absent(e)</span>
                                            </span>
                                        @elseif ($score !== null)
                                            <div class="flex flex-col items-center">
                                                <div class="flex items-baseline gap-1">
                                                    <span
                                                        class="text-base font-black font-mono {{ $normalized >= 10 ? 'text-slate-900' : 'text-red-700' }}">
                                                        {{ number_format($score, 2, ',', ' ') }}
                                                    </span>
                                                    <span class="text-[11px] font-bold text-slate-400">/
                                                        {{ (int) $maxScore }}</span>
                                                </div>
                                                <span
                                                    class="mt-1 text-[10px] font-black px-2 py-0.5 rounded-full {{ $scoreBadgeColor }}">
                                                    {{ $mentionLabel }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Non noté</span>
                                        @endif
                                    </td>

                                    <!-- 6. Remarque / Appréciation -->
                                    <td class="px-5 py-4 text-xs text-slate-600 max-w-xs truncate">
                                        {{ $grade->comment ?? '–' }}
                                    </td>

                                    <!-- 7. Action -->
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        @if ($eval)
                                            <a href="{{ route('evaluations.show', $eval) }}"
                                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition shadow-2xs">
                                                Détails
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-xs text-slate-400 italic">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="text-3xl">📝</span>
                                            <p class="font-bold text-slate-500">Aucune note ou évaluation enregistrée
                                                pour cet élève actuellement.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Historique Complet des Inscriptions -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="p-2 bg-emerald-100 text-emerald-900 rounded-xl text-lg">📜</span>
                        <div>
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-widest">
                                Parcours & Historique des Inscriptions
                            </h3>
                            <p class="text-xs text-slate-400 font-bold">Inscriptions enregistrées au fil des années
                                scolaires</p>
                        </div>
                    </div>
                    <a href="{{ route('enrollments.create') }}"
                        class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-950 font-black text-xs rounded-xl transition">
                        ➕ Nouvelle Inscription
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50/80 text-[11px] font-black text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4 text-left">N° Inscription</th>
                                <th class="px-6 py-4 text-left">Année Scolaire</th>
                                <th class="px-6 py-4 text-left">Classe & Cycle</th>
                                <th class="px-6 py-4 text-left">Type / Date</th>
                                <th class="px-6 py-4 text-center">Statut</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($student->enrollments as $enrollment)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-xs font-mono font-bold text-purple-900">
                                        {{ $enrollment->enrollment_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-black text-slate-900">
                                        {{ $enrollment->academicYear->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-1 text-xs font-bold rounded-xl bg-purple-100 text-purple-900">
                                            {{ $enrollment->schoolClass->name }}
                                        </span>
                                        <span class="text-[11px] font-bold text-slate-400 ml-1">
                                            ({{ $enrollment->schoolClass->level->cycle->name }})
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                        <div class="font-bold">{{ $enrollment->type }}</div>
                                        <div class="text-[11px] text-slate-400">Le
                                            {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($enrollment->status === 'VALIDE')
                                            <span
                                                class="px-2.5 py-1 text-[11px] font-black rounded-full bg-emerald-100 text-emerald-800">
                                                Validé
                                            </span>
                                        @elseif ($enrollment->status === 'EN_ATTENTE')
                                            <span
                                                class="px-2.5 py-1 text-[11px] font-black rounded-full bg-amber-100 text-amber-800">
                                                En attente
                                            </span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 text-[11px] font-black rounded-full bg-red-100 text-red-800">
                                                {{ $enrollment->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('enrollments.receipt', $enrollment) }}"
                                                class="px-3 py-1.5 bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs rounded-xl transition shadow-xs flex items-center gap-1">
                                                <span>📄</span>
                                                <span>Reçu</span>
                                            </a>
                                            <a href="{{ route('enrollments.show', $enrollment) }}"
                                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                                                Détails
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-xs text-slate-400 italic">
                                        Aucune inscription trouvée pour cet élève.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
