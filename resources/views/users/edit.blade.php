<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                ✏️ {{ __('Modifier le Compte') }} — <span class="text-purple-700">{{ $user->name }}</span>
            </h2>
            <a href="{{ route('users.index') }}" class="text-xs font-black text-purple-900 hover:underline flex items-center gap-1">
                ← Retour à la liste
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Nom Complet de l'Utilisateur *
                        </label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}" placeholder="ex: Souleymane Oumarou"
                               class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4" />
                        @error('name') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="email" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Adresse Email *
                            </label>
                            <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}" placeholder="utilisateur@roisdusahel.ne"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4" />
                            @error('email') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Numéro de Téléphone (Connexion) *
                            </label>
                            <input type="tel" id="phone" name="phone" required value="{{ old('phone', $user->phone) }}" placeholder="12345678"
                                   maxlength="8" pattern="[0-9]{8}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4" />
                            @error('phone') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Nouveau Mot de Passe
                                <span class="normal-case font-semibold text-slate-400 ml-1">(laisser vide pour ne pas changer)</span>
                            </label>
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4" />
                            @error('password') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="role" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Rôle & Habilitations *
                            </label>
                            <select id="role" name="role" required class="w-full text-xs font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4">
                                <option value="">-- Sélectionner un rôle --</option>
                                @foreach ($roles as $r)
                                    @php $currentRole = $user->roles->first()?->name; @endphp
                                    <option value="{{ $r->name }}" {{ (old('role', $currentRole) === $r->name) ? 'selected' : '' }}>
                                        🛡️ {{ $r->display_name ?? $r->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-purple-950 hover:bg-purple-900 text-white text-xs font-black rounded-xl shadow-md transition flex items-center gap-2">
                            💾 Enregistrer les Modifications
                        </button>
                    </div>
                </form>
            </div>

            {{-- Danger Zone: Delete account (Separate card) --}}
            @if ($user->id !== auth()->id())
                <div class="bg-red-50/70 border border-red-200 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-xs font-black text-red-800 uppercase tracking-wider mb-1">⚠️ Zone Dangereuse</h4>
                        <p class="text-xs text-red-600 font-medium">La suppression de ce compte utilisateur est définitive.</p>
                    </div>
                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                          data-confirm="Êtes-vous absolument sûr de vouloir supprimer le compte de {{ $user->name }} ?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-black rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                            🗑️ Supprimer ce compte
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
