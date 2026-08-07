<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ➕ Ajouter un Enseignant
            </h2>
            <a href="{{ route('teachers.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour au répertoire
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('teachers.store') }}" class="space-y-8">
                    @csrf

                    <!-- Compte Utilisateur -->
                    <div>
                        <h3 class="text-sm font-black text-purple-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-xs">1</span>
                            Compte d'Accès
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nom Complet *</label>
                                <input type="text" name="name" required value="{{ old('name') }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Adresse Email *</label>
                                <input type="email" name="email" required value="{{ old('email') }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Mot de Passe *</label>
                                <input type="password" name="password" required
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('password') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Confirmer le Mot de Passe *</label>
                                <input type="password" name="password_confirmation" required
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                        </div>
                    </div>

                    <!-- Informations Professionnelles -->
                    <div class="border-t border-slate-100 pt-6">
                        <h3 class="text-sm font-black text-purple-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center text-xs">2</span>
                            Profil Professionnel
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Spécialité Principale *</label>
                                <input type="text" name="speciality" required value="{{ old('speciality') }}" placeholder="ex: Mathématiques, Physique-Chimie"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('speciality') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Qualification / Diplôme</label>
                                <input type="text" name="qualification" value="{{ old('qualification') }}" placeholder="ex: Master 2, CAPES"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Type de Contrat *</label>
                                <select name="employment_type" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">-- Choisir --</option>
                                    <option value="PERMANENT" {{ old('employment_type') == 'PERMANENT' ? 'selected' : '' }}>Permanent</option>
                                    <option value="VACATAIRE" {{ old('employment_type') == 'VACATAIRE' ? 'selected' : '' }}>Vacataire</option>
                                    <option value="REMPLACANT" {{ old('employment_type') == 'REMPLACANT' ? 'selected' : '' }}>Remplaçant</option>
                                </select>
                                @error('employment_type') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Téléphone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Date de Recrutement</label>
                                <input type="date" name="hire_date" value="{{ old('hire_date') }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('teachers.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Enregistrer l'Enseignant
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
