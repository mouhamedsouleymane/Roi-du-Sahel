<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-black text-purple-200 uppercase tracking-wider mb-2">
                Nom Complet *
            </label>
            <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name"
                   placeholder="Mamadou TRAORE"
                   class="w-full text-sm font-bold bg-purple-900/50 border border-purple-700/60 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-400" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-black text-purple-200 uppercase tracking-wider mb-2">
                Adresse Email *
            </label>
            <input id="email" type="email" name="email" :value="old('email')" required autocomplete="username"
                   placeholder="votre.email@roisdusahel.edu"
                   class="w-full text-sm font-bold bg-purple-900/50 border border-purple-700/60 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-400" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-black text-purple-200 uppercase tracking-wider mb-2">
                Mot de passe *
            </label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   placeholder="••••••••"
                   class="w-full text-sm font-bold bg-purple-900/50 border border-purple-700/60 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-400" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-black text-purple-200 uppercase tracking-wider mb-2">
                Confirmer le Mot de passe *
            </label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   placeholder="••••••••"
                   class="w-full text-sm font-bold bg-purple-900/50 border border-purple-700/60 text-white rounded-xl focus:ring-amber-400 focus:border-amber-400 placeholder-purple-400" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div>
            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 text-purple-950 font-black text-sm shadow-xl shadow-amber-500/20 hover:brightness-110 transition flex items-center justify-center gap-2">
                <span>✨ Créer mon Compte</span>
            </button>
        </div>

        <div class="text-center pt-2 border-t border-purple-800/60 text-xs text-purple-300">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="font-black text-amber-400 hover:underline ms-1">
                Se connecter
            </a>
        </div>
    </form>
</x-guest-layout>
