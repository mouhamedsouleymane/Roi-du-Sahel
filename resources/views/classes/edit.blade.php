<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ✏️ Modifier la Classe {{ $schoolClass->name }}
            </h2>
            <a href="{{ route('classes.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour aux classes
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('classes.update', $schoolClass) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Nom de la Classe
                        </label>
                        <input type="text" name="name" required value="{{ old('name', $schoolClass->name) }}"
                               class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Niveau Pédagogique
                            </label>
                            <select name="level_id" required class="w-full text-xs font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}" {{ old('level_id', $schoolClass->level_id) == $level->id ? 'selected' : '' }}>
                                        {{ $level->cycle->name }} — {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('level_id') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Capacité d'élèves
                            </label>
                            <input type="number" name="capacity" min="1" max="100" value="{{ old('capacity', $schoolClass->capacity ?? 40) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Numéro / Salle
                            </label>
                            <input type="text" name="room_number" value="{{ old('room_number', $schoolClass->room_number) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Professeur Principal
                            </label>
                            <select name="main_teacher_id" class="w-full text-xs font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="">-- Aucun --</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('main_teacher_id', $schoolClass->main_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->user->name ?? 'Enseignant N°' . $teacher->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('classes.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Mettre à jour
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
