<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                📅 {{ $academicYear->name }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('academic-years.edit', $academicYear) }}" class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm">
                    ✏️ Modifier
                </a>
                <a href="{{ route('academic-years.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if(session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('status') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <!-- En-tête -->
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white flex justify-between items-center">
                    <div>
                        <div class="text-xs font-black text-amber-400 uppercase tracking-widest">Période Académique</div>
                        <h1 class="text-3xl font-black text-white mt-1">{{ $academicYear->name }}</h1>
                        <p class="text-xs text-purple-200 mt-1">
                            Du {{ $academicYear->start_date->format('d/m/Y') }} au {{ $academicYear->end_date->format('d/m/Y') }}
                        </p>
                        @if($academicYear->description)
                            <p class="text-purple-300 text-sm mt-2">{{ $academicYear->description }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @if($academicYear->is_active)
                            <span class="px-4 py-2 rounded-full text-xs font-black bg-emerald-500 text-white shadow-lg">✨ Année Active</span>
                        @else
                            <span class="px-4 py-2 rounded-full text-xs font-bold bg-white/10 text-slate-300 border border-white/20">Inactive</span>
                        @endif
                        @if($academicYear->is_closed)
                            <span class="px-4 py-2 rounded-full text-xs font-black bg-red-500 text-white shadow-lg">🔒 Clôturée</span>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $trimestres = $academicYear->periods->where('type', 'TRIMESTRE')->sortBy('order');
                $semestres  = $academicYear->periods->where('type', 'SEMESTRE')->sortBy('order');
            @endphp

            {{-- ===================== TRIMESTRES ===================== --}}
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80" x-data="{ showForm: false, editId: null }">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">📚 Trimestres</h3>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">Maternelle & Primaire</span>
                        <span class="text-xs font-semibold text-slate-400">{{ $trimestres->count() }}/3</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" x-show="!showForm" @click="showForm = true"
                                class="px-3 py-1.5 text-xs font-bold bg-purple-100 text-purple-900 rounded-lg hover:bg-purple-200 transition">
                            ➕ Ajouter
                        </button>
                        <button type="button" x-show="showForm" @click="showForm = false; editId = null"
                                class="px-3 py-1.5 text-xs font-bold bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                            ✕ Annuler
                        </button>
                    </div>
                </div>

                {{-- Formulaire ajout trimestre --}}
                <div x-show="showForm" style="display:none" class="px-6 py-4 bg-purple-50 border-b border-purple-100">
                    <form method="POST" action="{{ route('academic-years.periods.store', $academicYear) }}" class="grid grid-cols-5 gap-3 items-end">
                        @csrf
                        <input type="hidden" name="type" value="TRIMESTRE">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Nom</label>
                            <input type="text" name="name" required placeholder="Trimestre 1"
                                   class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Ordre</label>
                            <select name="order" class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Début</label>
                            <input type="date" name="start_date" required
                                   class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Fin</label>
                            <input type="date" name="end_date" required
                                   class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                        </div>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold bg-purple-900 text-white rounded-lg hover:bg-purple-800">
                            💾 Enregistrer
                        </button>
                    </form>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @forelse ($trimestres as $period)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 overflow-hidden">
                            {{-- Affichage --}}
                            <div x-show="editId !== {{ $period->id }}" class="p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="font-black text-slate-900 text-sm">{{ $period->name }}</div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $period->is_closed ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $period->is_closed ? '🔒 Clôturé' : '✅ En cours' }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 font-semibold">
                                    {{ $period->start_date->format('d/m/Y') }} — {{ $period->end_date->format('d/m/Y') }}
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                    <button type="button" @click="editId = {{ $period->id }}"
                                            class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                        <span>✏️</span> Modifier
                                    </button>
                                    @if($period->is_closed)
                                        <form method="POST" action="{{ route('academic-years.periods.toggle-close', [$academicYear, $period]) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                                <span>🔓</span> Rouvrir
                                            </button>
                                        </form>
                                    @elseif($period->hasEnded())
                                        <form method="POST" action="{{ route('academic-years.periods.toggle-close', [$academicYear, $period]) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition">
                                                <span>🔒</span> Clôturer
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled
                                                title="Impossible de clôturer : période en cours jusqu'au {{ $period->end_date->format('d/m/Y') }}"
                                                class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed">
                                            <span>🔒</span> Clôturer
                                        </button>
                                    @endif
                                    @if(! $period->is_closed)
                                        <form method="POST" action="{{ route('academic-years.periods.destroy', [$academicYear, $period]) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                                                <span>🗑️</span> Supprimer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            {{-- Formulaire édition inline --}}
                            <div x-show="editId === {{ $period->id }}" style="display:none" class="p-4 bg-amber-50">
                                <form method="POST" action="{{ route('academic-years.periods.update', [$academicYear, $period]) }}" class="space-y-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $period->name }}" required
                                           class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                                    <input type="date" name="start_date" value="{{ $period->start_date->format('Y-m-d') }}" required
                                           class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                                    <input type="date" name="end_date" value="{{ $period->end_date->format('Y-m-d') }}" required
                                           class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                                    <div class="flex gap-2">
                                        <button type="submit" class="flex-1 text-xs font-bold px-2 py-1.5 bg-purple-900 text-white rounded-lg hover:bg-purple-800">
                                            💾 Sauvegarder
                                        </button>
                                        <button type="button" @click="editId = null"
                                                class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1.5 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                                            <span>✕</span> Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-sm text-slate-400 italic">
                            Aucun trimestre défini. Cliquez sur "Ajouter" pour en créer un.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ===================== SEMESTRES ===================== --}}
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80" x-data="{ showForm: false, editId: null }">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">🎓 Semestres</h3>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Collège & Lycée</span>
                        <span class="text-xs font-semibold text-slate-400">{{ $semestres->count() }}/2</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" x-show="!showForm" @click="showForm = true"
                                class="px-3 py-1.5 text-xs font-bold bg-amber-100 text-amber-900 rounded-lg hover:bg-amber-200 transition">
                            ➕ Ajouter
                        </button>
                        <button type="button" x-show="showForm" @click="showForm = false; editId = null"
                                class="px-3 py-1.5 text-xs font-bold bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                            ✕ Annuler
                        </button>
                    </div>
                </div>

                {{-- Formulaire ajout semestre --}}
                <div x-show="showForm" style="display:none" class="px-6 py-4 bg-amber-50 border-b border-amber-100">
                    <form method="POST" action="{{ route('academic-years.periods.store', $academicYear) }}" class="grid grid-cols-5 gap-3 items-end">
                        @csrf
                        <input type="hidden" name="type" value="SEMESTRE">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Nom</label>
                            <input type="text" name="name" required placeholder="Semestre 1"
                                   class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Ordre</label>
                            <select name="order" class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Début</label>
                            <input type="date" name="start_date" required
                                   class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Fin</label>
                            <input type="date" name="end_date" required
                                   class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                        </div>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold bg-amber-600 text-white rounded-lg hover:bg-amber-700">
                            💾 Enregistrer
                        </button>
                    </form>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($semestres as $period)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 overflow-hidden">
                            {{-- Affichage --}}
                            <div x-show="editId !== {{ $period->id }}" class="p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="font-black text-slate-900 text-sm">{{ $period->name }}</div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $period->is_closed ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $period->is_closed ? '🔒 Clôturé' : '✅ En cours' }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 font-semibold">
                                    {{ $period->start_date->format('d/m/Y') }} — {{ $period->end_date->format('d/m/Y') }}
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                    <button type="button" @click="editId = {{ $period->id }}"
                                            class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                        <span>✏️</span> Modifier
                                    </button>
                                    @if($period->is_closed)
                                        <form method="POST" action="{{ route('academic-years.periods.toggle-close', [$academicYear, $period]) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                                <span>🔓</span> Rouvrir
                                            </button>
                                        </form>
                                    @elseif($period->hasEnded())
                                        <form method="POST" action="{{ route('academic-years.periods.toggle-close', [$academicYear, $period]) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition">
                                                <span>🔒</span> Clôturer
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled
                                                title="Impossible de clôturer : période en cours jusqu'au {{ $period->end_date->format('d/m/Y') }}"
                                                class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed">
                                            <span>🔒</span> Clôturer
                                        </button>
                                    @endif
                                    @if(! $period->is_closed)
                                        <form method="POST" action="{{ route('academic-years.periods.destroy', [$academicYear, $period]) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                                                <span>🗑️</span> Supprimer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            {{-- Formulaire édition inline --}}
                            <div x-show="editId === {{ $period->id }}" style="display:none" class="p-4 bg-amber-50">
                                <form method="POST" action="{{ route('academic-years.periods.update', [$academicYear, $period]) }}" class="space-y-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $period->name }}" required
                                           class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                                    <input type="date" name="start_date" value="{{ $period->start_date->format('Y-m-d') }}" required
                                           class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                                    <input type="date" name="end_date" value="{{ $period->end_date->format('Y-m-d') }}" required
                                           class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                                    <div class="flex gap-2">
                                        <button type="submit" class="flex-1 text-xs font-bold px-2 py-1.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700">
                                            💾 Sauvegarder
                                        </button>
                                        <button type="button" @click="editId = null"
                                                class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1.5 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                                            <span>✕</span> Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-sm text-slate-400 italic">
                            Aucun semestre défini. Cliquez sur "Ajouter" pour en créer un.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
