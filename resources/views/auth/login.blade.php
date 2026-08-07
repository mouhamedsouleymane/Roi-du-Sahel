<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-black text-purple-200 uppercase tracking-wider mb-2">
                Adresse Email
            </label>
            <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                   placeholder="votre.email@roisdusahel.edu"
                   class="w-full text-sm font-bold bg-purple-900/50 border border-purple-700/60 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-400" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-xs font-black text-purple-200 uppercase tracking-wider">
                    Mot de passe
                </label>
                @if (Route::has('password.request'))
                    <a class="text-[11px] font-bold text-amber-400 hover:underline" href="{{ route('password.request') }}">
                        Oublié ?
                    </a>
                @endif
            </div>

            <input id="password" type="password" name="password" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full text-sm font-bold bg-purple-900/50 border border-purple-700/60 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-400" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-purple-700 text-purple-900 shadow-sm focus:ring-amber-400" name="remember">
                <span class="ms-2 text-xs font-bold text-purple-200">Rester connecté</span>
            </label>
        </div>

        <div>
            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 text-purple-950 font-black text-sm shadow-xl shadow-amber-500/20 hover:brightness-110 transition flex items-center justify-center gap-2">
                <span>🔐 Accéder à mon Espace</span>
            </button>
        </div>

        @if (Route::has('register'))
            <div class="text-center pt-2 border-t border-purple-800/60 text-xs text-purple-300">
                Vous n'avez pas encore de compte ?
                <a href="{{ route('register') }}" class="font-black text-amber-400 hover:underline ms-1">
                    Créer un compte
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
