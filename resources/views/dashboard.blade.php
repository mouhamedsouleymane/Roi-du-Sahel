<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-purple-900 leading-tight flex items-center gap-2">
                👑 Complexe Scolaire Privé Les Rois du Sahel
            </h2>
            <span class="bg-amber-100 text-amber-900 border border-amber-300 text-xs font-semibold px-3 py-1 rounded-full">
                Discipline • Travail • Succès
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Banner d'accueil -->
            <div class="bg-gradient-to-r from-purple-900 via-indigo-800 to-amber-600 rounded-2xl p-6 text-white shadow-xl">
                <div class="md:flex justify-between items-center">
                    <div>
                        <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                            Bienvenue, {{ Auth::user()->name }}
                        </span>
                        <h1 class="text-3xl font-extrabold mt-2">Tableau de Pilotage Scolaire</h1>
                        <p class="mt-1 text-purple-100 text-sm max-w-xl">
                            Gestion intégrée des cycles Maternelle, Primaire, Collège et Lycée du CSP Les Rois du Sahel (Koubia Plateau).
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 text-right bg-white/10 backdrop-blur-md p-4 rounded-xl border border-white/20">
                        <div class="text-xs text-amber-300 font-semibold uppercase">Année Scolaire Active</div>
                        <div class="text-2xl font-bold">
                            {{ \App\Models\AcademicYear::getActive()?->name ?? 'Non définie' }}
                        </div>
                        <div class="text-xs text-purple-200 mt-1">
                            {{ \App\Models\AcademicYear::getActive()?->start_date?->format('d/m/Y') }} - {{ \App\Models\AcademicYear::getActive()?->end_date?->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grille d'Accès Rapide aux Modules -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                
                <!-- Card 1: Années Scolaires -->
                <a href="{{ route('academic-years.index') }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-200 transition">
                    <div class="w-12 h-12 bg-purple-100 text-purple-700 rounded-lg flex items-center justify-center text-2xl font-bold mb-4">
                        📅
                    </div>
                    <h3 class="font-bold text-lg text-gray-900">Années Scolaires</h3>
                    <p class="text-xs text-gray-500 mt-1">Créer, basculer et clôturer les périodes académiques.</p>
                </a>

                <!-- Card 2: Paramètres Établissement -->
                <a href="{{ route('settings.index') }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-200 transition">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center text-2xl font-bold mb-4">
                        ⚙️
                    </div>
                    <h3 class="font-bold text-lg text-gray-900">Paramètres École</h3>
                    <p class="text-xs text-gray-500 mt-1">Coordonnées, horaires des cycles, tenues officielles.</p>
                </a>

                <!-- Card 3: Cycles & Classes -->
                <a href="{{ route('classes.index') }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-amber-200 transition">
                    <div class="w-12 h-12 bg-amber-100 text-amber-700 rounded-lg flex items-center justify-center text-2xl font-bold mb-4">
                        🏫
                    </div>
                    <h3 class="font-bold text-lg text-gray-900">Cycles & Classes</h3>
                    <p class="text-xs text-gray-500 mt-1">Maternelle, Primaire, Collège, Lycée & Salles.</p>
                </a>

                <!-- Card 4: Matières & Coefficients -->
                <a href="{{ route('subjects.index') }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-200 transition">
                    <div class="w-12 h-12 bg-purple-100 text-purple-700 rounded-lg flex items-center justify-center text-2xl font-bold mb-4">
                        📖
                    </div>
                    <h3 class="font-bold text-lg text-gray-900">Matières & Coefficients</h3>
                    <p class="text-xs text-gray-500 mt-1">Catalogue des cours & matrice des coefficients.</p>
                </a>

            </div>

            <!-- Section Infos Horaires & Tenues de l'Établissement -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="p-6 bg-violet-50 border border-violet-200 rounded-xl">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-3 h-3 rounded-full bg-violet-600"></span>
                        <h4 class="font-bold text-violet-900 text-base">Maternelle & Primaire</h4>
                    </div>
                    <p class="text-sm text-violet-800">
                        <strong>Horaires :</strong> {{ \App\Models\SchoolSetting::get('maternelle_primaire_hours', '08h00 à 14h30min') }}
                    </p>
                    <p class="text-sm text-violet-800 mt-1">
                        <strong>Tenue officielle :</strong> {{ \App\Models\SchoolSetting::get('maternelle_primaire_uniform', 'T-shirt violet, Pantalon/Jupe kaki') }}
                    </p>
                </div>

                <div class="p-6 bg-amber-50 border border-amber-200 rounded-xl">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <h4 class="font-bold text-amber-900 text-base">Collège & Lycée</h4>
                    </div>
                    <p class="text-sm text-amber-800">
                        <strong>Horaires :</strong> {{ \App\Models\SchoolSetting::get('college_lycee_hours', '08h00 à 13h30min') }}
                    </p>
                    <p class="text-sm text-amber-800 mt-1">
                        <strong>Tenue officielle :</strong> {{ \App\Models\SchoolSetting::get('college_lycee_uniform', 'T-shirt jaune, Pantalon/Jupe kaki') }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
