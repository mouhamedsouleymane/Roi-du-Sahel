<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profil Enseignant — {{ $teacher->user->name }}
            </h2>
            <a href="{{ route('teachers.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
                ← Retour au répertoire
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Fiche Enseignant -->
            <div class="p-6 bg-white shadow sm:rounded-lg flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold text-2xl border-2 border-indigo-300">
                        👨‍🏫
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-indigo-600 tracking-wider">MATRICULE : {{ $teacher->matricule }}</div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $teacher->user->name }}</h1>
                        <p class="text-xs text-gray-500">
                            Spécialité : {{ $teacher->speciality }} • Qualification : {{ $teacher->qualification ?? 'Non renseignée' }}
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-right">
                    <div class="text-xs text-gray-500 font-semibold">Statut & Contrat</div>
                    <div class="text-lg font-extrabold text-purple-900">
                        {{ $teacher->employment_type }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $teacher->user->email }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Classes & Matières Enseignées -->
                <div class="p-6 bg-white shadow sm:rounded-lg space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">🏫 Classes & Matières Affectées</h3>
                    <div class="space-y-2">
                        @forelse ($teacher->assignments as $asn)
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-gray-900 text-sm">{{ $asn->schoolClass->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $asn->schoolClass->level->cycle->name }}</div>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-purple-100 text-purple-800">
                                    {{ $asn->subject->name }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">Aucune affectation enregistrée pour le moment.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Emploi du Temps Hebdomadaire de l'Enseignant -->
                <div class="p-6 bg-white shadow sm:rounded-lg space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">📅 Créneaux de Cours (Hebdomadaire)</h3>
                    <div class="space-y-2">
                        @forelse ($teacher->schedules as $sch)
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-gray-900 text-sm">{{ $sch->day_of_week }} ({{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }})</div>
                                    <div class="text-xs text-gray-500">{{ $sch->schoolClass->name }} • {{ $sch->subject->name }}</div>
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 bg-gray-200 text-gray-800 rounded">
                                    {{ $sch->room_number ?? 'Salle 1' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">Aucun créneau d'emploi du temps défini.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
