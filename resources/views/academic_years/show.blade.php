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
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if(session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('status') }}
                </div>
            @endif

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
                            <span class="px-4 py-2 rounded-full text-xs font-black bg-emerald-500 text-white shadow-lg">
                                ✨ Année Active
                            </span>
                        @else
                            <span class="px-4 py-2 rounded-full text-xs font-bold bg-white/10 text-slate-300 border border-white/20">
                                Inactive
                            </span>
                        @endif
                        @if($academicYear->is_closed)
                            <span class="px-4 py-2 rounded-full text-xs font-black bg-red-500 text-white shadow-lg">
                                🔒 Clôturée
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">
                        📌 Trimestres & Périodes ({{ $academicYear->periods->count() }})
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @forelse ($academicYear->periods as $period)
                            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2">
                                <div class="font-black text-slate-900 text-sm">{{ $period->name }}</div>
                                <div class="text-xs text-slate-500 font-semibold">
                                    {{ $period->start_date?->format('d/m/Y') }} — {{ $period->end_date?->format('d/m/Y') }}
                                </div>
                                <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-full {{ $period->is_closed ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $period->is_closed ? 'Clôturé' : 'En cours' }}
                                </span>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-6 text-sm text-slate-400 italic">
                                Aucun trimestre défini pour cette année scolaire.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
