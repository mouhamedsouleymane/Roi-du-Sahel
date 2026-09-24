<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3 no-print">
            <h2 class="font-black text-xl text-purple-950 leading-tight flex items-center gap-2">
                📄 {{ __('Reçu Officiel d\'Inscription') }} — <span class="text-amber-600">{{ $enrollment->enrollment_number }}</span>
            </h2>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" title="Exporter le reçu sous forme de fichier PDF"
                        class="px-4 py-2 bg-purple-950 hover:bg-purple-900 text-white font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5">
                    📥 Exporter en PDF
                </button>
                <button onclick="window.print()" title="Lancer l'impression directe du reçu"
                        class="px-4 py-2 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-purple-950 font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5 transform hover:scale-105">
                    🖨️ Imprimer
                </button>
                <a href="{{ route('enrollments.index') }}"
                   class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                    ← Liste
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-100 min-h-screen printable-area">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Flash success alert --}}
            @if (session('status'))
                <div class="mb-4 bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-xl font-bold text-xs shadow-xs no-print flex items-center justify-between">
                    <span>✅ {{ session('status') }}</span>
                    <button onclick="window.print()" class="text-xs font-black text-emerald-950 underline ml-4">
                        Imprimer le reçu maintenant 🖨️
                    </button>
                </div>
            @endif

            {{-- Official Paper Receipt Card (Strictly 1 Page A4 Format) --}}
            <div class="bg-white shadow-2xl rounded-2xl p-6 border border-slate-200 relative overflow-hidden receipt-card">

                {{-- Watermark background logo --}}
                <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none">
                    <img src="{{ asset('images/logo_roi.jpeg') }}" alt="Filigrane" class="w-80 h-80 object-cover rounded-full">
                </div>

                {{-- Header Official Banner --}}
                <div class="flex items-center justify-between pb-4 border-b-2 border-purple-950/20 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 rounded-full overflow-hidden bg-white ring-2 ring-amber-400 shadow-xs flex-shrink-0">
                            <img src="{{ asset('images/logo_roi.jpeg') }}" alt="Logo" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h1 class="font-black text-lg text-purple-950 tracking-wide uppercase leading-tight">
                                LES ROIS DU SAHEL
                            </h1>
                            <p class="text-[11px] font-extrabold text-amber-600 tracking-widest uppercase">
                                Complexe Scolaire Privé
                            </p>
                            <p class="text-[9px] text-slate-500 font-bold">
                                DISCIPLINE • TRAVAIL • SUCCÈS
                            </p>
                            <p class="text-[9px] text-slate-400">
                                Niamey, Niger &bull; Tél: +227 90 00 00 00 / 96 00 00 00
                            </p>
                        </div>
                    </div>

                    <div class="text-right bg-amber-50 px-4 py-2.5 rounded-xl border border-amber-200">
                        <span class="text-[9px] font-black text-amber-900 tracking-widest uppercase block mb-0.5">
                            REÇU D'INSCRIPTION OFFICIEL
                        </span>
                        <div class="text-sm font-black text-purple-950 font-mono">
                            {{ $enrollment->enrollment_number }}
                        </div>
                        <div class="text-[11px] font-bold text-slate-600">
                            Date: {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : now()->format('d/m/Y') }}
                        </div>
                        <div class="text-[11px] font-bold text-purple-900">
                            Année: {{ $enrollment->academicYear->name }}
                        </div>
                    </div>
                </div>

                {{-- Student & Guardian Info Grid --}}
                <div class="grid grid-cols-2 gap-4 my-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="space-y-1 text-xs">
                        <h3 class="text-[11px] font-black text-purple-950 uppercase tracking-wider border-b border-slate-200 pb-0.5 mb-1.5">
                            🎓 Informations Élève
                        </h3>
                        <p class="font-bold text-slate-700">
                            Matricule: <span class="text-purple-900 font-black">{{ $enrollment->student->matricule }}</span>
                        </p>
                        <p class="font-bold text-slate-700">
                            Nom & Prénom: <span class="text-slate-900 font-black uppercase">{{ $enrollment->student->last_name }} {{ $enrollment->student->first_name }}</span>
                        </p>
                        <p class="font-bold text-slate-700">
                            Classe: <span class="text-purple-900 font-extrabold">{{ $enrollment->schoolClass->level->cycle->name }} - {{ $enrollment->schoolClass->name }}</span>
                        </p>
                        <p class="font-bold text-slate-700">
                            Type: <span class="px-2 py-0.5 bg-purple-100 text-purple-900 rounded font-black text-[10px]">{{ $enrollment->type }}</span>
                        </p>
                    </div>

                    <div class="space-y-1 text-xs">
                        <h3 class="text-[11px] font-black text-purple-950 uppercase tracking-wider border-b border-slate-200 pb-0.5 mb-1.5">
                            👨‍👩‍👧 Parent / Tuteur Légal
                        </h3>
                        @php
                            $primaryGuardian = $enrollment->student->guardians->first();
                        @endphp
                        @if ($primaryGuardian)
                            <p class="font-bold text-slate-700">
                                Nom du Tuteur: <span class="text-slate-900 font-black">{{ $primaryGuardian->first_name }} {{ $primaryGuardian->last_name }}</span>
                            </p>
                            <p class="font-bold text-slate-700">
                                Lien de Parenté: <span class="text-slate-800 font-bold">{{ $primaryGuardian->relationship }}</span>
                            </p>
                            <p class="font-bold text-slate-700">
                                Téléphone: <span class="text-slate-900 font-black">{{ $primaryGuardian->phone_primary }}</span>
                            </p>
                            <p class="font-bold text-slate-700">
                                Adresse: <span class="text-slate-800">{{ $primaryGuardian->address ?? 'Niamey' }}</span>
                            </p>
                        @else
                            <p class="text-slate-500 italic text-[11px]">Information tuteur non spécifiée.</p>
                        @endif
                    </div>
                </div>

                {{-- Fee Breakdown Table --}}
                <div class="my-4">
                    <h3 class="text-[11px] font-black text-purple-950 uppercase tracking-wider mb-2">
                        💰 Détail des Frais & Règlements Effectués
                    </h3>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-purple-950 text-white text-[10px] font-black uppercase tracking-wider">
                                    <th class="p-2.5">Libellé du Frais</th>
                                    <th class="p-2.5 text-right">Montant Dû</th>
                                    <th class="p-2.5 text-right">Montant Payé</th>
                                    <th class="p-2.5 text-center">Mode Règlement</th>
                                    <th class="p-2.5 text-right">Solde Restant</th>
                                    <th class="p-2.5 text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-xs font-bold text-slate-800">
                                @forelse ($invoices as $inv)
                                    @php
                                        $lastPayment = $inv->payments->last();
                                    @endphp
                                    <tr class="{{ $inv->feeType->code === 'INSCRIPT' ? 'bg-amber-50/50' : '' }}">
                                        <td class="p-2.5">
                                            <span class="font-extrabold text-purple-950">{{ $inv->feeType->name }}</span>
                                            <span class="block text-[9px] text-slate-400 font-normal">Facture #{{ $inv->invoice_number }}</span>
                                        </td>
                                        <td class="p-2.5 text-right font-black">{{ number_format($inv->amount_due, 0, ',', ' ') }} FCFA</td>
                                        <td class="p-2.5 text-right font-black text-emerald-700">{{ number_format($inv->amount_paid, 0, ',', ' ') }} FCFA</td>
                                        <td class="p-2.5 text-center">
                                            @if ($lastPayment)
                                                <span class="px-2 py-0.5 bg-slate-200 text-slate-800 rounded text-[9px] font-extrabold">
                                                    {{ $lastPayment->payment_method }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 text-[9px]">—</span>
                                            @endif
                                        </td>
                                        <td class="p-2.5 text-right font-black text-red-600">
                                            {{ number_format($inv->amount_remaining, 0, ',', ' ') }} FCFA
                                        </td>
                                        <td class="p-2.5 text-center">
                                            @if ($inv->status === 'PAYEE')
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-[9px] font-black">
                                                    PAYÉE
                                                </span>
                                            @elseif ($inv->status === 'PARTIELLE')
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-[9px] font-black">
                                                    PARTIELLE
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-[9px] font-black">
                                                    IMPAYÉE
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-3 text-center text-slate-500 italic text-xs">
                                            Aucune facture enregistrée pour cette inscription.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-purple-50 font-black text-xs text-purple-950 border-t-2 border-purple-200">
                                <tr>
                                    <td class="p-2.5">TOTAL RÈGLEMENTS PERÇUS</td>
                                    <td class="p-2.5 text-right">{{ number_format($invoices->sum('amount_due'), 0, ',', ' ') }} FCFA</td>
                                    <td class="p-2.5 text-right text-emerald-700">{{ number_format($invoices->sum('amount_paid'), 0, ',', ' ') }} FCFA</td>
                                    <td class="p-2.5"></td>
                                    <td class="p-2.5 text-right text-red-600">{{ number_format($invoices->sum(fn($i) => $i->amount_remaining), 0, ',', ' ') }} FCFA</td>
                                    <td class="p-2.5"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Signatures & Stamp Block --}}
                <div class="grid grid-cols-2 gap-6 mt-6 pt-4 border-t border-slate-200 text-xs">
                    <div class="text-center">
                        <p class="font-extrabold text-slate-700 uppercase tracking-wider mb-8">
                            Signature du Parent / Tuteur Légal
                        </p>
                        <p class="text-[9px] text-slate-400 italic">Lu et approuvé</p>
                    </div>

                    <div class="text-center">
                        <p class="font-extrabold text-purple-950 uppercase tracking-wider mb-1">
                            Le Caissier / L'Agent Comptable
                        </p>
                        <p class="text-[10px] font-bold text-slate-600 mb-4">
                            {{ auth()->user()->name ?? 'Service de la Comptabilité' }}
                        </p>
                        <div class="w-28 h-12 border-2 border-dashed border-purple-300 rounded-lg mx-auto flex items-center justify-center text-[9px] text-purple-400 font-bold uppercase tracking-widest">
                            [ Cachet Officiel ]
                        </div>
                    </div>
                </div>

                {{-- Receipt Footer Notice --}}
                <div class="mt-4 text-center text-[9px] text-slate-400 border-t border-slate-100 pt-2">
                    Ce reçu officiel est délivré par le Complexe Scolaire Privé Les Rois du Sahel. Conservez ce document précieusement.
                </div>

            </div>

        </div>
    </div>

    {{-- Strict 1-Page A4 Print & PDF CSS --}}
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        @media print {
            html, body {
                height: 100% !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                color: black !important;
            }
            .no-print, nav, aside, header, footer {
                display: none !important;
            }
            .printable-area {
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: 1px solid #94a3b8 !important;
                border-radius: 8px !important;
                padding: 16px !important;
                max-height: 275mm !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</x-app-layout>
