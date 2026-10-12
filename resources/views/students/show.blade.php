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
                        Matricule : <span class="font-mono font-bold text-purple-900">{{ $student->matricule }}</span> • Créé le {{ $student->created_at->format('d/m/Y') }}
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
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-xs">
                    <span class="text-lg">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Hero Profile Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                <div class="flex items-center gap-5">
                    <div class="w-20 h-20 rounded-3xl font-black text-2xl flex items-center justify-center shadow-md {{ $student->gender === 'F' ? 'bg-pink-100 text-pink-700 border-2 border-pink-200' : 'bg-blue-100 text-blue-700 border-2 border-blue-200' }}">
                        {{ mb_substr($student->first_name, 0, 1) }}{{ mb_substr($student->last_name, 0, 1) }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-mono font-black px-3 py-1 rounded-xl bg-purple-100 text-purple-900 border border-purple-200">
                                {{ $student->matricule }}
                            </span>
                            @if ($student->status === 'ACTIF')
                                <span class="text-[11px] font-black px-3 py-1 rounded-full bg-emerald-100 text-emerald-800">
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
                            {{ $student->gender === 'F' ? 'Fille' : 'Garçon' }} • Né(e) le {{ $student->birth_date ? $student->birth_date->format('d/m/Y') : 'N/A' }} ({{ $student->birth_date ? $student->birth_date->age . ' ans' : '' }}) à {{ $student->birth_place }}
                        </p>
                    </div>
                </div>

                <!-- Carte Classe Actuelle -->
                <div class="bg-purple-950 text-white p-5 rounded-3xl shadow-lg border border-purple-900 min-w-[240px] text-right md:text-right w-full md:w-auto">
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
                            <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Date de Naissance</div>
                            <div class="text-sm font-bold text-slate-900 mt-1">
                                {{ $student->birth_date ? $student->birth_date->format('d F Y') : 'N/A' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Lieu de Naissance</div>
                            <div class="text-sm font-bold text-slate-900 mt-1">
                                {{ $student->birth_place ?? 'Non renseigné' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Groupe Sanguin</div>
                            <div class="text-sm font-bold text-purple-900 font-mono mt-1">
                                {{ $student->blood_group ?? 'Non renseigné' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">École Précédente</div>
                            <div class="text-sm font-bold text-slate-900 mt-1">
                                {{ $student->previous_school ?? 'Première inscription' }}
                            </div>
                        </div>
                    </div>

                    <!-- Notes médicales -->
                    <div class="p-5 rounded-2xl border {{ $student->medical_notes ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-100' }}">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm">{{ $student->medical_notes ? '⚠️' : '🩺' }}</span>
                            <span class="text-xs font-black uppercase tracking-wider {{ $student->medical_notes ? 'text-amber-900' : 'text-slate-500' }}">
                                Notes Médicales & Allergies
                            </span>
                        </div>
                        <p class="text-xs font-bold {{ $student->medical_notes ? 'text-amber-950' : 'text-slate-400 italic' }}">
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
                            <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3 hover:border-purple-200 transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-black text-slate-900">
                                            {{ $guardian->first_name }} {{ $guardian->last_name }}
                                        </div>
                                        <div class="text-xs font-bold text-slate-400">
                                            {{ $guardian->profession ?? 'Profession non renseignée' }}
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-extrabold rounded-full bg-blue-100 text-blue-800">
                                        {{ $guardian->relationship }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-200/60 text-xs">
                                    <!-- Téléphone Principal -->
                                    <div class="flex items-center justify-between bg-white px-3 py-2 rounded-xl border border-slate-200/80">
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
                                        <div class="flex items-center justify-between bg-white px-3 py-2 rounded-xl border border-slate-200/80">
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
                            <div class="p-6 text-center text-xs text-slate-400 italic bg-slate-50 rounded-2xl border border-slate-100">
                                Aucun tuteur ou parent n'est actuellement associé à cet élève.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- 3. Historique Complet des Inscriptions -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="p-2 bg-emerald-100 text-emerald-900 rounded-xl text-lg">📜</span>
                        <div>
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-widest">
                                Parcours & Historique des Inscriptions
                            </h3>
                            <p class="text-xs text-slate-400 font-bold">Inscriptions enregistrées au fil des années scolaires</p>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-mono font-bold text-purple-900">
                                        {{ $enrollment->enrollment_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-black text-slate-900">
                                        {{ $enrollment->academicYear->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-xl bg-purple-100 text-purple-900">
                                            {{ $enrollment->schoolClass->name }}
                                        </span>
                                        <span class="text-[11px] font-bold text-slate-400 ml-1">
                                            ({{ $enrollment->schoolClass->level->cycle->name }})
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                        <div class="font-bold">{{ $enrollment->type }}</div>
                                        <div class="text-[11px] text-slate-400">Le {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($enrollment->status === 'VALIDE')
                                            <span class="px-2.5 py-1 text-[11px] font-black rounded-full bg-emerald-100 text-emerald-800">
                                                Validé
                                            </span>
                                        @elseif ($enrollment->status === 'EN_ATTENTE')
                                            <span class="px-2.5 py-1 text-[11px] font-black rounded-full bg-amber-100 text-amber-800">
                                                En attente
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-[11px] font-black rounded-full bg-red-100 text-red-800">
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
