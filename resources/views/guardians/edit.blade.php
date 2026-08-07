<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ✏️ Modifier le Tuteur — {{ $guardian->first_name }} {{ $guardian->last_name }}
            </h2>
            <a href="{{ route('guardians.show', $guardian) }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour à la fiche
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('guardians.update', $guardian) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nom de Famille *</label>
                            <input type="text" name="last_name" required value="{{ old('last_name', $guardian->last_name) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Prénom(s) *</label>
                            <input type="text" name="first_name" required value="{{ old('first_name', $guardian->first_name) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Lien de Parenté *</label>
                            <select name="relationship" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach (['Père', 'Mère', 'Tuteur légal', 'Grand-père', 'Grand-mère', 'Oncle', 'Tante', 'Frère', 'Sœur', 'Autre'] as $rel)
                                    <option value="{{ $rel }}" {{ old('relationship', $guardian->relationship) == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Téléphone Principal *</label>
                            <input type="text" name="phone_primary" required value="{{ old('phone_primary', $guardian->phone_primary) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Téléphone Secondaire</label>
                            <input type="text" name="phone_secondary" value="{{ old('phone_secondary', $guardian->phone_secondary) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email', $guardian->email) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Profession</label>
                            <input type="text" name="profession" value="{{ old('profession', $guardian->profession) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Ville</label>
                            <input type="text" name="city" value="{{ old('city', $guardian->city) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Adresse Complète</label>
                            <textarea name="address" rows="2" class="w-full text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">{{ old('address', $guardian->address) }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('guardians.show', $guardian) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
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
