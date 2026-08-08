<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Affectation des Enseignants aux Classes & Matières') }}
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

            <!-- Formulaire d'Affectation -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">🔗 Attribuer une Matière et une Classe à un Enseignant</h3>

                    <form method="POST" action="{{ route('assignments.store') }}" class="space-y-4" data-confirm="Êtes-vous sûr de vouloir enregistrer cette affectation d'enseignant ?">
                        @csrf

                        <div>
                            <label for="teacher_id" class="block font-medium text-sm text-gray-700">Enseignant</label>
                            <select id="teacher_id" name="teacher_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Choisir l'enseignant --</option>
                                @foreach ($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->user->name }} (Spécialité : {{ $t->speciality }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="class_id" class="block font-medium text-sm text-gray-700">Classe</label>
                                <select id="class_id" name="class_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Choisir la classe --</option>
                                    @foreach ($classes as $cls)
                                        <option value="{{ $cls->id }}">{{ $cls->level->cycle->name }} - {{ $cls->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="subject_id" class="block font-medium text-sm text-gray-700">Matière</label>
                                <select id="subject_id" name="subject_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Choisir la matière --</option>
                                    @foreach ($subjects as $sbj)
                                        <option value="{{ $sbj->id }}">{{ $sbj->name }} ({{ $sbj->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <x-primary-button>
                            {{ __('Enregistrer l\'Affectation') }}
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Matrice des Affectations -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-900 mb-4">📋 Matrice des Affectations du Cours (Année {{ $activeYear?->name }})</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matière</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enseignant Attribué</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matricule</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($assignments as $asn)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ $asn->schoolClass->name }}
                                        <div class="text-xs text-gray-400 font-normal">{{ $asn->schoolClass->level->cycle->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-purple-900">
                                        {{ $asn->subject->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        {{ $asn->teacher->user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-mono font-bold text-indigo-600">
                                        {{ $asn->teacher->matricule }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('assignments.show', $asn) }}"
                                               class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                                👁️ Voir
                                            </a>
                                            <a href="{{ route('assignments.edit', $asn) }}"
                                               class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                                ✏️ Modifier
                                            </a>
                                            <form method="POST" action="{{ route('assignments.destroy', $asn) }}" class="inline"
                                                  data-confirm="Voulez-vous supprimer cette affectation d'enseignant ?">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                                                    🗑️ Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Aucune affectation de cours enregistrée pour le moment.
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
