<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                🛡️ {{ __('Gestion des Rôles & Habilitations') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('users.index') }}"
                    class="inline-flex items-center gap-1 bg-white hover:bg-slate-100 text-purple-950 font-extrabold py-2.5 px-4 rounded-xl text-xs border border-slate-200 shadow-sm transition">
                    👥 Voir les Utilisateurs
                </a>
                <a href="{{ route('roles.create') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-purple-950 font-black py-2.5 px-4 rounded-xl text-xs shadow-lg transition transform hover:scale-105">
                    ➕ Créer un Rôle
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <!-- Alerts -->
            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-2xl font-bold text-sm shadow-sm"
                    role="alert">
                    ✅ {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-2xl font-bold text-sm shadow-sm"
                    role="alert">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-white rounded-3xl shadow-sm border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-900 font-black flex items-center justify-center text-2xl">
                        🛡️
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Rôles Définis</div>
                        <div class="text-2xl font-black text-amber-950">{{ $totalRolesCount }} Rôles</div>
                    </div>
                </div>

                <div class="p-6 bg-white rounded-3xl shadow-sm border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-900 font-black flex items-center justify-center text-2xl">
                        🔐
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Habilitations Système</div>
                        <div class="text-2xl font-black text-emerald-900">{{ $totalPermissionsCount }} Permissions</div>
                    </div>
                </div>

                <div class="p-6 bg-white rounded-3xl shadow-sm border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-900 font-black flex items-center justify-center text-2xl">
                        👤
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Utilisateurs avec Rôle</div>
                        <div class="text-2xl font-black text-purple-950">{{ $totalAssignedUsersCount }} Actif(s)</div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="p-6 bg-white shadow-sm rounded-3xl border border-slate-200/80">
                <form method="GET" action="{{ route('roles.index') }}"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="search"
                            class="block font-black text-xs text-slate-600 uppercase tracking-wider mb-1">
                            Recherche de Rôle
                        </label>
                        <input type="text" id="search" name="search" value="{{ $search }}"
                            placeholder="Rechercher par nom de rôle ou description..."
                            class="w-full text-sm font-bold rounded-xl border-purple-300 shadow-xs focus:border-purple-500 focus:ring-purple-500" />
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="px-5 py-2.5 bg-purple-950 hover:bg-purple-900 text-white font-black rounded-xl text-xs transition shadow-md">
                            🔍 Filtrer
                        </button>
                        <a href="{{ route('roles.index') }}"
                            class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs transition">
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            <!-- Roles Table -->
            <div class="p-6 bg-white shadow-sm rounded-3xl border border-slate-200/80">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Nom du Rôle
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Description & Mission
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Permissions
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Utilisateurs
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse ($roles as $r)
                                <tr class="hover:bg-purple-50/30 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base">🛡️</span>
                                            <div>
                                                <div class="font-extrabold text-purple-950">{{ $r->display_name ?? $r->name }}</div>
                                                <div class="text-[11px] font-mono text-slate-400">code: {{ $r->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 font-medium max-w-xs">
                                        {{ $r->description ?? 'Aucune description spécifiée.' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-black {{ $r->name === 'Super Admin' ? 'bg-amber-100 text-amber-900 ring-1 ring-amber-400' : 'bg-purple-100 text-purple-900' }}">
                                            {{ $r->name === 'Super Admin' ? 'Toutes (' . $totalPermissionsCount . ')' : $r->permissions_count . ' permission(s)' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-slate-800">
                                        <a href="{{ route('users.index', ['role' => $r->name]) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-slate-100 hover:bg-purple-100 text-slate-700 hover:text-purple-900 transition text-xs font-black">
                                            👥 {{ $r->users_count }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('roles.edit', $r) }}"
                                                class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 transition border border-amber-200">
                                                ✏️ Configurer
                                            </a>
                                            @if (!in_array($r->name, ['Super Admin', 'Directeur', 'Enseignant', 'Parent', 'Élève']))
                                                <form method="POST" action="{{ route('roles.destroy', $r) }}" class="inline"
                                                    data-confirm="Êtes-vous sûr de vouloir supprimer définitivement le rôle {{ $r->display_name ?? $r->name }} ?">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 transition border border-red-200">
                                                        🗑️ Supprimer
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500 italic">
                                        Aucun rôle trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $roles->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
