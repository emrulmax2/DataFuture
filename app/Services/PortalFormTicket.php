<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * What every in-portal form does once a student presses submit.
 *
 * The order is the same whichever form it is:
 *
 *   1. the request is already saved here — that happens before this is called,
 *      so a submission is never lost to an Operations outage;
 *   2. the ticket is raised in Operations, which issues the reference;
 *   3. the student is written to, quoting that reference.
 *
 * Step 3 depends on step 2, which is why no receipt is sent from a controller:
 * a request that could not be raised has no reference to quote, and promising
 * one that does not exist is worse than a short delay. Such a request is left
 * `failed` with the reason on it, for the retry job to pick up.
 *
 * A form subclasses this to say where its ticket goes, what it says and which
 * receipt the student gets. Everything else — the idempotency key, the student
 * block, the failure handling, the logo — is the same for all of them.
 */
abstract class PortalFormTicket
{
    public function __construct(protected OperationsServiceDeskClient $operations)
    {
    }

    /** services.operations.service_desk.<key>: which department and issue type. */
    abstract protected function configKey(): string;

    /** Prefix for the idempotency key, e.g. CCR — unique per form. */
    abstract protected function referencePrefix(): string;

    abstract protected function subject(Model $row, Student $student): string;

    /** @param  array<int, string>  $attached  names of the files going with it */
    abstract protected function body(Model $row, Student $student, array $attached = []): string;

    abstract protected function receipt(Model $row, Student $student): Mailable;

    /**
     * Which issue type the ticket is raised under.
     *
     * Most forms are one kind of request and have it in config. A form where
     * the student chooses - IT support, where the department answers for many
     * kinds of thing - overrides this and reads their answer.
     *
     * @param  array<string, mixed>  $config
     */
    protected function issueTypeFor(Model $row, array $config): ?int
    {
        return $config['issue_type_id'] ?? null;
    }

    /**
     * Raise it, then tell the student.
     *
     * @param  array<int, array{name: string, contents: string}>  $files
     */
    public function raise(Model $row, array $files = []): bool
    {
        if ($row->ticket_status === 'raised' && $row->ticket_ref):
            return true;
        endif;

        $student = Student::with('users', 'contact', 'activeCR.course', 'activeCR.semester')
            ->find($row->student_id);

        if (! $student):
            $this->failed($row, 'The student record no longer exists.');

            return false;
        endif;

        $config = config('services.operations.service_desk.'.$this->configKey());
        $issueTypeId = $this->issueTypeFor($row, $config);

        if (empty($issueTypeId)):
            $this->failed($row, 'No Service Desk issue type is configured for this form.');

            return false;
        endif;

        $result = $this->operations->raiseStudentTicket(
            idempotencyKey: $this->referencePrefix().'-'.$row->id,
            departmentId: (int) $config['department_id'],
            issueTypeId: (int) $issueTypeId,
            subject: $this->subject($row, $student),
            /* Named from the files actually being sent: a document that storage
               refused is still on the ticket, and the ticket should say so. */
            body: $this->body($row, $student, array_column($files, 'name')),
            student: [
                'id' => $student->id,
                'ref' => $student->registration_no,
                'name' => $this->studentName($student),
                'course' => $student->activeCR->course->name ?? null,
                'email' => $this->studentEmail($student),
            ],
            files: $files,
        );

        if (! ($result['ok'] ?? false)):
            $this->failed($row, $result['error'] ?? 'Operations could not be reached.');

            return false;
        endif;

        $row->forceFill([
            'ticket_id' => $result['ticket']['id'] ?? null,
            'ticket_ref' => $result['ticket']['ref'] ?? null,
            'ticket_status' => 'raised',
            'ticket_failed_reason' => null,
            'ticket_raised_at' => now(),
        ])->save();

        $this->notifyStudent($row, $student);

        return true;
    }

    protected function notifyStudent(Model $row, Student $student): void
    {
        $email = $this->studentEmail($student);

        if (! $email):
            Log::warning('[Portal form] No email address; receipt not sent.', [
                'form' => $this->configKey(), 'request' => $row->id,
            ]);

            return;
        endif;

        try {
            Mail::to($email)->send($this->receipt($row->fresh(), $student));

            $row->forceFill(['student_notified_at' => now()])->save();
        } catch (\Throwable $e) {
            /* The ticket exists and the department has it, so this is not a
               failure of the request - it is one email to chase. */
            Log::error('[Portal form] Receipt could not be sent.', [
                'form' => $this->configKey(), 'request' => $row->id, 'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Where to write to them.
     *
     * The college address first - it is the one the portal signs them in with
     * and the one staff would use - then whatever else is on the record, so a
     * student without an institutional address still gets their receipt.
     */
    protected function studentEmail(Student $student): ?string
    {
        return $student->contact?->institutional_email
            ?: ($student->users->email ?? $student->contact?->personal_email);
    }

    protected function studentName(Student $student): string
    {
        return trim(($student->first_name ?? '').' '.($student->last_name ?? '')) ?: 'Student';
    }

    /** A date as it should read on a ticket. */
    protected function readableDate($value): string
    {
        return $value ? date('j F Y', strtotime((string) $value)) : 'Not recorded';
    }

    protected function failed(Model $row, string $reason): void
    {
        $row->forceFill([
            'ticket_status' => 'failed',
            'ticket_failed_reason' => mb_substr($reason, 0, 1000),
        ])->save();

        Log::error('[Portal form] Ticket not raised.', [
            'form' => $this->configKey(), 'request' => $row->id, 'reason' => $reason,
        ]);
    }

    /**
     * The logo from Site Settings, embedded in the receipt.
     *
     * The uploaded file is a print-sized PNG — around 400KB — and attaching
     * that to every receipt is wasteful enough that some clients will refuse to
     * show it. A 420px copy is made once and reused; if the original is ever
     * replaced in Site Settings the copy is rebuilt, because it is older.
     */
    protected function logoPath(): ?string
    {
        $file = cache('site_logo') ?: optional(
            \App\Models\Option::where('name', 'site_logo')->first()
        )->value;

        if (! $file):
            return null;
        endif;

        $source = storage_path('app/public/'.$file);

        if (! is_file($source)):
            return null;
        endif;

        $small = storage_path('app/public/email/logo-email.png');

        if (is_file($small) && filemtime($small) >= filemtime($source)):
            return $small;
        endif;

        return $this->shrink($source, $small) ?: $source;
    }

    /** A width-constrained PNG copy, or null if GD could not make one. */
    protected function shrink(string $source, string $target, int $width = 420): ?string
    {
        if (! function_exists('imagecreatefrompng')):
            return null;
        endif;

        try {
            $image = @imagecreatefrompng($source);

            if (! $image):
                return null;
            endif;

            $height = (int) round(imagesy($image) * ($width / imagesx($image)));
            $small = imagecreatetruecolor($width, $height);

            /* The crest sits on a dark header, so transparency has to survive. */
            imagealphablending($small, false);
            imagesavealpha($small, true);
            imagecopyresampled($small, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

            if (! is_dir(dirname($target))):
                mkdir(dirname($target), 0775, true);
            endif;

            imagepng($small, $target, 9);
            imagedestroy($image);
            imagedestroy($small);
        } catch (\Throwable $e) {
            Log::warning('[Portal form] Logo could not be resized for email.', ['error' => $e->getMessage()]);

            return null;
        }

        return is_file($target) ? $target : null;
    }
}
