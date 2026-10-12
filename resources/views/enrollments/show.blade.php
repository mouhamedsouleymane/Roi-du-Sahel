<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('enrollments.index') }}"
                   class="p-2.5 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition">
                    ←
                </a>
                <div>
                    <h2 class="font-black text-2xl text-purple-950 leading-tight">
                        Détail Inscription : {{ $enrollment->enrollment_number }}
                    </h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">
                        Élève : <span class="text-purple-950 font-extrabold">{{ $enrollment->student->last_name }} {{ $enrollment->student->first_name }}</span> ({{ $enrollment->student->matricule }})
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('enrollments.receipt', $enrollment) }}"
                   class="px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs uppercase tracking-wider rounded-2xl shadow-md transition flex items-center gap-2">
                    <span>📄</span>
                    <span>Reçu Officiel</span>
                </a>
                <a href="{{ route('enrollments.edit', $enrollment) }}"
                   class="px-4 py-2.5 bg-purple-100 hover:bg-purple-200 text-purple-950 font-black text-xs uppercase tracking-wider rounded-2xl transition flex items-center gap-1.5">
                    <span>✏️</span>
                    <span>Modifier</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <!-- Flash Status Message -->
            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-xs">
                    <span class="text-lg">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Hero Inscription Banner -->
            <div class="bg-purple-950 text-white p-6 sm:p-8 rounded-3xl shadow-xl border border-purple-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-3xl font-black text-2xl flex items-center justify-center shadow-md {{ $enrollment->student->gender === 'F' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ mb_substr($enrollment->student->first_name, 0, 1) }}{{ mb_substr($enrollment->student->last_name, 0, 1) }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-mono font-black px-3 py-1 rounded-xl bg-purple-800 text-amber-300">
                                {{ $enrollment->enrollment_number }}
                            </span>
                            @if ($enrollment->status === 'VALIDE')
                                <span class="text-[11px] font-black px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    ● Inscription Validée
                                </span>
                            @else
                                <span class="text-[11px] font-black px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    ● {{ $enrollment->status }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl font-black text-white">
                            {{ $enrollment->student->last_name }} {{ $enrollment->student->first_name }}
                        </h1>
                        <p class="text-xs text-purple-200 font-bold">
                            Matricule Élève : <span class="font-mono text-white">{{ $enrollment->student->matricule }}</span> • Année : <span class="text-amber-300">{{ $enrollment->academicYear->name }}</span>
                        </p>
                    </div>
                </div>

                <div class="bg-purple-900/60 p-4 rounded-2xl border border-purple-800 text-right w-full md:w-auto">
                    <div class="text-[11px] font-black text-purple-300 uppercase tracking-widest">Classe d'Affectation</div>
                    <div class="text-xl font-black text-amber-300 mt-0.5">
                        {{ $enrollment->schoolClass->name }}
                    </div>
                    <div class="text-xs text-purple-200">
                        {{ $enrollment->schoolClass->level->cycle->name }}
                    </div>
                </div>
            </div>

            <!-- Grille d'Informations 2 Colonnes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- 1. Détails de l'Inscription -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="p-2 bg-purple-100 text-purple-900 rounded-xl text-lg">📋</span>
                        <div>
                            <h3 class="text-sm font-black text-purple-950 uppercase tracking-widest">
                                Paramètres de l'Inscription
                            </h3>
                            <p class="text-xs text-slate-400 font-bold">Affectation, date et type d'admission</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase">Type d'Admission</div>
                            <div class="text-sm font-black text-purple-950 mt-1">{{ $enrollment->type }}</div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase">Date d'Enregistrement</div>
                            <div class="text-sm font-bold text-slate-900 mt-1">
                                {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase">Redoublement</div>
                            <div class="text-sm font-bold mt-1 {{ $enrollment->is_repeater ? 'text-red-700' : 'text-emerald-700' }}">
                                {{ $enrollment->is_repeater ? 'Oui (Redoublant)' : 'Non (Passant)' }}
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase">Dossier Élève</div>
                            <div class="mt-1">
                                <a href="{{ route('students.show', $enrollment->student) }}" class="text-xs font-black text-purple-900 hover:underline">
                                    Voir la fiche complète →
                                </a>
                            </div>
                        </div>
                    </div>

                    @if ($enrollment->notes)
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="text-[11px] font-black text-slate-400 uppercase mb-1">Observations / Remarques</div>
                            <p class="text-xs font-bold text-slate-700">{{ $enrollment->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- 2. Situation Financière & Factures Associées -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200/80 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 bg-emerald-100 text-emerald-900 rounded-xl text-lg">💳</span>
                            <div>
                                <h3 class="text-sm font-black text-purple-950 uppercase tracking-widest">
                                    Facturation & Règlements
                                </h3>
                                <p class="text-xs text-slate-400 font-bold">Frais scolaires générés lors de l'inscription</p>
                            </div>
                        </div>
                        <a href="{{ route('enrollments.receipt', $enrollment) }}"
                           class="px-3 py-1.5 bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs rounded-xl transition shadow-xs flex items-center gap-1">
                            <span>📄 Reçu</span>
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($enrollment->invoices as $invoice)
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-black text-slate-900">{{ $invoice->feeType->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">Facture: {{ $invoice->invoice_number }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-black text-sm text-purple-950">
                                        {{ number_format($invoice->amount_due, 0, ',', ' ') }} FCFA
                                    </div>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full {{ $invoice->status === 'PAYEE' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $invoice->status }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic p-4 text-center">Aucune facture enregistrée.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
