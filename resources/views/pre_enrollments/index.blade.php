<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>🌐</span>
                    <span>Demandes de Pré-inscription en Ligne</span>
                </h2>
                <p class="text-xs font-bold text-slate-500 mt-1">
                    Candidatures soumises depuis le site web public à valider et convertir en inscriptions
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('enrollments.index') }}"
                    class="px-4 py-2.5 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition flex items-center gap-1.5">
                    <span>📋</span>
                    <span>Registre Officiel</span>
                </a>
                <a href="{{ route('enrollments.create') }}"
                    class="px-5 py-2.5 bg-purple-900 hover:bg-purple-800 text-amber-300 font-black text-xs uppercase tracking-wider rounded-2xl shadow-md transition flex items-center gap-2">
                    <span>➕</span>
                    <span>Nouvelle Inscription</span>
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

            <!-- 4 Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-900 flex items-center justify-center text-2xl shadow-xs">
                        📥
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Demandes
                        </div>
                        <div class="text-2xl font-black text-purple-950">{{ $totalPreEnrollments }}</div>
                        <div class="text-[11px] font-bold text-purple-700">Toutes candidatures web</div>
                    </div>
                </div>

                <!-- En Attente -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-900 flex items-center justify-center text-2xl shadow-xs">
                        ⏳
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">En Attente</div>
                        <div class="text-2xl font-black text-amber-900">{{ $pendingCount }}</div>
                        <div class="text-[11px] font-bold text-amber-600">À examiner & convertir</div>
                    </div>
                </div>

                <!-- Validées -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-900 flex items-center justify-center text-2xl shadow-xs">
                        🎓
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Converties</div>
                        <div class="text-2xl font-black text-emerald-950">{{ $validatedCount }}</div>
                        <div class="text-[11px] font-bold text-emerald-700">Inscriptions effectives</div>
                    </div>
                </div>

                <!-- Rejetées -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-900 flex items-center justify-center text-2xl shadow-xs">
                        🚫
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Rejetées</div>
                        <div class="text-2xl font-black text-rose-950">{{ $rejectedCount }}</div>
                        <div class="text-[11px] font-bold text-rose-600">Dossiers refusés</div>
                    </div>
                </div>
            </div>

            <!-- Filtres & Recherche -->
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80">
                <form method="GET" action="{{ route('pre-enrollments.index') }}"
                    class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div class="sm:col-span-2">
                        <label for="search"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Recherche</label>
                        <input type="text" id="search" name="search" value="{{ $search }}"
                            placeholder="Référence (ex: PRE-2026-...), Nom de l'élève ou du parent, Téléphone..."
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 placeholder-slate-400" />
                    </div>

                    <div>
                        <label for="status"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Statut du
                            Dossier</label>
                        <select id="status" name="status"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les statuts --</option>
                            <option value="EN_ATTENTE" {{ $status === 'EN_ATTENTE' ? 'selected' : '' }}>⏳ En Attente
                            </option>
                            <option value="VALIDEE" {{ $status === 'VALIDEE' ? 'selected' : '' }}>✅ Validée / Convertie
                            </option>
                            <option value="REJETEE" {{ $status === 'REJETEE' ? 'selected' : '' }}>🚫 Rejetée</option>
                        </select>
                    </div>

                    <div class="sm:col-span-3 flex justify-end gap-2 pt-2">
                        @if ($search || $status)
                            <a href="{{ route('pre-enrollments.index') }}"
                                class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl transition">
                                ✕ Effacer les filtres
                            </a>
                        @endif
                        <button type="submit"
                            class="py-2.5 px-6 bg-purple-900 hover:bg-purple-800 text-amber-300 font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-md transition">
                            🔍 Filtrer les demandes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table des Pré-inscriptions -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs font-black text-purple-950 uppercase tracking-widest">
                        Demandes Reçues ({{ $preEnrollments->total() }})
                    </h3>
                    <span class="text-xs font-bold text-slate-400">Page {{ $preEnrollments->currentPage() }} sur
                        {{ $preEnrollments->lastPage() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-[11px] font-black text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4 text-left">Référence & Date</th>
                                <th class="px-6 py-4 text-left">Candidat (Élève)</th>
                                <th class="px-6 py-4 text-left">Cycle & Niveau Souhaité</th>
                                <th class="px-6 py-4 text-left">Parent / Contact</th>
                                <th class="px-6 py-4 text-left">Statut</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-700">
                            @forelse ($preEnrollments as $item)
                                <tr class="hover:bg-purple-50/30 transition">
                                    <!-- Réf & Date -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="font-mono font-black text-purple-950 px-2.5 py-1 rounded-xl bg-purple-100 text-xs">
                                            {{ $item->reference }}
                                        </span>
                                        <div class="text-[11px] font-medium text-slate-400 mt-1">
                                            {{ $item->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </td>

                                    <!-- Candidat -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-2xl font-black text-xs flex items-center justify-center shadow-xs {{ $item->gender === 'F' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ mb_substr($item->student_first_name, 0, 1) }}{{ mb_substr($item->student_last_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('pre-enrollments.show', $item) }}"
                                                    class="text-sm font-black text-slate-900 hover:text-purple-900 transition">
                                                    {{ $item->student_first_name }} {{ $item->student_last_name }}
                                                </a>
                                                <div class="text-[11px] text-slate-400 font-medium">
                                                    Né(e) le {{ $item->birth_date->format('d/m/Y') }}
                                                    ({{ $item->gender === 'F' ? 'Fille' : 'Garçon' }})
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Cycle souhaité -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-3 py-1 bg-slate-100 text-slate-800 font-black rounded-xl text-xs">
                                            {{ $item->requested_cycle }}
                                        </span>
                                    </td>

                                    <!-- Parent & WhatsApp -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900">{{ $item->guardian_first_name }}
                                            {{ $item->guardian_last_name }}</div>
                                        <div class="flex items-center gap-1 mt-1">
                                            <a href="tel:{{ $item->guardian_phone }}"
                                                class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[10px]">
                                                📞 {{ $item->guardian_phone }}
                                            </a>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->guardian_phone) }}"
                                                target="_blank"
                                                class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold rounded-lg text-[10px] flex items-center gap-0.5">
                                                <span>💬</span>
                                                <span>WhatsApp</span>
                                            </a>
                                        </div>
                                    </td>

                                    <!-- Statut -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($item->status === 'EN_ATTENTE')
                                            <span
                                                class="px-3 py-1 text-xs font-black rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                                ⏳ En Attente
                                            </span>
                                        @elseif ($item->status === 'VALIDEE')
                                            <span
                                                class="px-3 py-1 text-xs font-black rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                ✅ Convertie
                                            </span>
                                        @else
                                            <span
                                                class="px-3 py-1 text-xs font-black rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                                                🚫 Rejetée
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('pre-enrollments.show', $item) }}"
                                                class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-950 font-black text-xs rounded-xl transition flex items-center gap-1">
                                                <span>👁️</span>
                                                <span>Examiner</span>
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                        <div class="text-4xl mb-3">📭</div>
                                        <div class="text-base font-bold text-slate-700">Aucune demande de
                                            pré-inscription trouvée.</div>
                                        <div class="text-xs mt-1">Les candidatures soumises en ligne apparaîtront
                                            automatiquement ici.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($preEnrollments->hasPages())
                    <div class="p-6 border-t border-slate-100">
                        {{ $preEnrollments->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
