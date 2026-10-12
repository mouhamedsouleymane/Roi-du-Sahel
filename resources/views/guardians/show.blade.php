<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('guardians.index') }}"
                   class="p-2.5 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition">
                    ←
                </a>
                <div>
                    <h2 class="font-black text-2xl text-purple-950 leading-tight">
                        Fiche Contact Tuteur : {{ $guardian->first_name }} {{ $guardian->last_name }}
                    </h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">
                        Responsable légal de {{ $guardian->students->count() }} élève(s)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('guardians.edit', $guardian) }}"
                   class="px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs uppercase tracking-wider rounded-2xl shadow-md transition flex items-center gap-2">
                    <span>✏️</span>
                    <span>Modifier</span>
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

            <!-- Hero Guardian Profile Card -->
            <div class="bg-purple-950 text-white p-6 sm:p-8 rounded-3xl shadow-xl border border-purple-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-20 h-20 rounded-3xl font-black text-3xl flex items-center justify-center shadow-md {{ in_array($guardian->relationship, ['MERE', 'Mère']) ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ mb_substr($guardian->first_name, 0, 1) }}{{ mb_substr($guardian->last_name, 0, 1) }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-wider px-3 py-1 rounded-full bg-purple-800 text-amber-300">
                                {{ $guardian->relationship }}
                            </span>
                            @if ($guardian->city)
                                <span class="text-xs text-purple-300">📍 {{ $guardian->city }}</span>
                            @endif
                        </div>
                        <h1 class="text-2xl font-black text-white">
                            {{ $guardian->first_name }} {{ $guardian->last_name }}
                        </h1>
                        <p class="text-xs text-purple-200 font-bold">
                            {{ $guardian->profession ?? 'Profession non renseignée' }}
                        </p>
                    </div>
                </div>

                <!-- Boutons d'Action Rapide de Contact -->
                <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-start md:justify-end">
                    @if ($guardian->phone_primary)
                        <a href="tel:{{ $guardian->phone_primary }}"
                           class="px-4 py-2.5 bg-purple-800 hover:bg-purple-700 text-white font-black text-xs rounded-2xl transition border border-purple-700 flex items-center gap-1.5 shadow-sm">
                            <span>📞</span>
                            <span>Appeler ({{ $guardian->phone_primary }})</span>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $guardian->phone_primary) }}"
                           target="_blank"
                           class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-xs rounded-2xl transition shadow-md flex items-center gap-1.5">
                            <span>💬</span>
                            <span>WhatsApp</span>
                        </a>
                    @endif
                    @if ($guardian->email)
                        <a href="mailto:{{ $guardian->email }}"
                           class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-black text-xs rounded-2xl transition border border-slate-700 flex items-center gap-1.5">
                            <span>✉️</span>
                            <span>Email</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Grille d'Informations 2 Colonnes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- 1. Coordonnées Complètes -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="p-2 bg-purple-100 text-purple-900 rounded-xl text-lg">📞</span>
                        <div>
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-widest">
                                Coordonnées & Adresse
                            </h3>
                            <p class="text-xs text-slate-400 font-bold">Informations de contact et domiciliation</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase">Téléphone Principal</div>
                            <div class="text-sm font-black text-slate-900 font-mono mt-1">
                                {{ $guardian->phone_primary }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase">Téléphone Secondaire</div>
                            <div class="text-sm font-bold text-slate-900 font-mono mt-1">
                                {{ $guardian->phone_secondary ?? 'Non renseigné' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase">Adresse Email</div>
                            <div class="text-xs font-bold text-slate-900 mt-1 truncate">
                                {{ $guardian->email ?? 'Non renseignée' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase">Ville</div>
                            <div class="text-sm font-bold text-slate-900 mt-1">
                                {{ $guardian->city ?? 'Niamey' }}
                            </div>
                        </div>

                        <div class="col-span-2 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase">Adresse / Quartier</div>
                            <div class="text-xs font-bold text-slate-800 mt-1">
                                {{ $guardian->address ?? 'Aucune adresse renseignée' }}
                            </div>
                        </div>

                        <!-- Statut Compte d'Accès Espace Parent -->
                        <div class="col-span-2 p-4 bg-purple-50/60 rounded-2xl border border-purple-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">🔐</span>
                                <div>
                                    <div class="text-xs font-black text-purple-950">Compte d'Accès Espace Parent</div>
                                    <div class="text-[11px] text-slate-500 font-semibold mt-0.5">
                                        @if ($guardian->user)
                                            Identifiant de connexion : <strong class="text-purple-900 font-mono">{{ $guardian->user->phone ?? $guardian->user->email }}</strong>
                                        @else
                                            <span class="text-amber-700">Compte en attente d'association</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if ($guardian->user)
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider">
                                    ✅ Compte Actif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2. Enfants Rattachés -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 bg-blue-100 text-blue-900 rounded-xl text-lg">🎓</span>
                            <div>
                                <h3 class="text-sm font-black text-purple-950 uppercase tracking-widest">
                                    Élèves Rattachés ({{ $guardian->students->count() }})
                                </h3>
                                <p class="text-xs text-slate-400 font-bold">Enfants inscrits sous la responsabilité de ce tuteur</p>
                            </div>
                        </div>
                        <a href="{{ route('enrollments.create') }}"
                           class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-950 font-black text-xs rounded-xl transition">
                            ➕ Inscrire
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($guardian->students as $student)
                            <a href="{{ route('students.show', $student) }}"
                               class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-purple-300 hover:bg-purple-50/40 transition flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl font-black text-xs flex items-center justify-center shadow-xs {{ $student->gender === 'F' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ mb_substr($student->first_name, 0, 1) }}{{ mb_substr($student->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-900 text-sm group-hover:text-purple-900 transition">
                                            {{ $student->last_name }} {{ $student->first_name }}
                                        </div>
                                        <div class="text-xs text-slate-400 font-bold font-mono">
                                            {{ $student->matricule }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-xl bg-purple-100 text-purple-900 border border-purple-200">
                                        {{ $student->currentEnrollment?->schoolClass?->name ?? 'Non inscrit' }}
                                    </span>
                                    <div class="text-[10px] font-bold text-slate-400 mt-1">
                                        {{ $student->currentEnrollment?->schoolClass?->level->cycle->name ?? '' }}
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-8 text-center text-xs text-slate-400 italic bg-slate-50 rounded-2xl border border-slate-100">
                                Aucun élève n'est actuellement rattaché à ce tuteur.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
