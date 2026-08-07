<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ✏️ Modifier le Dossier de {{ $student->last_name }} {{ $student->first_name }}
            </h2>
            <a href="{{ route('students.show', $student) }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour à la fiche
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">

                @if (session('status'))
                    <div class="mb-6 px-5 py-3 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl text-sm font-bold">
                        ✅ {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('students.update', $student) }}" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Matricule (readonly) -->
                    <div class="p-4 bg-purple-50 rounded-2xl border border-purple-100 flex items-center gap-4">
                        <span class="text-2xl">🎓</span>
                        <div>
                            <div class="text-xs font-black text-purple-700 uppercase tracking-widest">Matricule</div>
                            <div class="text-xl font-black text-purple-950">{{ $student->matricule }}</div>
                        </div>
                    </div>

                    <!-- Identité -->
                    <div>
                        <h3 class="text-sm font-black text-purple-900 uppercase tracking-widest mb-4">Identité</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nom de Famille *</label>
                                <input type="text" name="last_name" required value="{{ old('last_name', $student->last_name) }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('last_name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Prénom(s) *</label>
                                <input type="text" name="first_name" required value="{{ old('first_name', $student->first_name) }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('first_name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Date de Naissance *</label>
                                <input type="date" name="birth_date" required value="{{ old('birth_date', $student->birth_date->format('Y-m-d')) }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Lieu de Naissance *</label>
                                <input type="text" name="birth_place" required value="{{ old('birth_place', $student->birth_place) }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Genre *</label>
                                <select name="gender" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="M" {{ old('gender', $student->gender) == 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('gender', $student->gender) == 'F' ? 'selected' : '' }}>Féminin</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Groupe Sanguin</label>
                                <select name="blood_group" class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">-- Inconnu --</option>
                                    @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nationalité</label>
                                <input type="text" name="nationality" value="{{ old('nationality', $student->nationality) }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('students.show', $student) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Mettre à Jour le Dossier
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
