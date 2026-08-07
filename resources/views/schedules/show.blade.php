<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                📅 Détail Créneau
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('schedules.edit', $schedule) }}" class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm">
                    ✏️ Modifier
                </a>
                <a href="{{ route('schedules.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white">
                    <div class="text-xs font-black text-amber-400 uppercase tracking-widest">{{ $schedule->day_of_week }}</div>
                    <h1 class="text-2xl font-black text-white mt-1">
                        {{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}
                    </h1>
                    <p class="text-purple-200 text-sm mt-0.5">
                        {{ $schedule->schoolClass->name }} — {{ $schedule->subject->name }}
                    </p>
                </div>

                <div class="p-8 grid grid-cols-2 gap-6">
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Classe</div>
                        <div class="text-base font-black text-slate-900 mt-1">{{ $schedule->schoolClass->name }}</div>
                        <div class="text-xs text-slate-500">{{ $schedule->schoolClass->level->cycle->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Matière</div>
                        <div class="text-base font-black text-slate-900 mt-1">{{ $schedule->subject->name }}</div>
                        <div class="text-xs text-slate-500">{{ $schedule->subject->code }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Enseignant</div>
                        <div class="text-base font-black text-slate-900 mt-1">{{ $schedule->teacher->user->name }}</div>
                        <div class="text-xs text-slate-500">{{ $schedule->teacher->speciality }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Salle</div>
                        <div class="text-base font-black text-slate-900 mt-1">{{ $schedule->room_number ?? 'Non définie' }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
