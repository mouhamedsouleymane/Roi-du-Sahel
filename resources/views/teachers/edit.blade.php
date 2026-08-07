<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ✏️ Modifier le Profil de {{ $teacher->user->name }}
            </h2>
            <a href="{{ route('teachers.show', $teacher) }}" class="text-xs font-black text-purple-900 hover:underline">
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

                <!-- Infos Professionnelles -->
                <form method="POST" action="{{ route('teachers.update', $teacher) }}" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Matricule (readonly) -->
                    <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center gap-4">
                        <span class="text-2xl">👨‍🏫</span>
                        <div>
                            <div class="text-xs font-black text-indigo-700 uppercase tracking-widest">Matricule Enseignant</div>
                            <div class="text-xl font-black text-indigo-950">{{ $teacher->matricule }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nom Complet *</label>
                            <input type="text" name="name" required value="{{ old('name', $teacher->user->name) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email', $teacher->user->email) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Spécialité *</label>
                            <input type="text" name="speciality" required value="{{ old('speciality', $teacher->speciality) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Qualification</label>
                            <input type="text" name="qualification" value="{{ old('qualification', $teacher->qualification) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Type de Contrat *</label>
                            <select name="employment_type" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="PERMANENT" {{ old('employment_type', $teacher->employment_type) == 'PERMANENT' ? 'selected' : '' }}>Permanent</option>
                                <option value="VACATAIRE" {{ old('employment_type', $teacher->employment_type) == 'VACATAIRE' ? 'selected' : '' }}>Vacataire</option>
                                <option value="REMPLACANT" {{ old('employment_type', $teacher->employment_type) == 'REMPLACANT' ? 'selected' : '' }}>Remplaçant</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Date de Recrutement</label>
                            <input type="date" name="hire_date" value="{{ old('hire_date', $teacher->hire_date?->format('Y-m-d')) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('teachers.show', $teacher) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Mettre à Jour le Profil
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
