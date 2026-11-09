<?php

namespace App\Services\ClassRoutine;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * One class routine PDF per group of students who share it (user, 30 Oct 2026).
 *
 * The detailed term timetable (term-pdf.blade.php) says nothing about the student who gets
 * it, so everyone with the same routine gets the same file: sending to 100 students in three
 * groups renders three PDFs, not a hundred. "The same routine" is decided by the data, not the
 * group's name: the key is a hash of everything the PDF is drawn from (the student's own
 * details left out) and of the template itself. So a student whose plans differ from the rest
 * of their group (a resit, an extra module) gets their own PDF, and any change to a plan, a
 * class date, a cancellation, a deadline or the design gives a new key and a fresh PDF.
 *
 * Rendered PDFs are kept on the local disk under class-routine-cache/ (not public) and
 * reused; files older than KEEP_DAYS are cleared out when a new one is written.
 */
class ClassRoutineGroupPdf
{
    private const DIR = 'class-routine-cache';
    private const KEEP_DAYS = 14;
    private const TEMPLATES = [
        'views/pages/students/class-routine/term-pdf.blade.php',
        'views/pages/students/class-routine/partials/term-pdf-header.blade.php',
    ];

    public function __construct(private ClassRoutineBuilder $builder)
    {
    }

    /** The data a group shares: the routine without the student's own details. */
    public static function shared(array $routine): array
    {
        unset($routine['student']);

        return $routine;
    }

    /** Same key = same PDF. */
    public function key(array $routine): string
    {
        $design = implode('|', array_map(fn ($file) => is_file(resource_path($file)) ? md5_file(resource_path($file)) : '', self::TEMPLATES));

        return sha1($design.'|'.json_encode(self::shared($routine)).'|'.config('class_routine.support_hours'));
    }

    /** The PDF for this routine: the kept copy when there is one, else rendered once and kept. */
    public function bytes(array $routine): string
    {
        $key = $this->key($routine);
        $path = self::DIR.'/'.$key.'.pdf';
        $disk = Storage::disk('local');

        if ($disk->exists($path) && $disk->size($path) > 0) {
            return $disk->get($path);
        }

        // two sends of the same group at once render it once
        return Cache::lock('class-routine-pdf-'.$key, 120)->block(90, function () use ($disk, $path, $routine) {
            if ($disk->exists($path) && $disk->size($path) > 0) {
                return $disk->get($path);
            }
            $pdf = $this->render($routine);
            $disk->put($path, $pdf);
            $this->prune();

            return $pdf;
        });
    }

    /** Render the term timetable (no cache). */
    public function render(array $routine): string
    {
        $html = view('pages.students.class-routine.term-pdf', ['routine' => self::shared($routine)])->render();

        return Pdf::loadHTML($html)
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false)
            ->output();
    }

    /**
     * The PDF kept once per routine on the document disk (S3 on the servers, the local disk here)
     * for the emails to attach: the plan tree's Send Class ICP emails it without filing a copy in
     * each student's Uploads (user, 30 Oct 2026). A copy that has gone missing is put back.
     *
     * @return array{disk:string, path:string, name:string}
     */
    public function stored(array $routine): array
    {
        $disk = \App\Support\ApplicantDocumentDisk::name();
        $path = 'class-routines/'.$this->key($routine).'.pdf';
        $storage = Storage::disk($disk);

        if (!$storage->exists($path) || (int) $storage->size($path) === 0) {
            if (!$storage->put($path, $this->bytes($routine))) {
                throw new \RuntimeException('The class routine PDF could not be stored.');
            }
        }

        return ['disk' => $disk, 'path' => $path, 'name' => self::fileName($routine)];
    }

    /** What the PDF is called when it is attached or downloaded. */
    public static function fileName(array $routine): string
    {
        return 'Class_Routine_'.preg_replace('/[^A-Za-z0-9_-]+/', '-', ($routine['group']['name'] ?? '') ?: 'Term').'.pdf';
    }

    /**
     * The selected students sorted into the groups that share a PDF, before anything is sent.
     *
     * @param int[] $studentIds
     * @param int|null $termDeclarationId build every routine from this term (the plan tree); null: each student's newest
     * @return array{groups: array<int, array{key:string, name:string, course:string, term:string, weeks:int, warnings:string[], students:array}>, skipped: array}
     */
    public function groups(array $studentIds, ?int $termDeclarationId = null): array
    {
        $groups = [];
        $skipped = [];
        $students = Student::whereIn('id', $studentIds)->get(['id', 'registration_no', 'first_name', 'last_name'])->keyBy('id');

        foreach (array_values(array_unique(array_map('intval', $studentIds))) as $id) {
            $student = $students[$id] ?? null;
            $who = ['id' => $id, 'registration_no' => $student->registration_no ?? (string) $id, 'name' => trim(($student->first_name ?? '').' '.($student->last_name ?? ''))];
            if (!$student) {
                $skipped[] = $who + ['reason' => 'Student not found.'];
                continue;
            }
            try {
                $routine = $this->builder->forStudent($id, $termDeclarationId);
            } catch (\Throwable $e) {
                Log::error('Class routine could not be read', ['student_id' => $id, 'error' => $e->getMessage()]);
                $skipped[] = $who + ['reason' => 'The class plans could not be read: '.$e->getMessage()];
                continue;
            }
            if (!$routine) {
                $skipped[] = $who + ['reason' => 'No class plan is assigned for this term.'];
                continue;
            }

            $key = $this->key($routine);
            $groups[$key] ??= [
                'key' => $key,
                'name' => $routine['group']['name'] ?: 'No group',
                'course' => $routine['course'],
                'term' => $routine['term']['name'],
                'weeks' => count($routine['calendar'] ?? []),
                'warnings' => $routine['warnings'] ?? [],
                'sample_student_id' => $id,
                'students' => [],
            ];
            $groups[$key]['students'][] = $who;
        }

        $groups = array_values($groups);
        usort($groups, fn ($a, $b) => [$a['name'], count($b['students'])] <=> [$b['name'], count($a['students'])]);

        return ['groups' => $groups, 'skipped' => $skipped];
    }

    private function prune(): void
    {
        try {
            $disk = Storage::disk('local');
            $cutoff = now()->subDays(self::KEEP_DAYS)->getTimestamp();
            foreach ($disk->files(self::DIR) as $file) {
                if ($disk->lastModified($file) < $cutoff) {
                    $disk->delete($file);
                }
            }
        } catch (\Throwable $e) {
            // tidying up never stops a send
        }
    }
}
