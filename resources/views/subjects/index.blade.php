<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestion des Matières & Coefficients') }}
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Formulaire Nouvelle Matière -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">📚 Ajouter une Matière au Catalogue</h3>
                    <form method="POST" action="{{ route('subjects.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="code" class="block font-medium text-sm text-gray-700">Code (ex: MATH)</label>
                                <input type="text" id="code" name="code" required placeholder="MATH" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                            <div>
                                <label for="category" class="block font-medium text-sm text-gray-700">Catégorie</label>
                                <select id="category" name="category" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="scientifique">Scientifique</option>
                                    <option value="littéraire">Littéraire</option>
                                    <option value="langues">Langues</option>
                                    <option value="sport">Sport / EPS</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">Nom de la Matière</label>
                            <input type="text" id="name" name="name" required placeholder="Mathématiques" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                        </div>

                        <x-primary-button>
                            {{ __('Enregistrer la Matière') }}
                        </x-primary-button>
                    </form>
                </div>

                <!-- Formulaire Attribution Coefficient par Niveau -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">⚖️ Définir un Coefficient par Niveau</h3>
                    <form method="POST" action="{{ route('subjects.coefficients.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="level_id" class="block font-medium text-sm text-gray-700">Niveau Scolaire</label>
                            <select id="level_id" name="level_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Sélectionner un niveau --</option>
                                @foreach ($levels as $lvl)
                                    <option value="{{ $lvl->id }}">{{ $lvl->cycle->name }} - {{ $lvl->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="subject_id" class="block font-medium text-sm text-gray-700">Matière</label>
                            <select id="subject_id" name="subject_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Sélectionner une matière --</option>
                                @foreach ($subjects as $sbj)
                                    <option value="{{ $sbj->id }}">{{ $sbj->name }} ({{ $sbj->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="coefficient" class="block font-medium text-sm text-gray-700">Coefficient</label>
                                <input type="number" id="coefficient" name="coefficient" value="2" min="1" max="20" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                            <div>
                                <label for="weekly_hours" class="block font-medium text-sm text-gray-700">Volume Horaire / Semaine</label>
                                <input type="number" id="weekly_hours" name="weekly_hours" value="3" min="1" max="20" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>

                        <x-primary-button>
                            {{ __('Enregistrer le Coefficient') }}
                        </x-primary-button>
                    </form>
                </div>

            </div>

            <!-- Table Catalogue des Matières -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-900 mb-4">📖 Catalogue Global des Matières</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($subjects as $sbj)
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-gray-900">{{ $sbj->name }}</span>
                                <span class="text-xs bg-purple-100 text-purple-800 font-bold px-2 py-0.5 rounded">
                                    {{ $sbj->code }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 capitalize">Catégorie : {{ $sbj->category }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
