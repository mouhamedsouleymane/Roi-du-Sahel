<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestion des Classes') }}
            </h2>
            <form method="GET" action="{{ route('classes.index') }}" class="flex items-center gap-2">
                <label for="academic_year_id" class="text-xs font-semibold text-gray-600">Année Scolaire :</label>
                <select id="academic_year_id" name="academic_year_id" onchange="this.form.submit()" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                            {{ $year->name }} {{ $year->is_active ? '(Active)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-4">➕ Créer une nouvelle Classe</h3>

                    <form method="POST" action="{{ route('classes.store') }}" class="space-y-4" data-confirm="Êtes-vous sûr de vouloir créer cette nouvelle classe ?">
                        @csrf
                        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}" />

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="level_id" class="block font-medium text-sm text-gray-700">Niveau Scolaire</label>
                                <select id="level_id" name="level_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach ($levels as $lvl)
                                        <option value="{{ $lvl->id }}">{{ $lvl->cycle->name }} - {{ $lvl->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nom de la Classe (ex: 6ème A)</label>
                                <input type="text" id="name" name="name" required placeholder="6ème A" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="capacity" class="block font-medium text-sm text-gray-700">Capacité d'accueil</label>
                                <input type="number" id="capacity" name="capacity" value="40" required min="1" max="100" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                            <div>
                                <label for="room_number" class="block font-medium text-sm text-gray-700">Salle de Classe / Batiment</label>
                                <input type="text" id="room_number" name="room_number" placeholder="Salle 12" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                            </div>
                        </div>

                        <div>
                            <label for="main_teacher_id" class="block font-medium text-sm text-gray-700">Professeur Titulaire / Principal</label>
                            <select id="main_teacher_id" name="main_teacher_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Aucun attribué --</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <x-primary-button>
                            {{ __('Créer la Classe') }}
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Liste des Classes par Cycle -->
            @foreach ($classes as $cycleName => $classList)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">🏫 Cycle {{ $cycleName }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($classList as $cls)
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl hover:shadow-sm transition">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-base font-extrabold text-gray-900">{{ $cls->name }}</span>
                                    <span class="text-xs bg-indigo-100 text-indigo-800 font-semibold px-2.5 py-0.5 rounded-full">
                                        Capacité : {{ $cls->capacity }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <div><strong>Niveau :</strong> {{ $cls->level->name }}</div>
                                    <div><strong>Salle :</strong> {{ $cls->room_number ?? 'Non attribuée' }}</div>
                                    <div><strong>Prof Principal :</strong> {{ $cls->mainTeacher?->name ?? 'Non désigné' }}</div>
                                </div>
                                <div class="mt-3 flex justify-end gap-2">
                                    <a href="{{ route('classes.show', $cls) }}"
                                       class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                        👁️ Voir
                                    </a>
                                    <a href="{{ route('classes.edit', $cls) }}"
                                       class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                        ✏️ Modifier
                                    </a>
                                    <form method="POST" action="{{ route('classes.destroy', $cls) }}" class="inline"
                                          data-confirm="Êtes-vous sûr de vouloir supprimer la classe {{ $cls->name }} ? Cette action affectera l'historique et est irréversible.">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
