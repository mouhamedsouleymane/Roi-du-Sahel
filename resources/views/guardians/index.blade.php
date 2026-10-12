<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-3">
                    <span class="p-2.5 bg-purple-900 text-amber-300 rounded-2xl shadow-md text-lg">👨‍👩‍👧</span>
                    <span>Répertoire des Parents & Tuteurs Légaux</span>
                </h2>
                <p class="text-xs font-bold text-slate-500 mt-1">
                    Gestion des contacts d'urgence, responsables légaux et suivi des correspondances
                </p>
            </div>
            <a href="{{ route('guardians.create') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-purple-900 hover:bg-purple-800 text-amber-300 font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg hover:shadow-xl transition-all duration-200">
                <span class="text-base">➕</span>
                <span>Ajouter un Tuteur</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Status Message -->
            @if (session('status'))
                <div
                    class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-xs">
                    <span class="text-lg">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- 4 Cartes Statistiques Globales -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total Tuteurs -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-900 flex items-center justify-center text-2xl shadow-xs">
                        👨‍👩‍👧
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Tuteurs</div>
                        <div class="text-2xl font-black text-purple-950">{{ $totalGuardians }}</div>
                        <div class="text-[11px] font-bold text-slate-500">Parents enregistrés</div>
                    </div>
                </div>

                <!-- Pères -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-900 flex items-center justify-center text-2xl shadow-xs">
                        👨
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Pères</div>
                        <div class="text-2xl font-black text-blue-950">{{ $fathersCount }}</div>
                        <div class="text-[11px] font-bold text-blue-600">
                            {{ $totalGuardians > 0 ? round(($fathersCount / $totalGuardians) * 100) : 0 }}% des contacts
                        </div>
                    </div>
                </div>

                <!-- Mères -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-pink-100 text-pink-900 flex items-center justify-center text-2xl shadow-xs">
                        👩
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Mères</div>
                        <div class="text-2xl font-black text-pink-900">{{ $mothersCount }}</div>
                        <div class="text-[11px] font-bold text-pink-600">
                            {{ $totalGuardians > 0 ? round(($mothersCount / $totalGuardians) * 100) : 0 }}% des contacts
                        </div>
                    </div>
                </div>

                <!-- Autres Tuteurs -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-900 flex items-center justify-center text-2xl shadow-xs">
                        🤝
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Tuteurs Légaux /
                            Autres</div>
                        <div class="text-2xl font-black text-amber-950">{{ $otherGuardiansCount }}</div>
                        <div class="text-[11px] font-bold text-amber-600">Oncles, tuteurs, proches</div>
                    </div>
                </div>
            </div>

            <!-- Filtres de recherche -->
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80">
                <form method="GET" action="{{ route('guardians.index') }}"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div class="lg:col-span-2">
                        <label for="search"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Recherche</label>
                        <input type="text" id="search" name="search" value="{{ $search }}"
                            placeholder="Nom, prénom, numéro de téléphone, email..."
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 placeholder-slate-400" />
                    </div>

                    <div>
                        <label for="relationship"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Lien de
                            Parenté</label>
                        <select id="relationship" name="relationship"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les liens --</option>
                            <option value="PERE" {{ $relationship === 'PERE' ? 'selected' : '' }}>Père</option>
                            <option value="MERE" {{ $relationship === 'MERE' ? 'selected' : '' }}>Mère</option>
                            <option value="TUTEUR_LEGAL" {{ $relationship === 'TUTEUR_LEGAL' ? 'selected' : '' }}>
                                Tuteur Légal</option>
                            <option value="ONCLE" {{ $relationship === 'ONCLE' ? 'selected' : '' }}>Oncle / Tante
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="flex-1 py-2.5 px-4 bg-purple-900 hover:bg-purple-800 text-amber-300 font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-md transition">
                            🔍 Filtrer
                        </button>
                        @if ($search || $relationship)
                            <a href="{{ route('guardians.index') }}"
                                class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl transition">
                                ✕ Effacer
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table des Tuteurs -->
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xs font-black text-purple-950 uppercase tracking-widest">
                        Liste des contacts & tuteurs ({{ $guardians->total() }})
                    </h3>
                    <span class="text-xs font-bold text-slate-400">Page {{ $guardians->currentPage() }} sur
                        {{ $guardians->lastPage() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50/80 text-[11px] font-black text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4 text-left">Parent / Tuteur</th>

                                <th class="px-6 py-4 text-left">Téléphones & WhatsApp</th>

                                <th class="px-6 py-4 text-left">Enfant(s) Rattaché(s)</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($guardians as $guardian)
                                <tr class="hover:bg-purple-50/30 transition-colors">
                                    <!-- Parent (Avatar + Nom) -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-2xl font-black text-xs flex items-center justify-center shadow-xs {{ in_array($guardian->relationship, ['MERE', 'Mère']) ? 'bg-pink-100 text-pink-700 border-2 border-pink-200' : 'bg-blue-100 text-blue-700 border-2 border-blue-200' }}">
                                                {{ mb_substr($guardian->first_name, 0, 1) }}{{ mb_substr($guardian->last_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('guardians.show', $guardian) }}"
                                                    class="text-sm font-black text-slate-900 hover:text-purple-900 transition">
                                                    {{ $guardian->first_name }} {{ $guardian->last_name }}
                                                </a>
                                                @if ($guardian->email)
                                                    <div class="text-xs text-slate-400 font-bold">
                                                        {{ $guardian->email }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>



                                    <!-- Téléphones & WhatsApp -->
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <div class="flex items-center gap-2 font-mono font-bold text-slate-900">
                                            <span>📞 {{ $guardian->phone_primary }}</span>
                                            @if ($guardian->phone_primary)
                                                <div class="flex items-center gap-1">
                                                    <a href="tel:{{ $guardian->phone_primary }}"
                                                        class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[10px]"
                                                        title="Appeler">
                                                        Appel
                                                    </a>
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $guardian->phone_primary) }}"
                                                        target="_blank"
                                                        class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold rounded-lg text-[10px] flex items-center gap-0.5"
                                                        title="Envoyer un message WhatsApp">
                                                        <span>💬</span>
                                                        <span>WhatsApp</span>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        @if ($guardian->phone_secondary)
                                            <div class="text-[11px] font-mono text-slate-400 mt-1">
                                                📱 {{ $guardian->phone_secondary }}
                                            </div>
                                        @endif
                                    </td>
                                    <!-- Enfants Rattachés -->
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse ($guardian->students as $child)
                                                <a href="{{ route('students.show', $child) }}"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-xl bg-purple-100 text-purple-950 hover:bg-purple-200 transition border border-purple-200">
                                                    <span>🎓 {{ $child->first_name }} {{ $child->last_name }}</span>
                                                    @if ($child->currentEnrollment?->schoolClass)
                                                        <span
                                                            class="text-[10px] text-purple-700 font-black">({{ $child->currentEnrollment->schoolClass->name }})</span>
                                                    @endif
                                                </a>
                                            @empty
                                                <span class="text-xs text-slate-400 italic">Aucun élève rattaché</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('guardians.show', $guardian) }}"
                                                class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-950 font-black text-xs rounded-xl transition flex items-center gap-1">
                                                <span>👁️</span>
                                                <span>Fiche</span>
                                            </a>
                                            <a href="{{ route('guardians.edit', $guardian) }}"
                                                class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 font-black text-xs rounded-xl transition flex items-center gap-1">
                                                <span>✏️</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="max-w-sm mx-auto space-y-3">
                                            <div class="text-4xl">👨‍👩‍👧</div>
                                            <div class="text-sm font-black text-slate-800">Aucun tuteur trouvé</div>
                                            <p class="text-xs text-slate-400">
                                                Aucun tuteur ne correspond aux critères de recherche actuels.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($guardians->hasPages())
                    <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                        {{ $guardians->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
