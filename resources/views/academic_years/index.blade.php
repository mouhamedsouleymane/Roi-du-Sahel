<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestion des Années Scolaires') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Succès ! </strong>
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <!-- Formulaire de création -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">➕ Nouvelle Année Scolaire</h3>
                    
                    <form method="POST" action="{{ route('academic-years.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">Libellé (ex: 2026-2027)</label>
                            <input type="text" id="name" name="name" required placeholder="2026-2027" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block font-medium text-sm text-gray-700">Date de Début</label>
                                <input type="date" id="start_date" name="start_date" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                            <div>
                                <label for="end_date" class="block font-medium text-sm text-gray-700">Date de Fin</label>
                                <input type="date" id="end_date" name="end_date" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block font-medium text-sm text-gray-700">Description / Remarques</label>
                            <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <label for="is_active" class="text-sm text-gray-700 font-medium">Définir immédiatement comme Année Active</label>
                        </div>

                        <x-primary-button>
                            {{ __('Créer l\'Année Scolaire') }}
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Liste des Années Scolaires -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">📅 Historique des Années Scolaires</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Année Scolaire</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut Actif</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">État Clôture</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($academicYears as $year)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ $year->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        Du {{ $year->start_date->format('d/m/Y') }} au {{ $year->end_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($year->is_active)
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                ✅ Année Active
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($year->is_closed)
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                                🔒 Clôturée
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                Ouverte
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        @if (! $year->is_active)
                                            <form method="POST" action="{{ route('academic-years.activate', $year) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1 px-3 rounded">
                                                    Activer
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('academic-years.toggle-close', $year) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xs bg-gray-600 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded">
                                                {{ $year->is_closed ? 'Réouvrir' : 'Clôturer' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Aucune année scolaire enregistrée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
