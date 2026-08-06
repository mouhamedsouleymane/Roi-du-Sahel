<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formulaire d\'Inscription d\'un Nouvel Élève') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="p-6 sm:p-8 bg-white shadow sm:rounded-lg">
                
                <form method="POST" action="{{ route('enrollments.store') }}" class="space-y-8">
                    @csrf

                    <!-- Section 1: État Civil de l'Élève -->
                    <div>
                        <h3 class="text-lg font-bold text-purple-900 border-b pb-2 mb-4">
                            👤 1. Informations sur l'Élève
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="last_name" class="block font-medium text-sm text-gray-700">Nom de famille</label>
                                <input type="text" id="last_name" name="last_name" required value="{{ old('last_name') }}" placeholder="Moussa" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="first_name" class="block font-medium text-sm text-gray-700">Prénom(s)</label>
                                <input type="text" id="first_name" name="first_name" required value="{{ old('first_name') }}" placeholder="Souleymane" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="gender" class="block font-medium text-sm text-gray-700">Sexe</label>
                                <select id="gender" name="gender" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculin (M)</option>
                                    <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Féminin (F)</option>
                                </select>
                            </div>

                            <div>
                                <label for="birth_date" class="block font-medium text-sm text-gray-700">Date de Naissance</label>
                                <input type="date" id="birth_date" name="birth_date" required value="{{ old('birth_date') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>

                            <div>
                                <label for="birth_place" class="block font-medium text-sm text-gray-700">Lieu de Naissance</label>
                                <input type="text" id="birth_place" name="birth_place" required value="{{ old('birth_place', 'Niamey') }}" placeholder="Niamey" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="blood_group" class="block font-medium text-sm text-gray-700">Groupe Sanguin (Optionnel)</label>
                                <select id="blood_group" name="blood_group" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Non renseigné</option>
                                    <option value="O+">O+</option>
                                    <option value="A+">A+</option>
                                    <option value="B+">B+</option>
                                    <option value="AB+">AB+</option>
                                    <option value="O-">O-</option>
                                    <option value="A-">A-</option>
                                </select>
                            </div>
                            <div>
                                <label for="previous_school" class="block font-medium text-sm text-gray-700">École de provenance / Établissement d'origine</label>
                                <input type="text" id="previous_school" name="previous_school" value="{{ old('previous_school') }}" placeholder="CSP Les Champions" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Parent / Tuteur -->
                    <div>
                        <h3 class="text-lg font-bold text-purple-900 border-b pb-2 mb-4">
                            👨‍👩‍👧 2. Parent / Tuteur Légal
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="guardian_last_name" class="block font-medium text-sm text-gray-700">Nom du Tuteur</label>
                                <input type="text" id="guardian_last_name" name="guardian_last_name" required value="{{ old('guardian_last_name') }}" placeholder="Souley" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                            <div>
                                <label for="guardian_first_name" class="block font-medium text-sm text-gray-700">Prénom(s) du Tuteur</label>
                                <input type="text" id="guardian_first_name" name="guardian_first_name" required value="{{ old('guardian_first_name') }}" placeholder="Moussa" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="relationship" class="block font-medium text-sm text-gray-700">Lien de Parenté</label>
                                <select id="relationship" name="relationship" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="PERE">Père</option>
                                    <option value="MERE">Mère</option>
                                    <option value="TUTEUR_LEGAL">Tuteur Légal</option>
                                    <option value="ONCLE">Oncle / Tante</option>
                                </select>
                            </div>

                            <div>
                                <label for="phone_primary" class="block font-medium text-sm text-gray-700">Téléphone Principal (Obligatoire)</label>
                                <input type="text" id="phone_primary" name="phone_primary" required value="{{ old('phone_primary') }}" placeholder="+227 90 12 34 56" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>

                            <div>
                                <label for="phone_secondary" class="block font-medium text-sm text-gray-700">Téléphone Secondaire</label>
                                <input type="text" id="phone_secondary" name="phone_secondary" value="{{ old('phone_secondary') }}" placeholder="+227 96 12 34 56" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Affectation de Classe -->
                    <div>
                        <h3 class="text-lg font-bold text-purple-900 border-b pb-2 mb-4">
                            🏫 3. Affectation & Inscription
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="class_id" class="block font-medium text-sm text-gray-700">Classe d'Affectation</label>
                                <select id="class_id" name="class_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Choisir la classe --</option>
                                    @foreach ($classes as $cls)
                                        <option value="{{ $cls->id }}">{{ $cls->level->cycle->name }} - {{ $cls->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="type" class="block font-medium text-sm text-gray-700">Type d'Inscription</label>
                                <select id="type" name="type" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="NOUVEAU">Nouvelle Inscription</option>
                                    <option value="REINSCRIPTION">Réinscription</option>
                                    <option value="TRANSFERT">Transfert Entrant</option>
                                </select>
                            </div>

                            <div class="flex items-center pt-6">
                                <input type="checkbox" id="is_repeater" name="is_repeater" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                <label for="is_repeater" class="ml-2 font-medium text-sm text-gray-700">Élève Redoublant(e)</label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('students.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg text-sm">
                            Annuler
                        </a>
                        <x-primary-button>
                            {{ __('Valider l\'Inscription') }}
                        </x-primary-button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>
