@php
    $coeffs = $level->coefficients->sortBy(fn($c) => $c->subject->name ?? '');
    $totalCoeffLevel = $coeffs->sum('coefficient');
    $totalHoursLevel = $coeffs->sum('weekly_hours');
@endphp
<div class="bg-slate-50/70 rounded-2xl border border-slate-200 p-4 flex flex-col justify-between hover:border-purple-200 transition">
    <div>
        <div class="flex items-start justify-between gap-2 border-b border-slate-200 pb-3 mb-3">
            <div>
                <a href="{{ route('cycles.levels.show', [$level->cycle, $level]) }}" class="text-sm font-black text-slate-900 leading-tight hover:text-purple-900 transition">
                    {{ $level->name }}
                </a>
                <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider block">
                    Code : {{ $level->code }}
                </span>
            </div>
            <div class="text-right">
                <span class="text-xs font-black text-purple-900 block">
                    Σ Coeff : {{ $totalCoeffLevel }}
                </span>
                <span class="text-2xs font-bold text-slate-400 block">
                    {{ $totalHoursLevel }}h / semaine
                </span>
            </div>
        </div>

        @if($coeffs->isEmpty())
            <div class="text-center py-6 text-xs text-slate-400 font-bold italic">
                Aucun coefficient défini pour ce niveau
            </div>
        @else
            <div class="space-y-1.5 max-h-64 overflow-y-auto pr-1">
                @foreach ($coeffs as $coeff)
                    <div class="flex items-center justify-between text-xs bg-white px-2.5 py-1.5 rounded-xl border border-slate-200/70">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="font-extrabold text-slate-800 truncate">
                                {{ $coeff->subject->name ?? 'Matière inconnue' }}
                            </span>
                            <span class="text-2xs font-black text-slate-400 uppercase">
                                ({{ $coeff->subject->code ?? '-' }})
                            </span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-2xs font-black px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-900">
                                Coeff {{ $coeff->coefficient }}
                            </span>
                            <span class="text-2xs font-bold text-slate-500">
                                {{ $coeff->weekly_hours }}h
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-4 pt-3 border-t border-slate-200 flex justify-between items-center text-2xs text-slate-400 font-bold">
        <span>{{ $coeffs->count() }} matière(s)</span>
        <a href="{{ route('cycles.levels.show', [$level->cycle, $level]) }}"
           class="text-purple-900 hover:underline font-extrabold">
            Gérer les coefficients →
        </a>
    </div>
</div>
