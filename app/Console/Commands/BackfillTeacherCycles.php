<?php

namespace App\Console\Commands;

use App\Models\Cycle;
use App\Models\Teacher;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillTeacherCycles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *   php artisan teachers:backfill-cycles              → Mode interactif (enseignant par enseignant)
     *   php artisan teachers:backfill-cycles --all=3      → Assigne le cycle ID 3 à tous les enseignants sans cycle
     *   php artisan teachers:backfill-cycles --all=3 --force → Inclut les enseignants qui ont déjà un cycle
     */
    protected $signature = 'teachers:backfill-cycles
                            {--all= : ID du cycle à affecter à tous les enseignants sans cycle}
                            {--force : En combinaison avec --all, écrase aussi les cycles déjà définis}';

    protected $description = 'Affecte un cycle aux enseignants existants qui n\'en ont pas encore';

    public function handle(): int
    {
        $cycles = Cycle::orderBy('id')->get();

        if ($cycles->isEmpty()) {
            $this->error('Aucun cycle trouvé dans la base de données. Créez des cycles d\'abord.');

            return self::FAILURE;
        }

        // ── Mode batch (--all=X) ────────────────────────────────────────────
        if ($this->option('all') !== null) {
            return $this->handleBatchMode($cycles);
        }

        // ── Mode interactif ─────────────────────────────────────────────────
        return $this->handleInteractiveMode($cycles);
    }

    /**
     * Assign the same cycle to all teachers without one.
     */
    private function handleBatchMode(Collection $cycles): int
    {
        $cycleId = (int) $this->option('all');
        $cycle = $cycles->firstWhere('id', $cycleId);

        if (! $cycle) {
            $this->error("Cycle ID {$cycleId} introuvable. IDs disponibles : ".$cycles->pluck('id')->implode(', '));

            return self::FAILURE;
        }

        $query = $this->option('force')
            ? Teacher::query()
            : Teacher::whereNull('cycle_id');

        $count = $query->count();

        if ($count === 0) {
            $this->info('Aucun enseignant à mettre à jour.');

            return self::SUCCESS;
        }

        $this->warn("{$count} enseignant(s) seront affectés au cycle : {$cycle->name}");

        if (! $this->confirm('Confirmer la mise à jour en masse ?')) {
            $this->info('Annulé.');

            return self::SUCCESS;
        }

        $isPolyvalent = in_array(
            strtolower(trim($cycle->name)),
            Teacher::polyvalentCycleNames(),
            strict: true
        );

        $updated = $query->update([
            'cycle_id' => $cycle->id,
            'is_polyvalent' => $isPolyvalent,
        ]);

        $this->info("✅ {$updated} enseignant(s) mis à jour → cycle : {$cycle->name}".
            ($isPolyvalent ? ' (polyvalent)' : ' (spécialiste)'));

        return self::SUCCESS;
    }

    /**
     * Let the user assign cycles teacher by teacher.
     */
    private function handleInteractiveMode(Collection $cycles): int
    {
        $teachers = Teacher::with('user')->whereNull('cycle_id')->orderBy('id')->get();

        if ($teachers->isEmpty()) {
            $this->info('✅ Tous les enseignants ont déjà un cycle assigné.');

            return self::SUCCESS;
        }

        $this->info("{$teachers->count()} enseignant(s) sans cycle à traiter.\n");

        // Display available cycles
        $this->table(
            ['ID', 'Cycle', 'Type'],
            $cycles->map(fn ($c) => [
                $c->id,
                $c->name,
                in_array(strtolower(trim($c->name)), Teacher::polyvalentCycleNames())
                    ? '🌟 Polyvalent'
                    : '🎓 Spécialiste',
            ])->toArray()
        );

        $cycleLabels = $cycles->pluck('name', 'id')->toArray();
        $updated = 0;

        foreach ($teachers as $teacher) {
            $this->line("\n👤 <comment>{$teacher->user->name}</comment> — Matricule : {$teacher->matricule} / Spécialité : {$teacher->speciality}");

            $choice = $this->choice(
                'Quel cycle lui affecter ?',
                array_merge(['Ignorer (passer)'], $cycleLabels),
                0
            );

            if ($choice === 'Ignorer (passer)') {
                $this->warn('   → Ignoré.');

                continue;
            }

            $selectedCycle = $cycles->first(fn ($c) => $c->name === $choice);

            if (! $selectedCycle) {
                $this->warn('   → Cycle non trouvé, ignoré.');

                continue;
            }

            $isPolyvalent = in_array(
                strtolower(trim($selectedCycle->name)),
                Teacher::polyvalentCycleNames(),
                strict: true
            );

            $teacher->update([
                'cycle_id' => $selectedCycle->id,
                'is_polyvalent' => $isPolyvalent,
            ]);

            $badge = $isPolyvalent ? '🌟 Polyvalent' : '🎓 Spécialiste';
            $this->info("   ✅ Assigné : {$selectedCycle->name} ({$badge})");
            $updated++;
        }

        $this->newLine();
        $this->info("Migration terminée. {$updated} enseignant(s) mis à jour.");

        return self::SUCCESS;
    }
}
