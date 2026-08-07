<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Rois du Sahel') }} - Connexion & Accès</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-b from-purple-950 via-slate-950 to-purple-950 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-amber-400 selection:text-purple-950">

        <!-- Top Header Navigation -->
        <div class="px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xs font-black text-amber-400 hover:underline flex items-center gap-2">
                <span>← Retour au Site Officiel</span>
            </a>
            <div class="text-[10px] font-black tracking-widest text-amber-300 uppercase hidden sm:block">
                DISCIPLINE • TRAVAIL • SUCCÈS
            </div>
        </div>

        <!-- Main Card Wrapper -->
        <div class="flex-1 flex flex-col justify-center items-center px-4 py-8">
            <div class="w-full sm:max-w-md space-y-6 animate-fade-in-up">

                <!-- Logo & Brand Header -->
                <div class="text-center space-y-3">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-gradient-to-tr from-amber-400 via-amber-500 to-purple-600 shadow-xl shadow-amber-500/20 ring-4 ring-amber-400/30 text-3xl animate-float">
                        👑
                    </div>
                    <h1 class="text-2xl font-black text-white tracking-wide">
                        ROIS DU SAHEL
                    </h1>
                    <p class="text-xs text-amber-400 font-extrabold tracking-widest uppercase">
                        Complexe Scolaire Privé &bull; Espace Sécurisé
                    </p>
                </div>

                <!-- Form Card -->
                <div class="bg-purple-950/80 backdrop-blur-xl border border-purple-800/60 p-8 shadow-2xl rounded-3xl">
                    {{ $slot }}
                </div>

            </div>
        </div>

        <!-- Footer Motto -->
        <div class="py-4 text-center text-[11px] text-purple-300/80 font-bold">
            &copy; {{ date('Y') }} Complexe Scolaire Privé Les Rois du Sahel. Tous droits réservés.
        </div>

    </body>
</html>
