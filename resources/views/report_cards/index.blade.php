<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Bulletins de Notes') }}
            </h2>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('report-cards.index') }}" class="flex items-center gap-2">
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

            <!-- Actions -->
            <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg flex flex-wrap gap-4 items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        Classe : {{ $selectedClass?->name ?? '–' }}
                        &nbsp;•&nbsp;
                        Période : {{ $selectedPeriod?->name ?? '–' }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Générez d'abord les bulletins, puis publiez-les pour les rendre visibles.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Générer -->
                    <form method="POST" action="{{ route('report-cards.generate') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}" />
                        <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}" />
                        <button type="submit" class="px-4 py-2 text-sm font-bold rounded-lg bg-indigo-700 text-white hover:bg-indigo-800 transition">
                            ⚙️ Générer les Bulletins
                        </button>
                    </form>
                    <!-- Publier -->
                    <form method="POST" action="{{ route('report-cards.publish') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}" />
                        <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}" />
                        <button type="submit" class="px-4 py-2 text-sm font-bold rounded-lg bg-emerald-700 text-white hover:bg-emerald-800 transition">
                            ✅ Publier les Bulletins
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tableau des bulletins -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matricule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Élève</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Moy. Générale</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Mention</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($reportCards as $rc)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 text-center">
                                        {{ $rc->rank }}<span class="text-gray-400 font-normal">/{{ $rc->total_students }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono font-bold text-indigo-600">
                                        {{ $rc->student->matricule }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        {{ $rc->student->last_name }} {{ $rc->student->first_name }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php $avg = (float)$rc->general_average; @endphp
                                        <span class="text-lg font-extrabold {{ $avg >= 10 ? 'text-emerald-700' : 'text-red-700' }}">
                                            {{ number_format($avg, 2) }}/20
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                            {{ $avg >= 16 ? 'bg-emerald-100 text-emerald-800' : ($avg >= 12 ? 'bg-blue-100 text-blue-800' : ($avg >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                            {{ $rc->appreciationLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($rc->is_published)
                                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-full">Publié</span>
                                        @else
                                            <span class="text-xs font-bold text-orange-700 bg-orange-50 px-2 py-1 rounded-full">Brouillon</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <a href="{{ route('report-cards.show', $rc) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                            Voir Bulletin →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 italic">
                                        Aucun bulletin généré pour la sélection. Cliquez sur « Générer les Bulletins ».
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
