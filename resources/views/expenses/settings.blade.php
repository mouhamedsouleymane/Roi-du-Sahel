<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-purple-700 font-bold uppercase tracking-wider mb-1">
                    <a href="{{ route('expenses.index') }}" class="hover:underline">💸 Dépenses</a>
                    <span>/</span><span class="text-slate-500">Catégories</span>
                </div>
                <h2 class="font-black text-2xl text-purple-950">⚙️ Catégories de Dépenses</h2>
            </div>
            <a href="{{ route('expenses.index') }}"
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                ← Retour aux Dépenses
            </a>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen bg-slate-50" x-data="{ editModalOpen: false, selectedCat: {} }">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 p-4 rounded-xl shadow-xs flex items-center gap-2 font-bold text-sm mb-5">
                    <span>✅</span> <span>{{ session('status') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-900 p-4 rounded-xl mb-5">
                    <ul class="list-disc list-inside text-xs font-semibold space-y-0.5">
                        @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Formulaire Créer --}}
                <div class="lg:col-span-1 bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                        <span class="p-1.5 rounded-lg bg-red-100 text-red-700 text-sm">🗂️</span>
                        <h3 class="text-sm font-black text-purple-950 uppercase tracking-wide">Nouvelle Catégorie</h3>
                    </div>
                    <form method="POST" action="{{ route('expense-categories.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Code Unique <span class="text-red-500">*</span></label>
                            <input type="text" name="code" required placeholder="Ex: SALAIRES, FOURNITURES…"
                                   class="w-full text-xs font-mono uppercase font-black rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nom <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="Ex: Salaires Enseignants"
                                   class="w-full text-xs font-bold rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full text-xs rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Couleur d'affichage</label>
                            <input type="color" name="color" value="#6366f1"
                                   class="h-9 w-full rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs cursor-pointer" />
                        </div>
                        <button type="submit"
                                class="w-full py-2.5 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                            ➕ Créer la Catégorie
                        </button>
                    </form>
                </div>

                {{-- Liste des catégories --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h3 class="text-sm font-black text-purple-950 uppercase tracking-wide">Catégories Existantes</h3>
                        <p class="text-xs text-slate-400">{{ $categories->count() }} catégorie(s) configurée(s)</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 font-black uppercase text-[10px] tracking-wider border-b border-slate-200">
                                    <th class="py-3 px-4">Catégorie</th>
                                    <th class="py-3 px-4">Code</th>
                                    <th class="py-3 px-4 text-center">Dépenses</th>
                                    <th class="py-3 px-4 text-center">Statut</th>
                                    <th class="py-3 px-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-800 bg-white">
                                @forelse ($categories as $cat)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2">
                                                <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $cat->color }};"></span>
                                                <span class="font-extrabold text-slate-900">{{ $cat->name }}</span>
                                            </div>
                                            @if ($cat->description)
                                                <p class="text-[10px] text-slate-500 mt-0.5 ml-5">{{ $cat->description }}</p>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 font-mono text-[10px] font-black rounded">{{ $cat->code }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-900 rounded font-bold text-[10px]">{{ $cat->expenses_count }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <form method="POST" action="{{ route('expense-categories.toggle', $cat) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="px-2 py-0.5 rounded-full font-black text-[10px] transition {{ $cat->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                                    {{ $cat->is_active ? '● Actif' : '○ Inactif' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button"
                                                        @click="selectedCat = {{ Js::from($cat) }}; editModalOpen = true"
                                                        class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg transition text-xs">✏️</button>
                                                <form method="POST" action="{{ route('expense-categories.destroy', $cat) }}"
                                                      onsubmit="return confirm('Supprimer cette catégorie ?');" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" {{ $cat->expenses_count > 0 ? 'disabled' : '' }}
                                                            class="p-1.5 rounded-lg transition text-xs {{ $cat->expenses_count > 0 ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-red-50 hover:bg-red-100 text-red-700' }}">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-10 text-center text-slate-400 text-xs font-bold">
                                            Aucune catégorie créée. Utilisez le formulaire pour en ajouter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Edit Catégorie --}}
        <div x-show="editModalOpen" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200"
                 @click.away="editModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="font-black text-sm text-purple-950 uppercase tracking-wide">✏️ Modifier la Catégorie</h3>
                    <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 font-black text-lg">&times;</button>
                </div>
                <form :action="'{{ url('/expense-categories') }}/' + selectedCat.id" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Code</label>
                        <input type="text" name="code" x-model="selectedCat.code" required
                               class="w-full text-xs font-mono uppercase font-black rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nom</label>
                        <input type="text" name="name" x-model="selectedCat.name" required
                               class="w-full text-xs font-bold rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                        <textarea name="description" x-model="selectedCat.description" rows="2"
                                  class="w-full text-xs rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Couleur</label>
                        <input type="color" name="color" x-model="selectedCat.color"
                               class="h-9 w-full rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs cursor-pointer" />
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="flex-1 py-2.5 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl transition">💾 Enregistrer</button>
                        <button type="button" @click="editModalOpen = false"
                                class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
