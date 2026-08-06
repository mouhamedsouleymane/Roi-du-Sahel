<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Registre des Élèves') }}
            </h2>
            <a href="{{ route('enrollments.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm shadow">
                ➕ Inscrire un Élève
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filtres de recherche -->
            <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
                <form method="GET" action="{{ route('students.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block font-medium text-xs text-gray-600 mb-1">Rechercher (Matricule, Nom, Prénom)</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" placeholder="ex: RS-2025-0001 ou Souleymane..." class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label for="class_id" class="block font-medium text-xs text-gray-600 mb-1">Filtrer par Classe</label>
                        <select id="class_id" name="class_id" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Toutes les classes --</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->level->cycle->name }} - {{ $cls->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-md text-sm">
                            Filtrer
                        </button>
                        <a href="{{ route('students.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-md text-sm">
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            <!-- Liste des Élèves -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matricule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom & Prénom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sexe / Né(e) le</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe Actuelle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tuteur Principal</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($students as $student)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                        {{ $student->matricule }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ $student->last_name }} {{ $student->first_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="font-bold {{ $student->gender === 'M' ? 'text-blue-600' : 'text-pink-600' }}">
                                            {{ $student->gender }}
                                        </span>
                                        • {{ $student->birth_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800">
                                            {{ $student->currentEnrollment?->schoolClass?->name ?? 'Non inscrit' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        @php $primaryGuardian = $student->guardians->first(); @endphp
                                        @if ($primaryGuardian)
                                            <div><strong>{{ $primaryGuardian->first_name }} {{ $primaryGuardian->last_name }}</strong></div>
                                            <div class="text-xs text-gray-400">{{ $primaryGuardian->phone_primary }}</div>
                                        @else
                                            <span class="text-gray-400 italic">Aucun</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('students.show', $student) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                            Voir Fiche →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Aucun élève trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $students->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
