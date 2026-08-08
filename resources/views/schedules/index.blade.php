<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Emploi du Temps Hebdomadaire') }}
            </h2>
            <form method="GET" action="{{ route('schedules.index') }}" class="flex items-center gap-2">
                <label for="class_id" class="text-xs font-semibold text-gray-600">Choisir une Classe :</label>
                <select id="class_id" name="class_id" onchange="this.form.submit()" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->level->cycle->name }} - {{ $cls->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Succès ! </strong>
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <!-- Formulaire d'ajout de créneau -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-3xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">⏱️ Ajouter un Créneau de Cours</h3>

                    <form method="POST" action="{{ route('schedules.store') }}" class="space-y-4" data-confirm="Ajouter ce créneau de cours à l'emploi du temps ?">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}" />

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="day_of_week" class="block font-medium text-sm text-gray-700">Jour de la Semaine</label>
                                <select id="day_of_week" name="day_of_week" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="LUNDI">Lundi</option>
                                    <option value="MARDI">Mardi</option>
                                    <option value="MERCREDI">Mercredi</option>
                                    <option value="JEUDI">Jeudi</option>
                                    <option value="VENDREDI">Vendredi</option>
                                    <option value="SAMEDI">Samedi</option>
                                </select>
                            </div>

                            <div>
                                <label for="start_time" class="block font-medium text-sm text-gray-700">Heure de Début</label>
                                <input type="time" id="start_time" name="start_time" required value="08:00" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>

                            <div>
                                <label for="end_time" class="block font-medium text-sm text-gray-700">Heure de Fin</label>
                                <input type="time" id="end_time" name="end_time" required value="10:00" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="subject_id" class="block font-medium text-sm text-gray-700">Matière</label>
                                <select id="subject_id" name="subject_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach ($subjects as $sbj)
                                        <option value="{{ $sbj->id }}">{{ $sbj->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="teacher_id" class="block font-medium text-sm text-gray-700">Enseignant</label>
                                <select id="teacher_id" name="teacher_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach ($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="room_number" class="block font-medium text-sm text-gray-700">Salle (Optionnel)</label>
                                <input type="text" id="room_number" name="room_number" placeholder="Salle 1" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>

                        <x-primary-button>
                            {{ __('Ajouter au Planning') }}
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Grille Emploi du Temps par Jour -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-900 mb-6">📅 Emploi du Temps de la Classe</h3>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @foreach (['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI'] as $day)
                        <div class="bg-gray-50 border border-gray-200 rounded-xl overflow-hidden">
                            <div class="bg-purple-900 text-white p-3 font-bold text-center text-sm">
                                {{ $day }}
                            </div>
                            <div class="p-3 space-y-3">
                                @forelse ($schedules[$day] ?? [] as $slot)
                                    <div class="p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                                        <div class="text-xs font-bold text-indigo-600 mb-1">
                                            {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}
                                        </div>
                                        <div class="font-bold text-sm text-gray-900">{{ $slot->subject->name }}</div>
                                        <div class="text-xs text-gray-600 mt-1">👨‍🏫 {{ $slot->teacher->user->name }}</div>
                                        <div class="text-[11px] text-gray-400 mt-1">📍 {{ $slot->room_number ?? 'Salle 1' }}</div>
                                        <div class="mt-2 flex justify-end gap-1.5">
                                            <a href="{{ route('schedules.edit', $slot) }}"
                                               class="text-[11px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 hover:bg-amber-100">
                                                ✏️ Modifier
                                            </a>
                                            <form method="POST" action="{{ route('schedules.destroy', $slot) }}" class="inline"
                                                  data-confirm="Supprimer ce créneau horaire du planning ?">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="text-[11px] font-bold px-2 py-0.5 rounded bg-red-50 text-red-700 hover:bg-red-100">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-xs text-gray-400 italic">
                                        Aucun cours
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
