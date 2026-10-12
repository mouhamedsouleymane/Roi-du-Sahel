<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('guardians.index') }}"
                   class="p-2.5 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition">
                    ←
                </a>
                <div>
                    <h2 class="font-black text-2xl text-purple-950 leading-tight">
                        ➕ Nouveau Parent / Tuteur Légal
                    </h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">
                        Enregistrement d'un responsable légal ou contact d'urgence
                    </p>
                </div>
            </div>
            <a href="{{ route('guardians.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                Retour au répertoire
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-6 sm:p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('guardians.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <h3 class="text-xs font-black text-purple-950 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-900 flex items-center justify-center text-xs">1</span>
                            Identité & Lien de Parenté
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="last_name" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nom de Famille *</label>
                                <input type="text" id="last_name" name="last_name" required value="{{ old('last_name') }}" placeholder="ex: Souley"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('last_name') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="first_name" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Prénom(s) *</label>
                                <input type="text" id="first_name" name="first_name" required value="{{ old('first_name') }}" placeholder="ex: Moussa"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('first_name') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="relationship" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Lien de Parenté *</label>
                                <select id="relationship" name="relationship" required class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="PERE" {{ old('relationship') === 'PERE' ? 'selected' : '' }}>Père</option>
                                    <option value="MERE" {{ old('relationship') === 'MERE' ? 'selected' : '' }}>Mère</option>
                                    <option value="TUTEUR_LEGAL" {{ old('relationship') === 'TUTEUR_LEGAL' ? 'selected' : '' }}>Tuteur Légal</option>
                                    <option value="ONCLE" {{ old('relationship') === 'ONCLE' ? 'selected' : '' }}>Oncle / Tante</option>
                                    <option value="AUTRE" {{ old('relationship') === 'AUTRE' ? 'selected' : '' }}>Autre proche</option>
                                </select>
                                @error('relationship') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="profession" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Profession</label>
                                <input type="text" id="profession" name="profession" value="{{ old('profession') }}" placeholder="ex: Commerçant, Enseignant, Cadre…"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6">
                        <h3 class="text-xs font-black text-purple-950 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-900 flex items-center justify-center text-xs">2</span>
                            Coordonnées de Contact
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="phone_primary" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Téléphone Principal (WhatsApp) *</label>
                                <input type="text" id="phone_primary" name="phone_primary" required value="{{ old('phone_primary') }}" placeholder="+227 90 12 34 56"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('phone_primary') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="phone_secondary" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Téléphone Secondaire</label>
                                <input type="text" id="phone_secondary" name="phone_secondary" value="{{ old('phone_secondary') }}" placeholder="+227 96 12 34 56"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Adresse Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="parent@example.com"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>

                            <div>
                                <label for="city" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Ville de Résidence</label>
                                <input type="text" id="city" name="city" value="{{ old('city', 'Niamey') }}" placeholder="Niamey"
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>

                            <div class="md:col-span-2">
                                <label for="address" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Adresse Complète / Quartier</label>
                                <input type="text" id="address" name="address" value="{{ old('address') }}" placeholder="Quartier Plateau, Rue..."
                                       class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end items-center gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('guardians.index') }}"
                           class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black uppercase tracking-wider rounded-2xl transition">
                            Annuler
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-purple-900 hover:bg-purple-800 text-amber-300 text-xs font-black uppercase tracking-wider rounded-2xl shadow-lg hover:shadow-xl transition flex items-center gap-2">
                            <span>💾</span>
                            <span>Enregistrer le Tuteur</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
