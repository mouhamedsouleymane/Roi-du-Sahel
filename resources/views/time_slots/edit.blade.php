<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight">
                    ✏️ Modifier la Plage Horaire : {{ $timeSlot->label }}
                </h2>
                <p class="text-xs text-slate-500 font-semibold mt-1">
                    Modifier les paramètres de cette ligne d'heure.
                </p>
            </div>
            <a href="{{ route('time-slots.index') }}"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                <span>←</span>
                <span>Retour à la liste</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 sm:p-8">

                <form method="POST" action="{{ route('time-slots.update', $timeSlot) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div
                            class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-950 text-xs font-bold space-y-1">
                            <div class="font-black">Veuillez corriger les erreurs suivantes :</div>
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Heure de début --}}
                        <div>
                            <label for="start_time"
                                class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Heure Début <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" id="start_time" name="start_time"
                                value="{{ old('start_time', substr($timeSlot->start_time, 0, 5)) }}" required
                                class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-600 focus:border-purple-600" />
                        </div>

                        {{-- Heure de fin --}}
                        <div>
                            <label for="end_time"
                                class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Heure Fin <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" id="end_time" name="end_time"
                                value="{{ old('end_time', substr($timeSlot->end_time, 0, 5)) }}" required
                                class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-600 focus:border-purple-600" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Libellé affiché --}}
                        <div>
                            <label for="label"
                                class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Libellé
                            </label>
                            <input type="text" id="label" name="label"
                                value="{{ old('label', $timeSlot->label) }}" placeholder="Ex: 08:00 - 09:00"
                                class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-600 focus:border-purple-600" />
                        </div>

                        {{-- Intitulé interne --}}
                        <div>
                            <label for="name"
                                class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Intitulé / Nom (Optionnel)
                            </label>
                            <input type="text" id="name" name="name"
                                value="{{ old('name', $timeSlot->name) }}" placeholder="Ex: Matin 1, Cours 3..."
                                class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-600 focus:border-purple-600" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Cycle applicable --}}
                        <div>
                            <label for="cycle_id"
                                class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Cycle Spécifique (Optionnel)
                            </label>
                            <select id="cycle_id" name="cycle_id"
                                class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-600 focus:border-purple-600">
                                <option value="">🌐 Tous les cycles</option>
                                @foreach ($cycles as $cy)
                                    <option value="{{ $cy->id }}"
                                        {{ old('cycle_id', $timeSlot->cycle_id) == $cy->id ? 'selected' : '' }}>
                                        {{ $cy->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Ordre d'affichage --}}
                        <div>
                            <label for="order_index"
                                class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Ordre d'affichage
                            </label>
                            <input type="number" id="order_index" name="order_index"
                                value="{{ old('order_index', $timeSlot->order_index) }}" min="0"
                                class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-600 focus:border-purple-600" />
                        </div>
                    </div>

                    {{-- Options cases à cocher --}}
                    <div class="p-4 rounded-2xl bg-purple-50/50 border border-purple-100 space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_pause" value="1"
                                {{ old('is_pause', $timeSlot->is_pause) ? 'checked' : '' }}
                                class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4" />
                            <div>
                                <span class="text-xs font-black text-purple-950">Pause Déjeuner / Récréation</span>
                                <span class="block text-[11px] text-slate-500">Marque cette ligne avec une couleur
                                    spéciale de pause dans la grille.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $timeSlot->is_active) ? 'checked' : '' }}
                                class="rounded text-purple-600 focus:ring-purple-500 w-4 h-4" />
                            <div>
                                <span class="text-xs font-black text-purple-950">Plage horaire active</span>
                                <span class="block text-[11px] text-slate-500">Si décochée, la ligne ne s'affichera pas
                                    dans la grille des cours.</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('time-slots.index') }}"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Annuler
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl shadow-md transition">
                            Mettre à Jour la Plage
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
