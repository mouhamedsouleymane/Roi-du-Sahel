<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>✏️</span> Modifier l'Affectation
                </h2>
                <p class="text-xs font-bold text-slate-500 mt-1">
                    Modification de l'affectation :
                    <span class="text-purple-950 font-extrabold">
                        {{ $assignment->teacher->user->name }} · {{ $assignment->subject->name }} · {{ $assignment->schoolClass->name }}
                    </span>
                </p>
            </div>
            <a href="{{ route('assignments.index') }}"
                class="px-4 py-2 text-xs font-black bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition shadow-sm flex items-center gap-1.5">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto space-y-6 animate-fade-in-up">

            {{-- Règle pédagogique --}}
            <div class="p-4 bg-purple-50 border border-purple-200/80 rounded-2xl flex items-start gap-3 text-xs text-purple-950 shadow-xs">
                <span class="text-lg mt-0.5">💡</span>
                <div>
                    <strong class="font-extrabold block text-sm text-purple-950">Règle pédagogique par cycle :</strong>
                    <p class="font-medium text-purple-900 mt-0.5">
                        Les enseignants <strong>Maternelle</strong> et <strong>Primaire</strong> ne peuvent être affectés qu'à
                        <strong>une seule classe</strong> par année. Les enseignants <strong>Collège</strong> et <strong>Lycée</strong>
                        peuvent couvrir plusieurs classes.
                    </p>
                </div>
            </div>

            {{-- Formulaire de modification --}}
            <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                {{-- Header de la carte --}}
                <div class="px-8 py-5 bg-gradient-to-r from-purple-950 to-indigo-950 flex items-center gap-3">
                    <span class="text-2xl">🔗</span>
                    <div>
                        <div class="text-sm font-black text-white">Modification de l'affectation</div>
                        <div class="text-xs font-bold text-purple-300 mt-0.5">ID #{{ $assignment->id }}</div>
                    </div>
                </div>

                <div class="p-8">
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl text-sm font-bold space-y-1 mb-6">
                            <div class="flex items-center gap-2 text-red-900 font-extrabold text-base">
                                <span>⚠️</span>
                                <span>Erreur lors de la modification</span>
                            </div>
                            <ul class="list-disc list-inside text-xs text-red-700 font-semibold space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('assignments.update', $assignment) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Enseignant --}}
                        <div>
                            <label for="teacher_id" class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                                Enseignant *
                            </label>
                            <select id="teacher_id" name="teacher_id" required
                                class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="">-- Choisir l'enseignant --</option>
                                @foreach ($teachers as $teacher)
                                    @php
                                        $cycleName = $teacher->cycle?->name ?? 'Sans cycle';
                                        $isRestricted = $teacher->isRestrictedToOneClass();
                                    @endphp
                                    <option value="{{ $teacher->id }}"
                                        {{ old('teacher_id', $assignment->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->user->name }} · {{ $cycleName }} ({{ $teacher->speciality }})
                                        {{ $isRestricted ? ' — [1 classe max]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Classe & Matière --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="class_id" class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                                    Classe *
                                </label>
                                <select id="class_id" name="class_id" required
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">-- Choisir la classe --</option>
                                    @foreach ($classes as $cls)
                                        <option value="{{ $cls->id }}"
                                            {{ old('class_id', $assignment->class_id) == $cls->id ? 'selected' : '' }}>
                                            {{ $cls->level->cycle->name }} — {{ $cls->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="subject_id" class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                                    Matière *
                                </label>
                                <select id="subject_id" name="subject_id" required
                                    class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">-- Choisir la matière --</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }} ({{ $subject->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                            <a href="{{ route('assignments.show', $assignment) }}"
                                class="text-xs font-bold text-slate-500 hover:text-purple-900 transition">
                                👁️ Voir le détail
                            </a>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('assignments.index') }}"
                                    class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                    Annuler
                                </a>
                                <button type="submit"
                                    class="px-6 py-2.5 bg-purple-900 hover:bg-purple-800 text-amber-300 text-xs font-black uppercase tracking-wider rounded-2xl shadow-md transition flex items-center gap-1.5">
                                    <span>💾</span>
                                    <span>Enregistrer les Modifications</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
