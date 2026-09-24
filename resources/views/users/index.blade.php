<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                👥 {{ __('Gestion des Utilisateurs & Habilitations') }}
            </h2>
            <a href="{{ route('users.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-purple-950 font-black py-2.5 px-4 rounded-xl text-xs shadow-lg transition transform hover:scale-105">
                ➕ Créer un Utilisateur
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                        class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-900 font-black flex items-center justify-center text-2xl">
                        👤
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Comptes Utilisateurs
                        </div>
                        <div class="text-2xl font-black text-purple-950">{{ $users->total() }}</div>
                    </div>
                </div>

                <div class="p-6 bg-white rounded-3xl shadow-sm border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-900 font-black flex items-center justify-center text-2xl">
                        🛡️
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Rôles Configuration
                        </div>
                        <div class="text-2xl font-black text-amber-900">{{ $totalRolesCount }} Rôles Défini(s)</div>
                    </div>
                </div>

                <div class="p-6 bg-white rounded-3xl shadow-sm border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-900 font-black flex items-center justify-center text-2xl">
                        🔐
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Permissions Système
                        </div>
                        <div class="text-2xl font-black text-emerald-900">{{ $totalPermissionsCount }} Habilitations
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="p-6 bg-white shadow-sm rounded-3xl border border-slate-200/80">
                <form method="GET" action="{{ route('users.index') }}"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label for="search"
                            class="block font-black text-xs text-slate-600 uppercase tracking-wider mb-1">
                            Recherche (Nom, Email)
                        </label>
                        <input type="text" id="search" name="search" value="{{ $search }}"
                            placeholder="Rechercher un nom ou email..."
                            class="w-full text-sm font-bold rounded-xl border-slate-300 shadow-xs focus:border-purple-500 focus:ring-purple-500" />
                    </div>

                    <div>
                        <label for="role" class="block font-black text-xs text-slate-600 uppercase tracking-wider mb-1">
                            Filtrer par Rôle
                        </label>
                        <select id="role" name="role"
                            class="w-full text-sm font-bold rounded-xl border-slate-300 shadow-xs focus:border-purple-500 focus:ring-purple-500">
                            <option value="">-- Tous les rôles --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->name }}" {{ $roleFilter === $r->name ? 'selected' : '' }}>
                                    {{ $r->display_name ?? $r->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="px-5 py-2.5 bg-purple-950 hover:bg-purple-900 text-white font-black rounded-xl text-xs transition shadow-md">
                            🔍 Filtrer
                        </button>
                        <a href="{{ route('users.index') }}"
                            class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs transition">
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="p-6 bg-white shadow-sm rounded-3xl border border-slate-200/80">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Utilisateur</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Email</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Rôle(s) Attribué(s)</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Créé le</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-black text-slate-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse ($users as $u)
                                <tr class="hover:bg-purple-50/30 transition">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-purple-900 text-amber-400 font-black flex items-center justify-center text-xs shadow-md ring-2 ring-purple-200">
                                            {{ mb_substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-purple-950">{{ $u->name }}</div>
                                            @if ($u->id === Auth::id())
                                                <span
                                                    class="text-[10px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Votre
                                                    compte</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 font-medium">
                                        {{ $u->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($u->roles as $userRole)
                                                <span
                                                    class="px-3 py-1 text-xs font-black rounded-full shadow-xs
                                                                    {{ $userRole->name === 'Super Admin' ? 'bg-amber-400 text-purple-950 border border-amber-500' : '' }}
                                                                    {{ $userRole->name === 'Directeur' || $userRole->name === 'Censeur' ? 'bg-purple-900 text-white' : '' }}
                                                                    {{ $userRole->name === 'Enseignant' ? 'bg-blue-100 text-blue-900' : '' }}
                                                                    {{ $userRole->name === 'Parent' ? 'bg-emerald-100 text-emerald-900' : '' }}
                                                                    {{ !in_array($userRole->name, ['Super Admin', 'Directeur', 'Censeur', 'Enseignant', 'Parent']) ? 'bg-slate-100 text-slate-800' : '' }}">
                                                    🛡️ {{ $userRole->display_name ?? $userRole->name }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-slate-400 italic">Aucun rôle</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-semibold">
                                        {{ $u->created_at ? $u->created_at->format('d/m/Y') : '–' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('users.edit', $u) }}"
                                                class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 transition border border-amber-200">
                                                ✏️ Modifier Rôle
                                            </a>
                                            @if ($u->id !== Auth::id())
                                                <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline"
                                                    data-confirm="Êtes-vous sûr de vouloir supprimer définitivement le compte utilisateur {{ $u->name }} ({{ $u->email }}) ?">
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
                                        Aucun utilisateur trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>