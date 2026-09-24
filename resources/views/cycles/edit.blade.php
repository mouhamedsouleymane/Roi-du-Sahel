<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ✏️ Modifier le Cycle {{ $cycle->name }}
            </h2>
            <a href="{{ route('cycles.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour aux cycles
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('cycles.update', $cycle) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Code du Cycle
                        </label>
                        <input type="text" name="code" required value="{{ old('code', $cycle->code) }}"
                               class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 uppercase" />
                        @error('code') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Nom du Cycle
                        </label>
                        <input type="text" name="name" required value="{{ old('name', $cycle->name) }}"
                               class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Description (optionnel)
                        </label>
                        <textarea name="description" rows="3"
                                  class="w-full text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">{{ old('description', $cycle->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Heure de début
                            </label>
                            <input type="time" name="start_time" value="{{ old('start_time', $cycle->start_time) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            @error('start_time') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Heure de fin
                            </label>
                            <input type="time" name="end_time" value="{{ old('end_time', $cycle->end_time) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            @error('end_time') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Couleur du T-shirt (tenue)
                        </label>
                        <select name="uniform_tshirt_color"
                                class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Sélectionner --</option>
                            @php $colors = ['Violet' => 'bg-purple-600', 'Jaune' => 'bg-amber-500', 'Vert' => 'bg-green-600', 'Bleu' => 'bg-blue-600', 'Rouge' => 'bg-red-600']; @endphp
                            @foreach ($colors as $color => $class)
                                <option value="{{ $color }}" {{ old('uniform_tshirt_color', $cycle->uniform_tshirt_color) === $color ? 'selected' : '' }}>
                                    {{ $color }}
                                </option>
                            @endforeach
                        </select>
                        @error('uniform_tshirt_color') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $cycle->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500" />
                        <label for="is_active" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">
                            Cycle actif
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('cycles.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Mettre à jour
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
