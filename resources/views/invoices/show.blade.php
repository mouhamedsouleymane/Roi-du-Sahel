<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-xl text-purple-950 leading-tight">
                    🧾 Facture {{ $invoice->invoice_number }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $invoice->feeType->name }} &nbsp;•&nbsp; {{ $invoice->academicYear->name ?? '–' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if ($invoice->enrollment)
                    <a href="{{ route('enrollments.receipt', $invoice->enrollment) }}"
                       class="px-4 py-2 bg-amber-400 text-purple-950 font-black rounded-xl text-xs hover:bg-amber-300 transition shadow-sm flex items-center gap-1">
                        📄 Reçu d'Inscription
                    </a>
                @endif
                <button onclick="window.print()"
                        class="px-4 py-2 bg-purple-950 text-white font-black rounded-xl text-xs hover:bg-purple-900 transition flex items-center gap-1">
                    🖨️ Imprimer
                </button>
                <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-purple-900 hover:underline">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-2xl shadow-xs">
                    <strong>Succès ! </strong>{{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-2xl shadow-xs">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- En-tête Facture --}}
            <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-purple-100">
                <div class="bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white p-8 relative overflow-hidden">
                    <div class="relative z-10 flex flex-wrap justify-between items-center gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-400 to-purple-600 flex items-center justify-center text-3xl shadow-lg ring-2 ring-amber-400/40">
                                👑
                            </div>
                            <div>
                                <div class="text-xs font-black uppercase tracking-widest text-amber-400">Complexe Scolaire Privé</div>
                                <div class="text-2xl font-black">LES ROIS DU SAHEL</div>
                                <div class="text-xs opacity-80 mt-0.5">Niamey, Niger • Koubia Plateau</div>
                            </div>
                        </div>
                        <div class="text-right bg-white/10 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/20">
                            <div class="text-[10px] uppercase font-extrabold tracking-widest text-amber-300">Facture Officielle N°</div>
                            <div class="text-2xl font-black font-mono text-white">{{ $invoice->invoice_number }}</div>
                            <div class="text-xs opacity-80 mt-0.5">Émise le {{ $invoice->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Info Élève --}}
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Élève Facturé</div>
                        <div class="text-lg font-black text-slate-900">
                            {{ $invoice->student->last_name }} {{ $invoice->student->first_name }}
                        </div>
                        <div class="text-xs text-purple-700 font-mono font-bold">{{ $invoice->student->matricule }}</div>
                        <div class="text-sm text-slate-600 mt-1">
                            Classe : <span class="font-bold text-slate-900">{{ $invoice->enrollment->schoolClass->name }}</span>
                            &nbsp;•&nbsp; {{ $invoice->enrollment->schoolClass->level->cycle->name }}
                        </div>
                        @if ($invoice->student->guardians->isNotEmpty())
                            <div class="text-xs text-slate-500 font-semibold mt-1">
                                Parent/Tuteur : {{ $invoice->student->guardians->first()->full_name }}
                            </div>
                        @endif
                    </div>
                    <div class="text-right space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Résumé Financier</div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Montant dû :</span>
                                <span class="font-bold text-slate-900">{{ number_format($invoice->amount_due, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Montant payé :</span>
                                <span class="font-bold text-emerald-700">{{ number_format($invoice->amount_paid, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between text-sm border-t border-slate-200 pt-1.5 mt-1">
                                <span class="font-extrabold text-slate-800">Reste à payer :</span>
                                @php $remaining = (float)$invoice->amount_due - (float)$invoice->amount_paid; @endphp
                                <span class="font-black text-xl {{ $remaining > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ number_format($remaining, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                        @php
                            $statusConf = [
                                'PAYEE'     => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                'PARTIELLE' => 'bg-amber-100 text-amber-800 border-amber-300',
                                'IMPAYEE'   => 'bg-red-100 text-red-800 border-red-300',
                                'ANNULEE'   => 'bg-slate-100 text-slate-600 border-slate-300',
                            ];
                        @endphp
                        <div class="mt-3">
                            <span class="px-4 py-1.5 text-xs font-black rounded-full border {{ $statusConf[$invoice->status] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $invoice->status }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Barre de progression du paiement --}}
                @php
                    $pctPaid = $invoice->amount_due > 0
                        ? min(100, round(((float)$invoice->amount_paid / (float)$invoice->amount_due) * 100))
                        : 0;
                @endphp
                <div class="px-6 py-4 bg-slate-100/70 border-t border-slate-200/60">
                    <div class="flex justify-between text-xs text-slate-600 mb-1.5">
                        <span class="font-bold">Progression du règlement</span>
                        <span class="font-black {{ $pctPaid >= 100 ? 'text-emerald-700' : 'text-amber-600' }}">{{ $pctPaid }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3.5 p-0.5 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700 {{ $pctPaid >= 100 ? 'bg-gradient-to-r from-emerald-600 to-emerald-400' : 'bg-gradient-to-r from-amber-500 to-amber-400' }}"
                             style="width: {{ $pctPaid }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Historique des Paiements --}}
            <div class="bg-white shadow-lg rounded-3xl p-6 border border-slate-100">
                <h3 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2">
                    📜 Versements & Reçus Effectués
                </h3>

                @if ($invoice->payments->isNotEmpty())
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-extrabold text-slate-500 uppercase">N° Reçu</th>
                                <th class="px-4 py-2.5 text-left text-xs font-extrabold text-slate-500 uppercase">Date</th>
                                <th class="px-4 py-2.5 text-left text-xs font-extrabold text-slate-500 uppercase">Mode</th>
                                <th class="px-4 py-2.5 text-left text-xs font-extrabold text-slate-500 uppercase">Référence</th>
                                <th class="px-4 py-2.5 text-right text-xs font-extrabold text-slate-500 uppercase">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($invoice->payments as $pmt)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs font-bold text-purple-700">{{ $pmt->receipt_number }}</td>
                                    <td class="px-4 py-3 text-slate-700 font-semibold">{{ $pmt->payment_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                            {{ $pmt->payment_method }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $pmt->reference ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-black text-emerald-700">
                                        {{ number_format($pmt->amount, 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-sm text-slate-400 italic text-center py-4">Aucun versement enregistré pour le moment.</p>
                @endif
            </div>

            {{-- Formulaire Nouveau Paiement --}}
            @if ($invoice->status !== 'PAYEE' && $invoice->status !== 'ANNULEE')
                <div class="bg-white shadow-lg rounded-3xl p-6 border border-slate-100">
                    <h3 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2">
                        💳 Enregistrer un Versement
                    </h3>

                    <form method="POST" action="{{ route('payments.store', $invoice) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700">
                                    Montant (FCFA) — max {{ number_format($remaining, 0, ',', ' ') }} FCFA
                                </label>
                                <input type="number" name="amount" min="1" max="{{ $remaining }}" required
                                       value="{{ $remaining }}"
                                       class="mt-1 block w-full border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 text-sm font-bold" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700">Mode de Paiement</label>
                                <select name="payment_method" required class="mt-1 block w-full border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 text-sm font-bold">
                                    <option value="ESPECES">Espèces</option>
                                    <option value="MOBILE_MONEY">Mobile Money</option>
                                    <option value="VIREMENT">Virement Bancaire</option>
                                    <option value="CHEQUE">Chèque</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700">Date du Versement</label>
                                <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                                       class="mt-1 block w-full border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 text-sm font-bold" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700">Référence (Optionnel)</label>
                                <input type="text" name="reference" placeholder="N° chèque, transaction MoMo…"
                                       class="mt-1 block w-full border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 text-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Caissier / Enregistré par</label>
                            <input type="text" name="recorded_by" placeholder="Nom du caissier"
                                   class="mt-1 block w-full border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 text-sm" />
                        </div>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Enregistrer le Paiement
                        </x-primary-button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
