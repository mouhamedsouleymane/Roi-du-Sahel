<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">Présences & Absences</h2>
            <a href="{{ route('attendances.report') }}" class="px-4 py-2.5 text-xs font-black bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition shadow-md">
                Bilan & Rapport
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-10 animate-fade-in-up">

            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-2xl">
                    <strong>Succès !</strong> {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-2xl">{{ session('error') }}</div>
            @endif

            {{-- Hero banner --}}
            <div class="relative bg-purple-950 rounded-3xl overflow-hidden px-8 py-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="absolute inset-0 opacity-10"
                     style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
                <div class="relative">
                    <p class="text-purple-300 text-xs font-black uppercase tracking-widest mb-1">{{ $activeYear?->name ?? 'Aucune année active' }}</p>
                    <h3 class="text-white text-3xl font-black leading-tight">Registre des<br>présences</h3>
                    <p class="text-purple-300 text-sm font-semibold mt-2">Sélectionnez une classe pour consulter ou saisir les feuilles d'appel.</p>
                </div>
                <div class="relative flex items-center gap-3">
                    @php $totalClasses = $classes->flatten()->count(); @endphp
                    <div class="bg-white/10 rounded-2xl px-6 py-4 text-center backdrop-blur-sm">
                        <div class="text-3xl font-black text-white">{{ $totalClasses }}</div>
                        <div class="text-[11px] font-bold text-purple-300 uppercase mt-0.5">Classe{{ $totalClasses > 1 ? 's' : '' }}</div>
                    </div>
                    <div class="bg-white/10 rounded-2xl px-6 py-4 text-center backdrop-blur-sm">
                        <div class="text-3xl font-black text-white">{{ $classes->count() }}</div>
                        <div class="text-[11px] font-bold text-purple-300 uppercase mt-0.5">Cycle{{ $classes->count() > 1 ? 's' : '' }}</div>
                    </div>
                </div>
            </div>

            {{-- Grille par cycle --}}
            @php
                $cycleColors = [
                    0 => ['dot' => 'bg-violet-500', 'badge' => 'bg-violet-100 text-violet-700', 'hover' => 'hover:border-violet-200 hover:shadow-violet-100'],
                    1 => ['dot' => 'bg-sky-500',    'badge' => 'bg-sky-100 text-sky-700',       'hover' => 'hover:border-sky-200 hover:shadow-sky-100'],
                    2 => ['dot' => 'bg-emerald-500','badge' => 'bg-emerald-100 text-emerald-700','hover' => 'hover:border-emerald-200 hover:shadow-emerald-100'],
                    3 => ['dot' => 'bg-amber-500',  'badge' => 'bg-amber-100 text-amber-700',   'hover' => 'hover:border-amber-200 hover:shadow-amber-100'],
                ];
                $ci = 0;
            @endphp

            @forelse ($classes as $cycleName => $cycleClasses)
                @php $color = $cycleColors[$ci % count($cycleColors)]; $ci++; @endphp
                <div>
                    {{-- En-tête cycle --}}
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-2.5 h-2.5 rounded-full {{ $color['dot'] }}"></span>
                        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">{{ $cycleName }}</h3>
                        <span class="px-2 py-0.5 text-[11px] font-black rounded-full {{ $color['badge'] }}">
                            {{ $cycleClasses->count() }} classe{{ $cycleClasses->count() > 1 ? 's' : '' }}
                        </span>
                        <div class="flex-1 h-px bg-slate-200"></div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                        @foreach ($cycleClasses as $class)
                            <a href="{{ route('attendances.show', ['class_id' => $class->id]) }}"
                               class="group bg-white rounded-3xl shadow-sm border border-slate-200/80 p-5 flex flex-col gap-3 hover:shadow-lg transition-all duration-200 {{ $color['hover'] }}">

                                {{-- Icône initiale --}}
                                <div class="w-12 h-12 rounded-2xl {{ $color['badge'] }} flex items-center justify-center shrink-0">
                                    <span class="text-xl font-black">{{ mb_strtoupper(mb_substr($class->name, 0, 1)) }}</span>
                                </div>

                                <div>
                                    <p class="text-sm font-black text-slate-900 group-hover:text-purple-950 transition leading-tight">
                                        {{ $class->name }}
                                    </p>
                                    <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $class->level->name }}</p>
                                </div>

                                <div class="flex items-center gap-1 text-[11px] font-black text-slate-400 group-hover:text-purple-700 transition mt-auto">
                                    <span>Voir les feuilles</span>
                                    <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-slate-200/80 p-16 text-center">
                    <p class="text-sm text-slate-400 italic">Aucune classe pour l'année scolaire active.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
