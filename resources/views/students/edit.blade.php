<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('students.show', $student) }}"
                   class="p-2.5 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition">
                    ←
                </a>
                <div>
                    <h2 class="font-black text-2xl text-purple-950 leading-tight">
                        ✏️ Modifier le Dossier Élève
                    </h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">
                        Élève : <span class="text-purple-950">{{ $student->last_name }} {{ $student->first_name }}</span> ({{ $student->matricule }})
                    </p>
                </div>
            </div>
            <a href="{{ route('students.show', $student) }}" class="text-xs font-black text-purple-900 hover:underline">
                Annuler et retourner
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-xs">
                    <span class="text-lg">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-3xl p-6 sm:p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('students.update', $student) }}" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Bandeau Matricule & Statut -->
                    <div class="p-5 bg-purple-50 rounded-2xl border border-purple-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="p-3 bg-purple-900 text-amber-300 rounded-2xl text-xl shadow-xs">🎓</span>
                            <div>
                                <div class="text-[11px] font-black text-purple-700 uppercase tracking-widest">Matricule Officiel</div>
                                <div class="text-xl font-black font-mono text-purple-950">{{ $student->matricule }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-purple-800 uppercase tracking-wider mb-1">Statut Administratif</label>
                            <select name="status" class="text-xs font-black border-purple-200 bg-white rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 text-purple-950">
                                <option value="ACTIF" {{ old('status', $student->status) === 'ACTIF' ? 'selected' : '' }}>● Actif</option>
                                <option value="INACTIF" {{ old('status', $student->status) === 'INACTIF' ? 'selected' : '' }}>● Inactif</option>
                                <option value="TRANSFERE" {{ old('status', $student->status) === 'TRANSFERE' ? 'selected' : '' }}>● Transféré</option>
                                <option value="RADIE" {{ old('status', $student->status) === 'RADIE' ? 'selected' : '' }}>● Radié</option>
                            </select>
                        </div>
                    </div>

                    <!-- 1. Identité de l'Élève -->
                    <div>
                        <h3 class="text-xs font-black text-purple-950 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-900 flex items-center justify-center text-xs">1</span>
                            État Civil & Identité
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nom de Famille *</label>
                                <input type="text" name="last_name" required value="{{ old('last_name', $student->last_name) }}"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('last_name') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Prénom(s) *</label>
                                <input type="text" name="first_name" required value="{{ old('first_name', $student->first_name) }}"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('first_name') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Genre / Sexe *</label>
                                <select name="gender" required class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="M" {{ old('gender', $student->gender) === 'M' ? 'selected' : '' }}>Masculin (Garçon)</option>
                                    <option value="F" {{ old('gender', $student->gender) === 'F' ? 'selected' : '' }}>Féminin (Fille)</option>
                                </select>
                                @error('gender') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Date de Naissance *</label>
                                <input type="date" name="birth_date" required value="{{ old('birth_date', $student->birth_date ? $student->birth_date->format('Y-m-d') : '') }}"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('birth_date') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Lieu de Naissance *</label>
                                <input type="text" name="birth_place" required value="{{ old('birth_place', $student->birth_place) }}"
                                       placeholder="ex: Niamey, Commune 1"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('birth_place') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 2. Santé & Renseignements Complémentaires -->
                    <div class="border-t border-slate-100 pt-6">
                        <h3 class="text-xs font-black text-purple-950 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-900 flex items-center justify-center text-xs">2</span>
                            Santé & Historique
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Groupe Sanguin</label>
                                <select name="blood_group" class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">-- Non renseigné --</option>
                                    @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group) === $bg ? 'selected' : '' }}>
                                            {{ $bg }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">École Précédente / Provenance</label>
                                <input type="text" name="previous_school" value="{{ old('previous_school', $student->previous_school) }}"
                                       placeholder="ex: Complexe Scolaire Sahel..."
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                    Notes Médicales & Allergies / Traitements
                                </label>
                                <textarea name="medical_notes" rows="3"
                                          placeholder="Asthme, allergies alimentaires, port de lunettes, antécédents particuliers..."
                                          class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">{{ old('medical_notes', $student->medical_notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end items-center gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('students.show', $student) }}"
                           class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black uppercase tracking-wider rounded-2xl transition">
                            Annuler
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-purple-900 hover:bg-purple-800 text-amber-300 text-xs font-black uppercase tracking-wider rounded-2xl shadow-lg hover:shadow-xl transition">
                            💾 Enregistrer les Modifications
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
