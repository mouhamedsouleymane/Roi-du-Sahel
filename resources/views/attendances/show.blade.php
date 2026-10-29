<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight">{{ $schoolClass->name }}</h2>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">{{ $schoolClass->level->cycle->name }} · {{ $activeYear?->name ?? '–' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('attendances.index') }}" class="px-4 py-2.5 text-xs font-black bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition shadow-md">
                    Retour
                </a>
                <a href="{{ route('attendances.create', ['class_id' => $schoolClass->id]) }}" class="px-4 py-2.5 text-xs font-black bg-purple-900 text-white rounded-xl hover:bg-purple-800 transition shadow-md">
                    + Nouvelle Feuille
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if ($sheets->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center">
                    <p class="text-sm text-slate-400 italic">Aucune feuille d'appel enregistrée pour cette classe.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach ($sheets as $weekStart => $weekDates)
                    @php
                        $weekEnd = \Carbon\Carbon::parse($weekStart)->endOfWeek();
                    @endphp
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-5 flex flex-col gap-3 hover:shadow-md transition">
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Semaine</p>
                            <p class="text-base font-black text-purple-950 leading-snug mt-0.5">
                                {{ \Carbon\Carbon::parse($weekStart)->translatedFormat('d M') }}
                                — {{ $weekEnd->translatedFormat('d M') }}
                            </p>
                            <p class="text-[11px] font-semibold text-slate-400 mt-0.5">
                                {{ $weekDates->count() }} jour{{ $weekDates->count() > 1 ? 's' : '' }} enregistré{{ $weekDates->count() > 1 ? 's' : '' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 pt-1 border-t border-slate-100 mt-auto">
                            <a href="{{ route('attendances.sheet', ['class_id' => $schoolClass->id, 'week_start' => $weekStart]) }}"
                               class="flex-1 text-center text-[11px] font-bold px-2 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                Voir
                            </a>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
