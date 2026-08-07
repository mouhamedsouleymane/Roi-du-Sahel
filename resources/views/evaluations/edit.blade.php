<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ✏️ Modifier l'Évaluation — {{ $evaluation->title }}
            </h2>
            <a href="{{ route('evaluations.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour aux évaluations
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('evaluations.update', $evaluation) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Intitulé *</label>
                        <input type="text" name="title" required value="{{ old('title', $evaluation->title) }}"
                               class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        @error('title') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Classe *</label>
                            <select name="class_id" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach ($classes as $cls)
                                    <option value="{{ $cls->id }}" {{ old('class_id', $evaluation->class_id) == $cls->id ? 'selected' : '' }}>
                                        {{ $cls->level->cycle->name }} — {{ $cls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Période *</label>
                            <select name="period_id" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach ($periods as $period)
                                    <option value="{{ $period->id }}" {{ old('period_id', $evaluation->period_id) == $period->id ? 'selected' : '' }}>
                                        {{ $period->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Matière *</label>
                            <select name="subject_id" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $evaluation->subject_id) == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Date *</label>
                            <input type="date" name="evaluation_date" required value="{{ old('evaluation_date', $evaluation->evaluation_date->format('Y-m-d')) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Type *</label>
                            <select name="type" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="DEVOIR" {{ old('type', $evaluation->type) == 'DEVOIR' ? 'selected' : '' }}>Devoir Surveillé (DS)</option>
                                <option value="COMPOSITION" {{ old('type', $evaluation->type) == 'COMPOSITION' ? 'selected' : '' }}>Composition</option>
                                <option value="EXAMEN" {{ old('type', $evaluation->type) == 'EXAMEN' ? 'selected' : '' }}>Examen</option>
                                <option value="INTERROGATION" {{ old('type', $evaluation->type) == 'INTERROGATION' ? 'selected' : '' }}>Interrogation Orale</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Note Maximale</label>
                            <input type="number" name="max_score" min="1" max="100" value="{{ old('max_score', $evaluation->max_score) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Coefficient</label>
                            <input type="number" name="coefficient" min="1" max="10" value="{{ old('coefficient', $evaluation->coefficient) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('evaluations.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Mettre à Jour
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
