<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ➕ Nouvelle Matière Enseignée
            </h2>
            <a href="{{ route('subjects.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour aux matières
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('subjects.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Code de la Matière (ex: MATH, FRANCAIS, SVT)
                        </label>
                        <input type="text" name="code" required placeholder="MATH"
                               value="{{ old('code') }}"
                               class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 uppercase" />
                        @error('code') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Nom Complet de la Matière
                        </label>
                        <input type="text" name="name" required placeholder="Mathématiques"
                               value="{{ old('name') }}"
                               class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Description ou Objectifs (optionnel)
                        </label>
                        <textarea name="description" rows="3" placeholder="Description courte…"
                                  class="w-full text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('subjects.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Enregistrer la Matière
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
