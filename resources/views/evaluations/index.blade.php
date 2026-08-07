<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Évaluations & Saisie de Notes') }}
            </h2>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('evaluations.index') }}" class="flex items-center gap-2">
                    <select name="class_id" onchange="this.form.submit()" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($classes as $cls)
                            <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                                {{ $cls->level->cycle->name }} — {{ $cls->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="period_id" onchange="this.form.submit()" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($periods as $p)
                            <option value="{{ $p->id }}" {{ $selectedPeriodId == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
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

            <!-- Formulaire Nouvelle Évaluation -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">📝 Créer une Évaluation</h3>
                <form method="POST" action="{{ route('evaluations.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}" />
                    <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="title" class="block font-medium text-sm text-gray-700">Intitulé de l'évaluation</label>
                            <input type="text" id="title" name="title" required placeholder="DS1 Mathématiques" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label for="evaluation_date" class="block font-medium text-sm text-gray-700">Date</label>
                            <input type="date" id="evaluation_date" name="evaluation_date" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="subject_id" class="block font-medium text-sm text-gray-700">Matière</label>
                            <select id="subject_id" name="subject_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach ($subjects as $sbj)
                                    <option value="{{ $sbj->id }}">{{ $sbj->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="teacher_id" class="block font-medium text-sm text-gray-700">Enseignant</label>
                            <select id="teacher_id" name="teacher_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach ($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="type" class="block font-medium text-sm text-gray-700">Type</label>
                            <select id="type" name="type" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="DEVOIR">Devoir Surveillé</option>
                                <option value="COMPOSITION">Composition</option>
                                <option value="CONTROLE">Contrôle</option>
                                <option value="EXAMEN">Examen</option>
                            </select>
                        </div>
                        <div>
                            <label for="max_score" class="block font-medium text-sm text-gray-700">Note Max</label>
                            <input type="number" id="max_score" name="max_score" min="1" max="100" step="0.5" value="20" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                        </div>
                    </div>

                    <x-primary-button>{{ __('Créer l\'Évaluation') }}</x-primary-button>
                </form>
            </div>

            <!-- Liste des Évaluations -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    📋 Évaluations — {{ $selectedClass?->name ?? '–' }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Intitulé</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matière</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note Max</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Notes Saisies</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($evaluations as $eval)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $eval->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $eval->subject->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                            {{ $eval->type === 'COMPOSITION' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $eval->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $eval->evaluation_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-800">/ {{ number_format($eval->max_score, 0) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                        <span class="font-bold">{{ $eval->grades->count() }}</span> note(s)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('evaluations.grades', $eval) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                            Saisir les Notes →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 italic">
                                        Aucune évaluation pour cette classe et cette période.
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
