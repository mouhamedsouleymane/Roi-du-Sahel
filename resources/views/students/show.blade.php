<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Fiche Élève — {{ $student->last_name }} {{ $student->first_name }}
            </h2>
            <a href="{{ route('students.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
                ← Retour à la liste
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- En-tête Fiche Élève -->
            <div class="p-6 bg-white shadow sm:rounded-lg flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center font-bold text-2xl border-2 border-purple-300">
                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-indigo-600 tracking-wider">MATRICULE : {{ $student->matricule }}</div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $student->last_name }} {{ $student->first_name }}</h1>
                        <p class="text-xs text-gray-500">
                            Né(e) le {{ $student->birth_date->format('d/m/Y') }} à {{ $student->birth_place }} • Groupe Sanguin : {{ $student->blood_group ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-right">
                    <div class="text-xs text-gray-500 font-semibold">Classe Actuelle</div>
                    <div class="text-lg font-extrabold text-purple-900">
                        {{ $student->currentEnrollment?->schoolClass?->name ?? 'Non inscrit' }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $student->currentEnrollment?->schoolClass?->level->cycle->name ?? '' }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Tuteurs Légaux -->
                <div class="p-6 bg-white shadow sm:rounded-lg space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">👨‍👩‍👧 Parents & Tuteurs Légaux</h3>
                    @forelse ($student->guardians as $guardian)
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900 text-base">{{ $guardian->first_name }} {{ $guardian->last_name }}</span>
                                <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full">
                                    {{ $guardian->relationship }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-600 mt-2 space-y-1">
                                <div><strong>Téléphone Principal :</strong> {{ $guardian->phone_primary }}</div>
                                @if ($guardian->phone_secondary) <div><strong>Téléphone Secondaire :</strong> {{ $guardian->phone_secondary }}</div> @endif
                                @if ($guardian->email) <div><strong>Email :</strong> {{ $guardian->email }}</div> @endif
                                @if ($guardian->address) <div><strong>Adresse :</strong> {{ $guardian->address }}, {{ $guardian->city }}</div> @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 italic">Aucun tuteur enregistré pour cet élève.</p>
                    @endforelse
                </div>

                <!-- Historique des Inscriptions -->
                <div class="p-6 bg-white shadow sm:rounded-lg space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">📜 Historique des Inscriptions</h3>
                    <div class="space-y-3">
                        @forelse ($student->enrollments as $enrollment)
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-gray-900 text-sm">Année Scolaire : {{ $enrollment->academicYear->name }}</div>
                                    <div class="text-xs text-gray-600">Classe : {{ $enrollment->schoolClass->name }} ({{ $enrollment->schoolClass->level->cycle->name }})</div>
                                    <div class="text-[11px] text-gray-400 mt-1">Inscrit le {{ $enrollment->enrollment_date->format('d/m/Y') }} • {{ $enrollment->type }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('enrollments.receipt', $enrollment) }}"
                                       class="px-3 py-1 bg-amber-400 text-purple-950 rounded-lg text-xs font-black hover:bg-amber-300 transition shadow-xs">
                                        📄 Reçu
                                    </a>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">
                                        {{ $enrollment->status }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">Aucune inscription enregistrée.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
