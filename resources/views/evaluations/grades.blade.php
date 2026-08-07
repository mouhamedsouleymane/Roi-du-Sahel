<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Saisie de Notes — {{ $evaluation->title }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $evaluation->schoolClass->name }} • {{ $evaluation->subject->name }}
                    • {{ $evaluation->period->name }}
                    • Max : {{ number_format($evaluation->max_score, 0) }} pts
                </p>
            </div>
            <a href="{{ route('evaluations.index', ['class_id' => $evaluation->class_id, 'period_id' => $evaluation->period_id]) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
                ← Retour aux évaluations
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Succès ! </strong>
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <form method="POST" action="{{ route('evaluations.grades.save', $evaluation) }}">
                    @csrf

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-purple-900 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider w-10">#</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Matricule</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Élève</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">Absent</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">
                                    Note (/ {{ number_format($evaluation->max_score, 0) }})
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Observation</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($students as $i => $student)
                                @php
                                    $grade = $student->grades->first();
                                @endphp
                                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-6 py-3 text-xs font-mono font-bold text-indigo-600">
                                        {{ $student->matricule }}
                                    </td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">
                                        {{ $student->last_name }} {{ $student->first_name }}
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <input type="hidden" name="grades[{{ $i }}][student_id]" value="{{ $student->id }}" />
                                        <input type="checkbox"
                                               id="absent_{{ $student->id }}"
                                               name="grades[{{ $i }}][is_absent]"
                                               value="1"
                                               {{ $grade?->is_absent ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                               onchange="toggleScore(this, 'score_{{ $student->id }}')" />
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <input type="number"
                                               id="score_{{ $student->id }}"
                                               name="grades[{{ $i }}][score]"
                                               min="0"
                                               max="{{ $evaluation->max_score }}"
                                               step="0.25"
                                               value="{{ $grade && ! $grade->is_absent ? $grade->score : '' }}"
                                               placeholder="—"
                                               {{ $grade?->is_absent ? 'disabled' : '' }}
                                               class="w-24 text-center border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                                    </td>
                                    <td class="px-6 py-3">
                                        <input type="text"
                                               name="grades[{{ $i }}][comment]"
                                               value="{{ $grade?->comment ?? '' }}"
                                               placeholder="Facultatif"
                                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        <x-primary-button>
                            💾 {{ __('Enregistrer toutes les Notes') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleScore(checkbox, scoreFieldId) {
            const field = document.getElementById(scoreFieldId);
            if (checkbox.checked) {
                field.disabled = true;
                field.value = '';
            } else {
                field.disabled = false;
                field.focus();
            }
        }
    </script>
</x-app-layout>
