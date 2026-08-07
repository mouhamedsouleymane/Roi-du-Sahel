<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Complexe Scolaire Privé Les Rois du Sahel | Excellence Éducative</title>
        <meta name="description" content="Site officiel et portail de pré-inscription en ligne du Complexe Scolaire Privé Les Rois du Sahel. Enseignement Préscolaire, Primaire, Collège et Lycée.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts & Tailwind CSS -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-950 text-slate-100 selection:bg-amber-400 selection:text-purple-950">

        <!-- Navigation Bar -->
        <nav class="sticky top-0 z-50 bg-purple-950 border-b border-purple-800/80 shadow-2xl">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                
                <!-- Brand Header -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-2xl bg-amber-400 flex items-center justify-center text-2xl shadow-lg shadow-amber-400/30 group-hover:scale-105 transition duration-300">
                        👑
                    </div>
                    <div>
                        <h1 class="font-black text-lg text-white tracking-wide leading-tight group-hover:text-amber-400 transition">
                            LES ROIS DU SAHEL
                        </h1>
                        <span class="text-[10px] text-amber-400 font-black tracking-widest uppercase block">
                            Complexe Scolaire Privé
                        </span>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden md:flex items-center gap-8 text-xs font-black uppercase tracking-wider text-white">
                    <a href="#accueil" class="hover:text-amber-400 transition">Accueil</a>
                    <a href="#presentation" class="hover:text-amber-400 transition">Présentation</a>
                    <a href="#cycles" class="hover:text-amber-400 transition">Nos Cycles</a>
                    <a href="#portail" class="hover:text-amber-400 transition">Suivi Digital</a>
                    <a href="#preinscription" class="hover:text-amber-400 transition text-amber-300">Pré-inscription</a>
                    <a href="#contact" class="hover:text-amber-400 transition">Contact</a>
                </div>

                <!-- Action Button / Auth -->
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-400 text-purple-950 text-xs font-black shadow-lg hover:bg-amber-300 transition flex items-center gap-2">
                            <span>👑 Espace Gestion</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl bg-purple-900 text-white text-xs font-black hover:bg-purple-800 transition border border-purple-700">
                            Se Connecter
                        </a>
                        <a href="#preinscription" class="hidden sm:inline-flex px-5 py-2.5 rounded-xl bg-amber-400 text-purple-950 text-xs font-black shadow-lg hover:bg-amber-300 transition">
                            S'inscrire en Ligne
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section id="accueil" class="relative overflow-hidden pt-12 pb-24 lg:pt-20 lg:pb-32 bg-gradient-to-b from-purple-950 via-purple-900 to-slate-950 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

                <!-- Success Alert after Pre-Enrollment -->
                @if (session('preinscription_success'))
                    <div class="mb-10 max-w-3xl mx-auto p-6 bg-emerald-950 border-2 border-emerald-400 rounded-3xl text-emerald-100 shadow-2xl animate-fade-in-up">
                        <div class="flex items-start gap-4">
                            <span class="text-4xl">🎉</span>
                            <div>
                                <h3 class="text-lg font-black text-emerald-300">Demande de Pré-inscription Reçue avec Succès !</h3>
                                <p class="text-sm text-emerald-100 mt-1">
                                    La candidature de <strong class="text-white">{{ session('preinscription_success.name') }}</strong> pour le cycle <strong class="text-amber-300">{{ session('preinscription_success.cycle') }}</strong> a bien été enregistrée sous la référence :
                                </p>
                                <div class="mt-3 inline-block px-4 py-1.5 rounded-xl bg-emerald-900 border border-emerald-400 font-mono text-base font-black text-amber-300 shadow-inner">
                                    {{ session('preinscription_success.ref') }}
                                </div>
                                <p class="text-xs text-emerald-200 mt-2">
                                    Conservez ce numéro de référence. Notre équipe administrative vous contactera par téléphone pour finaliser le dépôt de dossier.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="text-center max-w-4xl mx-auto space-y-6">
                    
                    <!-- Motto Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-400/20 border border-amber-400/50 text-amber-300 text-xs font-black tracking-widest uppercase shadow-sm">
                        <span>✨ MOTTO</span>
                        <span>•</span>
                        <span>DISCIPLINE • TRAVAIL • SUCCÈS</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-4xl sm:text-6xl font-black text-white leading-tight tracking-tight">
                        L'Excellence Éducative au Cœur du <span class="text-amber-400">Sahel</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-lg sm:text-xl text-purple-100 font-medium leading-relaxed max-w-3xl mx-auto">
                        Le <strong class="text-white">Complexe Scolaire Privé "Les Rois du Sahel"</strong> offre un cadre d'apprentissage stimulant, rigoureux et moderne du Préscolaire au Lycée. Formons ensemble la future élite de notre nation.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap justify-center items-center gap-4 pt-4">
                        <a href="#preinscription" class="px-8 py-4 rounded-2xl bg-amber-400 text-purple-950 text-base font-black shadow-xl hover:bg-amber-300 transition transform hover:scale-105 flex items-center gap-3">
                            <span>📝 Déposer une Demande de Pré-inscription</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="#cycles" class="px-8 py-4 rounded-2xl bg-purple-900 hover:bg-purple-800 text-white border border-purple-700 text-base font-black transition">
                            Découvrir nos Cycles & Programmes
                        </a>
                    </div>

                    <!-- Key Stats Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-12">
                        <div class="p-5 rounded-2xl bg-purple-900/60 border border-purple-700/60 shadow-lg">
                            <div class="text-3xl font-black text-amber-400">4 Cycles</div>
                            <div class="text-xs text-purple-200 font-bold uppercase tracking-wider mt-1">Maternelle au Lycée</div>
                        </div>
                        <div class="p-5 rounded-2xl bg-purple-900/60 border border-purple-700/60 shadow-lg">
                            <div class="text-3xl font-black text-emerald-400">98.5%</div>
                            <div class="text-xs text-purple-200 font-bold uppercase tracking-wider mt-1">Taux de Réussite Examens</div>
                        </div>
                        <div class="p-5 rounded-2xl bg-purple-900/60 border border-purple-700/60 shadow-lg">
                            <div class="text-3xl font-black text-amber-400">+1200</div>
                            <div class="text-xs text-purple-200 font-bold uppercase tracking-wider mt-1">Élèves Épanouis</div>
                        </div>
                        <div class="p-5 rounded-2xl bg-purple-900/60 border border-purple-700/60 shadow-lg">
                            <div class="text-3xl font-black text-emerald-400">100%</div>
                            <div class="text-xs text-purple-200 font-bold uppercase tracking-wider mt-1">Suivi Digital Parent</div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Presentation & Values Section -->
        <section id="presentation" class="py-20 bg-slate-950 border-t border-purple-900/40 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="text-xs font-black text-amber-400 tracking-widest uppercase">Pourquoi Nous Choisir</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white">Une Vision Pédagogique Fondée sur l'Excellence</h2>
                    <p class="text-slate-300 text-sm">
                        Au Complexe Scolaire Privé Les Rois du Sahel, nous combinons la rigueur des programmes nationaux avec des méthodes d'enseignement modernes et un suivi individualisé.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-8 rounded-3xl bg-slate-900 border border-purple-800/60 shadow-xl space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-purple-950 text-amber-400 flex items-center justify-center text-3xl font-black shadow-inner">
                            🎯
                        </div>
                        <h3 class="text-xl font-black text-white">Discipline & Rigueur</h3>
                        <p class="text-slate-300 text-xs leading-relaxed">
                            Encadrement stricte du comportement, respect des horaires et incitation constante au travail bien fait dès le plus jeune âge.
                        </p>
                    </div>

                    <div class="p-8 rounded-3xl bg-slate-900 border border-purple-800/60 shadow-xl space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-purple-950 text-amber-400 flex items-center justify-center text-3xl font-black shadow-inner">
                            👨‍🏫
                        </div>
                        <h3 class="text-xl font-black text-white">Corps Enseignant Qualifié</h3>
                        <p class="text-slate-300 text-xs leading-relaxed">
                            Professeurs diplômés, expérimentés et passionnés par la réussite académique et humaine de chaque élève.
                        </p>
                    </div>

                    <div class="p-8 rounded-3xl bg-slate-900 border border-purple-800/60 shadow-xl space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-purple-950 text-amber-400 flex items-center justify-center text-3xl font-black shadow-inner">
                            📱
                        </div>
                        <h3 class="text-xl font-black text-white">Transparence & Suivi Digital</h3>
                        <p class="text-slate-300 text-xs leading-relaxed">
                            Accès en temps réel pour les parents aux notes, bulletins de notes en ligne, relevés d'absences et reçu de paiements.
                        </p>
                    </div>
                </div>

            </div>
        </section>

        <!-- Cycles Section -->
        <section id="cycles" class="py-20 bg-purple-950 border-t border-purple-900/40 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="text-xs font-black text-amber-400 tracking-widest uppercase">Offre Éducative</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white">Nos Cycles d'Enseignement</h2>
                    <p class="text-purple-200 text-sm">
                        De l'éveil de la petite enfance jusqu'au diplôme du Baccalauréat, nous accompagnons votre enfant à chaque étape.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                    <!-- Maternelle -->
                    <div class="p-6 rounded-3xl bg-slate-900 border-2 border-purple-700/80 flex flex-col justify-between space-y-6 shadow-xl">
                        <div class="space-y-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-amber-400 text-purple-950">CYCLES PRÉSCOLAIRES</span>
                            <h3 class="text-2xl font-black text-white">Maternelle</h3>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Éveil créatif, développement du langage, motricité et sociabilité dans des espaces sécurisés.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-200 space-y-2 border-t border-slate-800 pt-4 font-bold">
                            <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Petite, Moyenne & Grande Section</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Activités ludiques & d'éveil</li>
                        </ul>
                    </div>

                    <!-- Primaire -->
                    <div class="p-6 rounded-3xl bg-slate-900 border-2 border-purple-700/80 flex flex-col justify-between space-y-6 shadow-xl">
                        <div class="space-y-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-amber-400 text-purple-950">FONDAMENTAUX</span>
                            <h3 class="text-2xl font-black text-white">Primaire</h3>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Acquisition solide de la lecture, écriture, calcul, sciences de base, civisme et éducation morale.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-200 space-y-2 border-t border-slate-800 pt-4 font-bold">
                            <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Du CI au CM2</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Examen de Fin d'Études Primaire</li>
                        </ul>
                    </div>

                    <!-- Collège -->
                    <div class="p-6 rounded-3xl bg-slate-900 border-2 border-purple-700/80 flex flex-col justify-between space-y-6 shadow-xl">
                        <div class="space-y-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-amber-400 text-purple-950">SECONDAIRE 1ER CYCLE</span>
                            <h3 class="text-2xl font-black text-white">Collège</h3>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Renforcement scientifique et littéraire, apprentissage des langues vivantes, développement du sens critique.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-200 space-y-2 border-t border-slate-800 pt-4 font-bold">
                            <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> De la 7ème à la 9ème (6ème à 3ème)</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Diplôme du DEF / Brevet</li>
                        </ul>
                    </div>

                    <!-- Lycée -->
                    <div class="p-6 rounded-3xl bg-slate-900 border-2 border-amber-400 flex flex-col justify-between space-y-6 shadow-xl">
                        <div class="space-y-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-amber-400 text-purple-950">SECONDAIRE 2ND CYCLE</span>
                            <h3 class="text-2xl font-black text-white">Lycée</h3>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Spécialisations scientifiques et littéraires, préparation intensive au Baccalauréat et aux études supérieures.
                            </p>
                        </div>
                        <ul class="text-xs text-slate-200 space-y-2 border-t border-slate-800 pt-4 font-bold">
                            <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Séries TSE, TSECO, TSS, LSH</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400">✓</span> Préparation Baccalauréat</li>
                        </ul>
                    </div>

                </div>

            </div>
        </section>

        <!-- Digital Portal Showcase Section -->
        <section id="portail" class="py-20 bg-slate-900 text-white border-t border-purple-900/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div class="space-y-6">
                    <span class="text-xs font-black text-amber-400 tracking-widest uppercase">Espace Familles & Élèves</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white leading-tight">
                        Un Suivi Scolaire en Temps Réel à la Portée des Parents
                    </h2>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Chaque tuteur inscrit bénéficie d'un accès sécurisé au portail de l'établissement pour consulter les notes, télécharger les bulletins trimestriels, suivre les présences et vérifier le solde des frais scolaires.
                    </p>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-center gap-3 text-sm font-extrabold text-white">
                            <span class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</span>
                            <span>Consultation des bulletins officiels horodatés</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm font-extrabold text-white">
                            <span class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</span>
                            <span>Alertes instantanées en cas de retard ou d'absence</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm font-extrabold text-white">
                            <span class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</span>
                            <span>Reçus officiels de scolarité téléchargeables</span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-amber-400 text-purple-950 font-black text-xs hover:bg-amber-300 transition inline-flex items-center gap-2 shadow-lg">
                            <span>🔑 Accéder à mon Espace Parent</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Graphic Preview Box -->
                <div class="p-8 rounded-3xl bg-slate-950 border-2 border-purple-800/80 shadow-2xl space-y-6">
                    <div class="flex items-center justify-between border-b border-purple-800/60 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-400 text-purple-950 font-black flex items-center justify-center text-sm">
                                👑
                            </div>
                            <div>
                                <div class="text-xs font-black text-white">PORTAIL PARENT</div>
                                <div class="text-[10px] text-amber-400 font-bold">Année Scolaire 2025-2026</div>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-black border border-emerald-500/30">Connecté</span>
                    </div>

                    <div class="space-y-3">
                        <div class="p-4 rounded-2xl bg-slate-900 border border-purple-800/40 flex justify-between items-center">
                            <div>
                                <div class="text-xs font-bold text-white">Dernier Bulletin Publié</div>
                                <div class="text-[10px] text-purple-300">Trimestre 1 &bull; Moyenne : 16.45 / 20</div>
                            </div>
                            <span class="text-xs font-black px-3 py-1 rounded-lg bg-amber-400 text-purple-950">Rang : 2ème</span>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-900 border border-purple-800/40 flex justify-between items-center">
                            <div>
                                <div class="text-xs font-bold text-white">Statut des Présences</div>
                                <div class="text-[10px] text-emerald-400">100% Assiduité enregistrée</div>
                            </div>
                            <span class="text-xs font-black text-emerald-400">En Règle</span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Interactive Pre-Enrollment Request Form Section -->
        <section id="preinscription" class="py-20 bg-slate-950 text-white border-t border-purple-900/40 relative">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <div class="text-center space-y-3">
                    <span class="px-4 py-1.5 rounded-full bg-amber-400 text-purple-950 text-xs font-black tracking-widest uppercase shadow-md">
                        FORMULAIRE D'ADMISSION
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white">Demande de Pré-inscription en Ligne</h2>
                    <p class="text-purple-200 text-sm max-w-2xl mx-auto">
                        Soumettez directement la candidature de votre enfant pour la rentrée scolaire. Notre secrétariat traitera votre demande sous 48 heures.
                    </p>
                </div>

                <div class="bg-purple-950 p-8 sm:p-10 rounded-3xl border-2 border-amber-400/60 shadow-2xl">
                    <form method="POST" action="{{ route('pre-enrollment.store') }}" class="space-y-8">
                        @csrf

                        <!-- Section Élève -->
                        <div>
                            <h3 class="text-sm font-black text-amber-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-amber-400 text-purple-950 flex items-center justify-center text-xs font-black">1</span>
                                IDENTITÉ DU FUTUR ÉLÈVE
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-black text-amber-300 uppercase tracking-wider mb-2">NOM DE L'ÉLÈVE *</label>
                                    <input type="text" name="student_last_name" required placeholder="ex: KEITA" value="{{ old('student_last_name') }}"
                                           class="w-full text-sm font-bold bg-slate-900 border-2 border-purple-700 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-300 px-4 py-3" />
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-amber-300 uppercase tracking-wider mb-2">PRÉNOM(S) DE L'ÉLÈVE *</label>
                                    <input type="text" name="student_first_name" required placeholder="ex: Mamadou" value="{{ old('student_first_name') }}"
                                           class="w-full text-sm font-bold bg-slate-900 border-2 border-purple-700 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-300 px-4 py-3" />
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-amber-300 uppercase tracking-wider mb-2">DATE DE NAISSANCE *</label>
                                    <input type="date" name="birth_date" required value="{{ old('birth_date') }}"
                                           class="w-full text-sm font-bold bg-slate-900 border-2 border-purple-700 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 px-4 py-3" />
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-amber-300 uppercase tracking-wider mb-2">GENRE *</label>
                                    <select name="gender" required class="w-full text-sm font-bold bg-slate-900 border-2 border-purple-700 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 px-4 py-3">
                                        <option value="" class="bg-slate-900 text-white">-- Choisir --</option>
                                        <option value="M" class="bg-slate-900 text-white">Masculin</option>
                                        <option value="F" class="bg-slate-900 text-white">Féminin</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-black text-amber-300 uppercase tracking-wider mb-2">CYCLE & NIVEAU SOUHAITÉ *</label>
                                    <select name="requested_cycle" required class="w-full text-sm font-bold bg-slate-900 border-2 border-purple-700 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 px-4 py-3">
                                        <option value="" class="bg-slate-900 text-white">-- Sélectionner le cycle et niveau --</option>
                                        <option value="Maternelle Petite Section" class="bg-slate-900 text-white">Maternelle - Petite Section</option>
                                        <option value="Maternelle Moyenne Section" class="bg-slate-900 text-white">Maternelle - Moyenne Section</option>
                                        <option value="Maternelle Grande Section" class="bg-slate-900 text-white">Maternelle - Grande Section</option>
                                        <option value="Primaire (CI - CM2)" class="bg-slate-900 text-white">Primaire (CI, CP, CE1, CE2, CM1, CM2)</option>
                                        <option value="Collège (7ème - 9ème)" class="bg-slate-900 text-white">Collège (7ème, 8ème, 9ème / 6ème - 3ème)</option>
                                        <option value="Lycée Scientifique (TSE / TSECO)" class="bg-slate-900 text-white">Lycée - Séries Scientifiques (TSE / TSECO)</option>
                                        <option value="Lycée Littéraire (TSS / LSH)" class="bg-slate-900 text-white">Lycée - Séries Littéraires (TSS / LSH)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section Parent / Tuteur -->
                        <div class="border-t border-purple-800/80 pt-6">
                            <h3 class="text-sm font-black text-amber-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-amber-400 text-purple-950 flex items-center justify-center text-xs font-black">2</span>
                                COORDONNÉES DU PARENT OU TUTEUR LÉGAL
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-black text-amber-300 uppercase tracking-wider mb-2">NOM COMPLET DU TUTEUR *</label>
                                    <input type="text" name="guardian_name" required placeholder="ex: Souleymane KEITA" value="{{ old('guardian_name') }}"
                                           class="w-full text-sm font-bold bg-slate-900 border-2 border-purple-700 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-300 px-4 py-3" />
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-amber-300 uppercase tracking-wider mb-2">TÉLÉPHONE JOIGNABLE *</label>
                                    <input type="text" name="guardian_phone" required placeholder="ex: +223 70 00 00 00" value="{{ old('guardian_phone') }}"
                                           class="w-full text-sm font-bold bg-slate-900 border-2 border-purple-700 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-300 px-4 py-3" />
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-amber-300 uppercase tracking-wider mb-2">ADRESSE EMAIL</label>
                                    <input type="email" name="guardian_email" placeholder="parent@email.com" value="{{ old('guardian_email') }}"
                                           class="w-full text-sm font-bold bg-slate-900 border-2 border-purple-700 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-300 px-4 py-3" />
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-amber-300 uppercase tracking-wider mb-2">REMARQUES OU BESOINS PARTICULIERS</label>
                                    <input type="text" name="notes" placeholder="ex: Élève transféré de l'établissement X" value="{{ old('notes') }}"
                                           class="w-full text-sm font-bold bg-slate-900 border-2 border-purple-700 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-300 px-4 py-3" />
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="w-full sm:w-auto px-10 py-4 rounded-2xl bg-amber-400 hover:bg-amber-300 text-purple-950 text-base font-black shadow-2xl hover:scale-105 transition transform flex items-center justify-center gap-3 cursor-pointer">
                                <span>🚀 Envoyer la Demande de Pré-inscription</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </section>

        <!-- Contact & Footer Section -->
        <footer id="contact" class="bg-purple-950 border-t border-purple-800/80 pt-16 pb-12 text-slate-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-10 pb-12 border-b border-purple-800/40">
                
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-400 text-purple-950 flex items-center justify-center text-xl font-black">
                            👑
                        </div>
                        <div>
                            <h4 class="font-black text-white text-base">LES ROIS DU SAHEL</h4>
                            <span class="text-[10px] text-amber-400 font-bold uppercase tracking-widest">Complexe Scolaire Privé</span>
                        </div>
                    </div>
                    <p class="text-xs text-purple-200 leading-relaxed">
                        Établissement privé d'excellence dispensant les programmes officiels de la Maternelle au Lycée.
                    </p>
                    <div class="text-xs font-black text-amber-300">
                        DISCIPLINE • TRAVAIL • SUCCÈS
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="font-black text-white text-sm uppercase tracking-wider">Contact Administration</h4>
                    <div class="text-xs space-y-2">
                        <div class="flex items-center gap-2"><span class="text-amber-400">📞</span> <span>+223 20 00 00 00 / +223 76 00 00 00</span></div>
                        <div class="flex items-center gap-2"><span class="text-amber-400">✉️</span> <span>contact@roisdusahel.edu</span></div>
                        <div class="flex items-center gap-2"><span class="text-amber-400">📍</span> <span>Quartier Résidentiel, Sahel</span></div>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="font-black text-white text-sm uppercase tracking-wider">Horaires Secrétariat</h4>
                    <div class="text-xs space-y-1.5 text-purple-200">
                        <div><strong>Lundi — Vendredi :</strong> 07h30 — 17h00</div>
                        <div><strong>Samedi :</strong> 08h00 — 12h30</div>
                        <div><strong>Dimanche :</strong> Fermé</div>
                    </div>
                </div>

            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-purple-300 font-semibold">
                <div>
                    &copy; {{ date('Y') }} Complexe Scolaire Privé Les Rois du Sahel. Tous droits réservés.
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ route('login') }}" class="hover:text-amber-400 transition">Espace Gestion Administration</a>
                </div>
            </div>
        </footer>

    </body>
</html>
