@php
    $catBadge = match(strtolower($sbj->category)) {
        'scientifique' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'littéraire'   => 'bg-amber-50 text-amber-700 border-amber-200',
        'langues'      => 'bg-blue-50 text-blue-700 border-blue-200',
        'sport'        => 'bg-rose-50 text-rose-700 border-rose-200',
        default        => 'bg-purple-50 text-purple-700 border-purple-200',
    };
    $canDelete = ($sbj->coefficients_count == 0 && $sbj->teacher_assignments_count == 0 && $sbj->evaluations_count == 0);
@endphp
<div class="p-5 bg-slate-50/70 border border-slate-200 rounded-2xl hover:border-purple-300 hover:shadow-md transition flex flex-col justify-between">
    <div>
        <div class="flex items-start justify-between gap-2 mb-2">
            <div>
                <h4 class="text-sm font-black text-slate-900 leading-tight">{{ $sbj->name }}</h4>
                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                    <span class="text-2xs font-black px-2 py-0.5 rounded-md bg-purple-900 text-white tracking-wide">
                        {{ $sbj->code }}
                    </span>
                    <span class="text-2xs font-extrabold px-2 py-0.5 rounded-md border {{ $catBadge }}">
                        {{ $categories[$sbj->category] ?? ucfirst($sbj->category) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-1">
                @if($sbj->is_active)
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" title="Active"></span>
                @else
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-300" title="Inactive"></span>
                @endif
            </div>
        </div>

        @if($sbj->description)
            <p class="text-xs text-slate-500 font-medium line-clamp-2 mt-2 leading-relaxed">
                {{ $sbj->description }}
            </p>
        @endif

        <div class="flex items-center gap-4 text-2xs font-bold text-slate-400 mt-3 pt-3 border-t border-slate-200/60">
            <span title="Nombre de niveaux configurés">⚖️ {{ $sbj->coefficients_count }} niveaux</span>
            <span title="Affectations enseignants">👨‍🏫 {{ $sbj->teacher_assignments_count }} profs</span>
            <span title="Évaluations enregistrées">📝 {{ $sbj->evaluations_count }} évals</span>
        </div>
    </div>

    <div class="flex items-center justify-between gap-2 mt-4 pt-3 border-t border-slate-200/60">
        {{-- Toggle Active --}}
        <form method="POST" action="{{ route('subjects.toggle-active', $sbj) }}" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="text-xs font-extrabold px-2.5 py-1 rounded-lg transition {{ $sbj->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                {{ $sbj->is_active ? 'Actif' : 'Inactif' }}
            </button>
        </form>

        <div class="flex items-center gap-1.5">
            <a href="{{ route('subjects.edit', $sbj) }}"
               class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                <span>✏️</span> Modifier
            </a>

            @if($canDelete)
                <form method="POST" action="{{ route('subjects.destroy', $sbj) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            data-confirm="Supprimer la matière {{ $sbj->name }} ({{ $sbj->code }}) du catalogue ?"
                            class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                        <span>🗑️</span> Supprimer
                    </button>
                </form>
            @else
                <button type="button" disabled
                        title="Impossible de supprimer : coefficients ou données associés"
                        class="flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-300 cursor-not-allowed">
                    <span>🗑️</span> Supprimer
                </button>
            @endif
        </div>
    </div>
</div>
