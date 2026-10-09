<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads Service Desk tickets from Operations.
 *
 * Staff there tag a student on a ticket to record who it concerns; this brings
 * those onto the student's record here. Tickets live in Operations and are
 * worked there — nothing is written back, and nothing is stored locally, so
 * what a member of staff sees on this page is the ticket as it stands now.
 *
 * Follows OperationsLibraryClient: shared-secret header, short timeout, and
 * null on failure rather than an exception, so an Operations outage leaves the
 * student record rendering a line that says so instead of a 500.
 *
 * Internal notes are filtered out at the far end, not here. That is deliberate:
 * the rule belongs with the data, and this client must not be the thing anyone
 * relies on to enforce it.
 */
class OperationsServiceDeskClient
{
    /**
     * The HTTP status of the last call, or null if it never got that far.
     *
     * Kept because "refused" and "unreachable" are different answers that both
     * come back as null: a confidential ticket is a 403 and should be met with
     * a PIN prompt, not an error about Operations being down.
     */
    private ?int $lastStatus = null;

    public function lastStatus(): ?int
    {
        return $this->lastStatus;
    }

    private function request()
    {
        $key = (string) config('services.operations.api_key');

        if ($key === ''):
            Log::warning('[Operations] No API key configured; Service Desk tickets unavailable.');

            return null;
        endif;

        return Http::acceptJson()
            ->timeout((int) config('services.operations.timeout', 10))
            ->withOptions(['verify' => (bool) config('services.operations.verify_tls', true)])
            ->withHeaders(['X-Operations-Key' => $key]);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.operations.url'), '/').'/api/service-desk/'.ltrim($path, '/');
    }

    /**
     * The issue types a student may raise in one department.
     *
     * Service Desk settings in Operations decide what appears here, so the
     * portal's dropdown is never out of step with what that end will accept.
     * Cached briefly: the list changes when somebody edits the settings, which
     * is rare, and a form page should not wait on a round trip every time.
     *
     * @return array<int, array{id: int, name: string}>  empty when Operations
     *         cannot be reached, or the department is not open to students
     */
    public function studentIssueTypes(int $departmentId): array
    {
        return Cache::remember(
            'operations.sd.issue-types.'.$departmentId,
            now()->addMinutes(10),
            function () use ($departmentId) {
                $request = $this->request();

                if (! $request):
                    return [];
                endif;

                try {
                    $response = $request->get($this->url('portal/departments/'.$departmentId.'/issue-types'));
                } catch (\Throwable $e) {
                    Log::warning('[Operations] Issue types could not be read.', [
                        'department' => $departmentId, 'error' => $e->getMessage(),
                    ]);

                    return [];
                }

                if (! $response->successful()):
                    Log::warning('[Operations] Issue types refused.', [
                        'department' => $departmentId, 'status' => $response->status(),
                    ]);

                    return [];
                endif;

                return $response->json('data', []);
            }
        );
    }

    /**
     * Raise a ticket on a student's behalf.
     *
     * The student has no account in Operations, so the ticket is requested by
     * the portal account there and carries the student's name and registration
     * number as who it came from. Everything else - reference, response target,
     * who it is assigned to, who is told about it - is decided at that end, the
     * same way a ticket raised by a member of staff is.
     *
     * `idempotencyKey` is the request this came from. Operations holds it
     * unique, so a call that times out can be repeated without opening a second
     * ticket: the same one comes back, flagged as a duplicate.
     *
     * @param  array{id: string|int, ref: ?string, name: string, course: ?string, email: ?string}  $student
     * @param  array<int, array{name: string, contents: string}>  $files  already read, so a
     *         retry can send the same files back from storage
     * @return array{ok: bool, ticket?: array<string, mixed>, error?: string}
     */
    public function raiseStudentTicket(
        string $idempotencyKey,
        int $departmentId,
        int $issueTypeId,
        string $subject,
        string $body,
        array $student,
        array $files = [],
        string $priority = 'normal',
    ): array {
        $request = $this->request();

        if (! $request):
            return ['ok' => false, 'error' => 'Operations API key is not configured.'];
        endif;

        $payload = [
            ['name' => 'idempotency_key', 'contents' => $idempotencyKey],
            ['name' => 'department_id', 'contents' => (string) $departmentId],
            ['name' => 'issue_type_id', 'contents' => (string) $issueTypeId],
            ['name' => 'subject', 'contents' => $subject],
            ['name' => 'body', 'contents' => $body],
            ['name' => 'priority', 'contents' => $priority],
            ['name' => 'student[id]', 'contents' => (string) $student['id']],
            ['name' => 'student[name]', 'contents' => (string) $student['name']],
        ];

        foreach (['ref', 'course', 'email'] as $field):
            if (! empty($student[$field])):
                $payload[] = ['name' => 'student['.$field.']', 'contents' => (string) $student[$field]];
            endif;
        endforeach;

        try {
            /* Multipart throughout: the student's own files go with it, so the
               ticket carries what they attached rather than a link back here. */
            foreach ($files as $file):
                $request = $request->attach('attachments[]', $file['contents'], $file['name']);
            endforeach;

            $response = $request->asMultipart()->post($this->url('portal/tickets'), $payload);
        } catch (\Throwable $e) {
            Log::warning('[Operations] Student ticket could not be raised.', [
                'key' => $idempotencyKey, 'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }

        if (! $response->successful()):
            Log::warning('[Operations] Student ticket refused.', [
                'key' => $idempotencyKey,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'ok' => false,
                'error' => $response->json('message') ?: 'Operations refused the ticket ('.$response->status().').',
            ];
        endif;

        return ['ok' => true, 'ticket' => $response->json('data', [])];
    }

    /**
     * Every ticket this student has been tagged on.
     *
     * @return array<int, array<string, mixed>>|null  null means Operations could not be reached
     */
    public function ticketsForStudent(int|string $studentId): ?array
    {
        $request = $this->request();

        if (! $request):
            return null;
        endif;

        try {
            $response = $request->get($this->url("students/{$studentId}/tickets"));
        } catch (\Throwable $e) {
            Log::warning('[Operations] Service Desk ticket list failed.', ['student' => $studentId, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()):
            Log::warning('[Operations] Service Desk ticket list refused.', ['student' => $studentId, 'status' => $response->status()]);

            return null;
        endif;

        return $response->json('data', []);
    }

    /**
     * Every ticket this employee has been tagged on.
     *
     * Asked for by email. It is the one identifier both systems hold for a
     * member of staff — their account in Operations was created from the one
     * here by that address, and each side numbers people its own way.
     *
     * @return array<int, array<string, mixed>>|null  null means Operations could not be reached
     */
    public function ticketsForEmployee(string $email): ?array
    {
        $request = $this->request();

        if (! $request):
            return null;
        endif;

        try {
            $response = $request->get($this->url('employees/tickets'), ['email' => $email]);
        } catch (\Throwable $e) {
            Log::warning('[Operations] Service Desk ticket list failed.', ['employee' => $email, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()):
            Log::warning('[Operations] Service Desk ticket list refused.', ['employee' => $email, 'status' => $response->status()]);

            return null;
        endif;

        return $response->json('data', []);
    }

    /**
     * Who is asking, when it is an employee's profile rather than a student's
     * record. Operations serves an employee profile only a ticket tagged with
     * that employee, so the address has to travel with every request.
     *
     * @return array<string, string>
     */
    private function audience(?string $employeeEmail): array
    {
        return $employeeEmail ? ['audience' => 'employee', 'email' => $employeeEmail] : [];
    }

    /**
     * A file from a ticket's conversation.
     *
     * Fetched rather than linked. The Operations endpoint is guarded by a
     * shared key that only a server can present, so a browser sent straight at
     * it is refused — this app holds the key and its own session, and hands the
     * file to a member of staff who is already signed in here.
     *
     * Read whole rather than streamed: ticket attachments are capped at 10MB at
     * the far end, and a passthrough stream would buy little for the complexity.
     *
     * @return array{body: string, type: string, name: string}|null
     */
    public function attachment(int $attachmentId, bool $unlocked = false, ?string $employeeEmail = null): ?array
    {
        $request = $this->request();

        if (! $request):
            return null;
        endif;

        try {
            $response = $request->get(
                $this->url("attachments/{$attachmentId}"),
                $this->audience($employeeEmail) + ($unlocked ? ['unlocked' => 1] : [])
            );
        } catch (\Throwable $e) {
            Log::warning('[Operations] Service Desk attachment failed.', ['attachment' => $attachmentId, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()):
            return null;
        endif;

        return [
            'body' => $response->body(),
            'type' => $response->header('Content-Type') ?: 'application/octet-stream',
            /* Operations names the file on the way out; fall back only if it did not. */
            'name' => $this->filenameFrom($response->header('Content-Disposition')) ?: 'attachment',
        ];
    }

    /** The filename out of a Content-Disposition header, if it carries one. */
    private function filenameFrom(?string $disposition): ?string
    {
        if (! $disposition):
            return null;
        endif;

        return preg_match('/filename\*?=(?:UTF-8\'\'|")?([^";]+)/i', $disposition, $found)
            ? urldecode(trim($found[1], '"'))
            : null;
    }

    /**
     * One ticket, with the conversation the tagged person's record may show.
     *
     * Pass `$employeeEmail` when it is being read from an employee's profile;
     * without it the request is for a student record, as it always was.
     *
     * @return array<string, mixed>|null
     */
    public function ticket(int $ticketId, bool $unlocked = false, ?string $employeeEmail = null): ?array
    {
        $request = $this->request();

        if (! $request):
            return null;
        endif;

        $this->lastStatus = null;

        try {
            /* `unlocked` is only ever true after this application has
               challenged the reader for their own document PIN - it is this
               app vouching for a person, not a way around the rule. */
            $response = $request->get(
                $this->url("tickets/{$ticketId}"),
                $this->audience($employeeEmail) + ($unlocked ? ['unlocked' => 1] : [])
            );
        } catch (\Throwable $e) {
            Log::warning('[Operations] Service Desk ticket failed.', ['ticket' => $ticketId, 'error' => $e->getMessage()]);

            return null;
        }

        $this->lastStatus = $response->status();

        return $response->successful() ? $response->json('data') : null;
    }
}
