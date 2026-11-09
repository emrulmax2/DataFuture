<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\ClassRoutine\ClassRoutineBuilder;
use App\Services\ClassRoutine\ClassRoutineGroupPdf;
use Illuminate\Console\Command;

/**
 * Writes a student's class routine PDF (the one their group shares) to a file to look at,
 * sending nothing (user, 30 Oct 2026).
 *
 *   php artisan class-routine:pdf LCC20260894 --out=/tmp/routine.pdf
 */
class ClassRoutinePdf extends Command
{
    protected $signature = 'class-routine:pdf
        {student : Student id, or registration number (e.g. LCC20260894)}
        {--out= : Where to write it (default storage/app/class-routine-preview.pdf)}
        {--fresh : Render it again rather than use the kept copy}';

    protected $description = 'Write a student\'s class routine PDF to a file without sending anything';

    public function handle(ClassRoutineBuilder $builder, ClassRoutineGroupPdf $group): int
    {
        $key = trim((string) $this->argument('student'));
        $student = ctype_digit($key) ? Student::find((int) $key) : Student::where('registration_no', $key)->first();
        if (!$student) {
            $this->error('No student with id or registration number "'.$key.'".');

            return self::FAILURE;
        }
        $routine = $builder->forStudent((int) $student->id);
        if (!$routine) {
            $this->error('No class plan is assigned to '.$student->registration_no.' this term.');

            return self::FAILURE;
        }

        $started = microtime(true);
        $pdf = $this->option('fresh') ? $group->render($routine) : $group->bytes($routine);
        $out = (string) ($this->option('out') ?: storage_path('app/class-routine-preview.pdf'));
        file_put_contents($out, $pdf);

        $this->info($routine['group']['name'].' · '.$routine['term']['name'].' · '.count($routine['calendar']).' weeks · '.preg_match_all('#/Type\s*/Page[^s]#', $pdf).' pages · '.round(microtime(true) - $started, 1).'s');
        foreach ($routine['warnings'] as $warning) {
            $this->warn('  '.$warning);
        }
        $this->line('  Written to '.$out);

        return self::SUCCESS;
    }
}
