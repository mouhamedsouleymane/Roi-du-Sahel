<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                ✏️ {{ __('Configurer le Rôle') }} — <span class="text-purple-700">{{ $role->display_name ?? $role->name }}</span>
            </h2>
            <a href="{{ route('roles.index') }}" class="text-xs font-black text-purple-900 hover:underline flex items-center gap-1">
                ← Retour aux rôles
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            @if ($isProtected)
                <div class="bg-amber-50 border border-amber-300 text-amber-900 px-5 py-4 rounded-3xl text-xs font-bold flex items-center gap-3">
                    <span class="text-xl">🛡️</span>
                    <div>
                        <span class="font-black uppercase tracking-wider">Rôle Système Essentiel :</span>
                        Ce rôle est indispensable au fonctionnement interne de la plateforme (son code système et sa suppression sont verrouillés).
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-8" x-data="{
                    toggleGroup(group) {
                        const checkboxes = document.querySelectorAll(`input[data-group='${group}']`);
                        const allChecked = Array.from(checkboxes).every(c => c.checked);
                        checkboxes.forEach(c => c.checked = !allChecked);
                    }
                }">
                    @csrf
                    @method('PATCH')

                    <!-- Informations du rôle -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-slate-100">
                        <div>
                            <label for="display_name" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Intitulé du Rôle (Affichage) *
                            </label>
                            <input type="text" id="display_name" name="display_name" required value="{{ old('display_name', $role->display_name ?? $role->name) }}"
                                   placeholder="ex: Responsable Cantine"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4" />
                            @error('display_name') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Code Système Unique (Slug) *
                            </label>
                            <input type="text" id="name" name="name" {{ $isProtected ? 'readonly' : '' }} required
                                   value="{{ old('name', $role->name) }}"
                                   class="w-full text-sm font-bold {{ $isProtected ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white' }} border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4" />
                            @if ($isProtected)
                                <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Le code des rôles système n'est pas modifiable.</span>
                            @endif
                            @error('name') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Description & Périmètre de Responsabilité
                            </label>
                            <textarea id="description" name="description" rows="2"
                                      placeholder="Précisez les prérogatives associées à ce rôle..."
                                      class="w-full text-sm font-medium border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4">{{ old('description', $role->description) }}</textarea>
                            @error('description') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Matrice des Habilitations -->
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-black text-lg text-purple-950 flex items-center gap-2">
                                    🔐 Matrice des Permissions & Habilitations
                                </h3>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">
                                    Activez ou désactivez les permissions rattachées à ce rôle.
                                </p>
                            </div>
                        </div>

                        @php
                            $rolePermissionIds = $role->permissions->pluck('id')->toArray();
                            $groupNames = [
                                'administration' => ['title' => '🏛️ Administration Système', 'color' => 'amber'],
                                'iam' => ['title' => '👥 Utilisateurs & Sécurité', 'color' => 'purple'],
                                'academique' => ['title' => '📚 Structure Académique & Classes', 'color' => 'indigo'],
                                'scolarite' => ['title' => '🎓 Scolarité & Inscriptions', 'color' => 'blue'],
                                'pedagogie' => ['title' => '📝 Pédagogie, Notes & Bulletins', 'color' => 'emerald'],
                                'finance' => ['title' => '💳 Finances & Frais Scolaires', 'color' => 'teal'],
                                'discipline' => ['title' => '⚖️ Discipline & Présences', 'color' => 'rose'],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($permissionsByGroup as $group => $permissions)
                                @php
                                    $groupMeta = $groupNames[$group] ?? ['title' => '🔹 ' . ucfirst($group), 'color' => 'slate'];
                                @endphp
                                <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-200/80 space-y-3">
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                                        <h4 class="font-black text-xs uppercase tracking-wider text-purple-950">
                                            {{ $groupMeta['title'] }}
                                        </h4>
                                        <button type="button" @click="toggleGroup('{{ $group }}')"
                                                class="text-[11px] font-extrabold text-purple-700 hover:text-purple-950 hover:underline">
                                            Tout cocher / décocher
                                        </button>
                                    </div>

                                    <div class="space-y-2.5">
                                        @foreach ($permissions as $perm)
                                            <label class="flex items-start gap-3 cursor-pointer group select-none">
                                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                       data-group="{{ $group }}"
                                                       {{ in_array($perm->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}
                                                       class="mt-1 w-4 h-4 rounded border-slate-300 text-purple-600 shadow-xs focus:ring-purple-500" />
                                                <div>
                                                    <div class="text-xs font-bold text-slate-800 group-hover:text-purple-900 transition">
                                                        {{ $perm->display_name ?? $perm->name }}
                                                    </div>
                                                    <div class="text-[10px] font-mono text-slate-400">
                                                        {{ $perm->name }}
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('roles.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-purple-950 hover:bg-purple-900 text-white text-xs font-black rounded-xl shadow-md transition flex items-center gap-2">
                            💾 Enregistrer les Modifications
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone if not protected -->
            @if (!$isProtected)
                <div class="bg-red-50/70 border border-red-200 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-xs font-black text-red-800 uppercase tracking-wider mb-1">⚠️ Zone Dangereuse</h4>
                        <p class="text-xs text-red-600 font-medium">La suppression de ce rôle est irréversible (uniquement si aucun utilisateur ne lui est attribué).</p>
                    </div>
                    <form method="POST" action="{{ route('roles.destroy', $role) }}"
                          data-confirm="Êtes-vous absolument sûr de vouloir supprimer le rôle « {{ $role->display_name ?? $role->name }} » ?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-black rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                            🗑️ Supprimer ce rôle
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
