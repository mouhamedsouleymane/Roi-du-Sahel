<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Connexion — {{ config('app.name', 'Les Rois du Sahel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_roi.jpeg') }}">

    {{-- PWA : manifest, icônes, service worker + proposition d’installation --}}
    @include('layouts.partials.pwa')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased min-h-screen" x-data="{}">

    <div class="min-h-screen flex">

        {{-- ===================== PANNEAU GAUCHE — Branding ===================== --}}
        <div
            class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative flex-col justify-center items-center overflow-hidden bg-gradient-to-br from-purple-950 via-purple-900 to-slate-950 p-12">

            {{-- Motifs décoratifs de fond --}}
            <div class="absolute inset-0 pointer-events-none">
                <div
                    class="absolute top-0 left-0 w-80 h-80 bg-amber-400/5 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl">
                </div>
                <div
                    class="absolute bottom-0 right-0 w-96 h-96 bg-purple-600/20 rounded-full translate-x-1/3 translate-y-1/3 blur-3xl">
                </div>
                <div
                    class="absolute top-1/2 left-1/2 w-64 h-64 bg-red-500/5 rounded-full -translate-x-1/2 -translate-y-1/2 blur-2xl">
                </div>
                {{-- Grille décorative --}}
                <div class="absolute inset-0 opacity-[0.03]"
                    style="background-image: linear-gradient(rgba(251,191,36,1) 1px, transparent 1px), linear-gradient(90deg, rgba(251,191,36,1) 1px, transparent 1px); background-size: 40px 40px;">
                </div>
            </div>

            {{-- Contenu central --}}
            <div class="relative z-10 flex flex-col justify-center items-center text-center max-w-lg">

                {{-- Grand logo circulaire --}}
                <div class="relative mb-8">
                    <div
                        class="w-48 h-48 rounded-full overflow-hidden bg-white ring-8 ring-amber-400/20 shadow-2xl shadow-purple-900/60 mx-auto">
                        <img src="{{ asset('images/logo_roi.jpeg') }}" alt="Logo Les Rois du Sahel"
                            class="w-full h-full object-cover">
                    </div>
                    {{-- Halo lumineux --}}
                    <div class="absolute inset-0 rounded-full bg-amber-400/10 blur-xl -z-10 scale-125"></div>
                </div>

                {{-- Titre principal --}}
                <h2 class="text-4xl xl:text-5xl font-black text-white leading-tight tracking-tight mb-4">
                    Bienvenue sur<br>
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-amber-300 to-yellow-200">
                        votre Espace
                    </span>
                </h2>
                <p class="text-purple-200/80 text-sm font-medium max-w-md leading-relaxed mb-8">
                    Plateforme de gestion scolaire intégrée du Complexe Scolaire Privé Les Rois du Sahel.
                    Accédez à vos données académiques, administratives et financières en toute sécurité.
                </p>

                {{-- Devise --}}
                <div
                    class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-amber-400/10 border border-amber-400/25 backdrop-blur-sm mb-8">
                    <span class="text-amber-400 text-lg">📖</span>
                    <span class="text-amber-300 text-xs font-black tracking-widest uppercase">Discipline • Travail •
                        Succès</span>
                    <span class="text-amber-400 text-lg">👑</span>
                </div>

                {{-- Statistiques --}}
                <div class="grid grid-cols-3 gap-6 w-full max-w-sm">
                    <div class="text-center">
                        <div class="text-2xl font-black text-amber-400">🎓</div>
                        <div class="text-xs font-bold text-purple-200 mt-1">Élèves</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-black text-amber-400">👨‍🏫</div>
                        <div class="text-xs font-bold text-purple-200 mt-1">Enseignants</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-black text-amber-400">🏫</div>
                        <div class="text-xs font-bold text-purple-200 mt-1">Classes</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== PANNEAU DROIT — Formulaire ===================== --}}
        <div
            class="w-full lg:w-1/2 xl:w-2/5 flex flex-col justify-between bg-slate-50 relative overflow-hidden min-h-screen">

            {{-- Motif de fond subtil --}}
            <div class="absolute inset-0 pointer-events-none opacity-40"
                style="background-image: radial-gradient(circle at 20% 80%, rgba(124,58,237,0.05) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(251,191,36,0.05) 0%, transparent 50%);">
            </div>

            {{-- En-tête droit : Logo + Nom de l'établissement --}}
            <div class="relative z-10 px-8 sm:px-12 lg:px-12 pt-8 pb-4 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-12 h-12 rounded-full overflow-hidden bg-white ring-2 ring-amber-400/50 shadow-md flex-shrink-0">
                        <img src="{{ asset('images/logo_roi.jpeg') }}" alt="Logo Les Rois du Sahel"
                            class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h1 class="font-black text-base sm:text-lg text-purple-950 leading-tight tracking-wide">LES ROIS
                            DU SAHEL</h1>
                        <span class="text-[10px] text-amber-600 font-extrabold tracking-widest uppercase">Complexe
                            Scolaire Privé</span>
                    </div>
                </div>

            </div>

            {{-- Formulaire --}}
            <div class="relative z-10 flex-1 flex items-center justify-center px-8 sm:px-12 lg:px-12 xl:px-16 py-6">
                <div class="w-full max-w-md">

                    {{-- En-tête formulaire --}}
                    <div class="mb-6">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-[10px] font-black tracking-widest uppercase mb-3">
                            🔐 Accès Sécurisé
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-purple-950 leading-tight">
                            Connexion à<br>
                            <span class="text-purple-700">votre compte</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-2 font-medium">
                            Entrez vos identifiants pour accéder au tableau de bord.
                        </p>
                    </div>

                    {{-- Session Status --}}
                    <x-auth-session-status class="mb-5" :status="session('status')" />

                    {{-- Erreurs globales --}}
                    @if ($errors->any())
                        <div
                            class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-xs text-red-700 font-bold">
                            ⚠️ {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        {{-- Identifiant : Téléphone ou Email --}}
                        <div>
                            <label for="phone"
                                class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-2">
                                Numéro de Téléphone ou Email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required
                                    autofocus autocomplete="username" placeholder="ex: 90123456 ou admin@roisdusahel.ne"
                                    class="w-full pl-11 pr-4 py-3 text-sm font-bold bg-white border border-slate-200 rounded-2xl shadow-xs text-slate-800 placeholder-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" />
                            </div>
                            <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
                        </div>

                        {{-- Mot de passe --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password"
                                    class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider">
                                    Mot de passe
                                </label>
                                @if (Route::has('password.request'))
                                    <a class="text-[11px] font-bold text-purple-600 hover:text-purple-800 hover:underline transition"
                                        href="{{ route('password.request') }}">
                                        Mot de passe oublié ?
                                    </a>
                                @endif
                            </div>
                            <div class="relative" x-data="{ showPassword: false }">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input id="password" :type="showPassword ? 'text' : 'password'" name="password"
                                    required autocomplete="current-password" placeholder="••••••••••••"
                                    class="w-full pl-11 pr-12 py-3 text-sm font-bold bg-white border border-slate-200 rounded-2xl shadow-xs text-slate-800 placeholder-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                        </div>

                        {{-- Remember Me --}}
                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                                <input id="remember_me" type="checkbox"
                                    class="w-4 h-4 rounded border-slate-300 text-purple-600 shadow-sm focus:ring-purple-500"
                                    name="remember">
                                <span class="text-xs font-bold text-slate-500">Rester connecté</span>
                            </label>
                        </div>

                        {{-- Bouton de connexion --}}
                        <button type="submit"
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-purple-900 via-purple-800 to-purple-900 text-white font-black text-sm shadow-xl shadow-purple-900/30 hover:from-purple-800 hover:to-purple-700 transition-all duration-200 flex items-center justify-center gap-2 group mt-2">
                            <span>Accéder à mon Espace</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </form>

                </div>
            </div>

            {{-- Footer droit (Retour au site + Copyright) --}}
            <div class="relative z-10 px-8 sm:px-12 lg:px-12 pb-6 text-center space-y-2">
                <a href="{{ route('home') }}"
                    class="text-xs text-purple-700 hover:text-amber-600 transition font-bold inline-flex items-center gap-1">
                    ← Retour au Site Officiel
                </a>
                <p class="text-[11px] text-slate-400 font-medium">
                    © {{ date('Y') }} Complexe Scolaire Privé Les Rois du Sahel
                </p>
            </div>
        </div>

    </div>

    {{-- PWA : bannière de proposition d’installation --}}
    @include('layouts.partials.pwa-install-banner')

</body>

</html>
