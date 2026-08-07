<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ✏️ Modifier l'Inscription de {{ $enrollment->student->last_name }} {{ $enrollment->student->first_name }}
            </h2>
            <a href="{{ route('enrollments.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour aux inscriptions
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">

                <!-- Info Élève (readonly) -->
                <div class="p-4 bg-purple-50 rounded-2xl border border-purple-100 flex items-center gap-4 mb-6">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-purple-700 to-indigo-900 flex items-center justify-center text-white font-black text-lg flex-shrink-0">
                        {{ mb_substr($enrollment->student->first_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-xs font-black text-purple-700 uppercase tracking-widest">Élève</div>
                        <div class="text-base font-black text-purple-950">{{ $enrollment->student->last_name }} {{ $enrollment->student->first_name }}</div>
                        <div class="text-xs text-purple-600">Matricule : {{ $enrollment->student->matricule }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('enrollments.update', $enrollment) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Classe *</label>
                            <select name="class_id" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach ($classes as $cls)
                                    <option value="{{ $cls->id }}" {{ old('class_id', $enrollment->class_id) == $cls->id ? 'selected' : '' }}>
                                        {{ $cls->level->cycle->name }} — {{ $cls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Statut *</label>
                            <select name="status" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="EN_ATTENTE" {{ old('status', $enrollment->status) == 'EN_ATTENTE' ? 'selected' : '' }}>En Attente</option>
                                <option value="VALIDE" {{ old('status', $enrollment->status) == 'VALIDE' ? 'selected' : '' }}>Validé</option>
                                <option value="ANNULE" {{ old('status', $enrollment->status) == 'ANNULE' ? 'selected' : '' }}>Annulé</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Type d'Inscription</label>
                            <select name="type" class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="NOUVELLE" {{ old('type', $enrollment->type) == 'NOUVELLE' ? 'selected' : '' }}>Nouvelle Inscription</option>
                                <option value="REINSCRIPTION" {{ old('type', $enrollment->type) == 'REINSCRIPTION' ? 'selected' : '' }}>Réinscription</option>
                                <option value="TRANSFERT" {{ old('type', $enrollment->type) == 'TRANSFERT' ? 'selected' : '' }}>Transfert</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Date d'Inscription</label>
                            <input type="date" name="enrollment_date" value="{{ old('enrollment_date', $enrollment->enrollment_date->format('Y-m-d')) }}"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Remarques (optionnel)</label>
                            <textarea name="notes" rows="2" class="w-full text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">{{ old('notes', $enrollment->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('enrollments.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Mettre à Jour
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
