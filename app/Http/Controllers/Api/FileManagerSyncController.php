<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Exposes the file manager (folders, documents, versions, permissions, tags and
 * reminders) for external synchronisation.
 *
 * Protected by Passport client_credentials with the `sms.file-manager.read`
 * scope, in the same shape as the other *SyncControllers.
 *
 * Read-only: nothing here writes to the file manager. People are identified by
 * email rather than by id, because the consuming system keys its own users on
 * email and the two databases share no key space.
 */
class FileManagerSyncController extends Controller
{
    /** Where the file manager keeps its files on the local disk. */
    private const ROOT = 'public/file-manager/';

    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);
        $withTrashed = $request->boolean('with_trashed');

        $documents = DB::table('document_infos')
            ->when(! $withTrashed, fn ($q) => $q->whereNull('deleted_at'))
            ->orderBy('id')
            ->paginate($perPage);

        $emails = $this->emailLookup();

        $rows = collect($documents->items())->map(function ($doc) use ($emails, $withTrashed) {
            return [
                'id'                => (int) $doc->id,
                'folder_id'         => $doc->document_folder_id ? (int) $doc->document_folder_id : null,
                'display_file_name' => $doc->display_file_name,
                'doc_type'          => $doc->doc_type,
                'file_type'         => $doc->file_type,
                'description'       => $doc->description,
                'expire_at'         => $this->iso($doc->expire_at),
                'reminder_at'       => $this->iso($doc->reminder_at),
                'publish_date'      => $this->iso($doc->publish_date),
                'email_reminder'    => (bool) ($doc->email_reminder ?? false),
                'created_by_email'  => $emails[$doc->created_by] ?? null,
                'created_at'        => $this->iso($doc->created_at),
                'updated_at'        => $this->iso($doc->updated_at),
                'deleted'           => $doc->deleted_at !== null,
                'tags'              => $this->tagsFor((int) $doc->id),
                'reminders'         => $this->remindersFor((int) $doc->id, $emails),
                'versions'          => $this->versionsFor($doc, $emails, $withTrashed),
            ];
        })->all();

        return response()->json([
            'folders' => $this->folders($emails, $withTrashed),
            'data'    => $rows,
            'meta'    => [
                'current_page' => $documents->currentPage(),
                'last_page'    => $documents->lastPage(),
                'per_page'     => $documents->perPage(),
                'total'        => $documents->total(),
            ],
        ]);
    }

    /**
     * Stream the bytes of one document or one historic version.
     *
     * The path is rebuilt from the row's own columns — never from the request —
     * so this cannot be walked outside the file manager directory.
     */
    public function download(Request $request, string $type, int $id)
    {
        $table = match ($type) {
            'version'    => 'documents',
            'attachment' => 'document_attachments',
            default      => 'document_infos',
        };

        $row = DB::table($table)->where('id', $id)->first();

        if (! $row || ! $row->path || ! $row->current_file_name) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $relative = self::ROOT . trim($row->path, '/') . '/' . $row->current_file_name;

        if (! Storage::disk('local')->exists($relative)) {
            return response()->json(['message' => 'File missing on disk.'], 404);
        }

        return response()->streamDownload(
            fn () => print(Storage::disk('local')->get($relative)),
            $row->current_file_name
        );
    }

    /* ------------------------------------------------------------------ */

    private function folders(array $emails, bool $withTrashed): array
    {
        $roles = DB::table('document_role_and_permissions')->get()->keyBy('id');

        $permissions = DB::table('document_folder_permissions')
            ->get()
            ->groupBy('document_folder_id');

        $employees = $this->employeeLoginEmails();

        return DB::table('document_folders')
            ->when(! $withTrashed, fn ($q) => $q->whereNull('deleted_at'))
            ->orderBy('id')
            ->get()
            ->map(function ($folder) use ($emails, $roles, $permissions, $employees) {
                $grants = ($permissions[$folder->id] ?? collect())
                    ->map(function ($p) use ($roles, $employees) {
                        $role = $roles[$p->document_role_and_permission_id] ?? null;

                        return [
                            'email' => $employees[$p->employee_id] ?? null,
                            'role'  => $role?->type,
                            'can'   => $role ? [
                                'create' => (bool) $role->create,
                                'read'   => (bool) $role->read,
                                'update' => (bool) $role->update,
                                'delete' => (bool) $role->delete,
                            ] : null,
                        ];
                    })
                    ->filter(fn ($g) => $g['email'] !== null)
                    ->values()
                    ->all();

                return [
                    'id'               => (int) $folder->id,
                    'parent_id'        => $folder->parent_id ? (int) $folder->parent_id : null,
                    'name'             => $folder->name,
                    'created_by_email' => $emails[$folder->created_by] ?? null,
                    'created_at'       => $this->iso($folder->created_at),
                    'deleted'          => $folder->deleted_at !== null,
                    'permissions'      => $grants,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Version history, oldest first. `documents` holds the superseded copies;
     * the document_infos row itself is the current one and is appended last.
     */
    private function versionsFor($doc, array $emails, bool $withTrashed): array
    {
        $history = DB::table('documents')
            ->where('document_info_id', $doc->id)
            ->when(! $withTrashed, fn ($q) => $q->whereNull('deleted_at'))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $versions = [];
        $seen = [];

        foreach ($history as $row) {
            if (! $row->current_file_name || isset($seen[$row->current_file_name])) {
                continue;
            }
            $seen[$row->current_file_name] = true;

            $versions[] = [
                'ref'               => 'version:' . $row->id,
                'file_name'         => $row->current_file_name,
                'display_file_name' => $row->display_file_name,
                'size'              => $this->sizeOf($row->path, $row->current_file_name),
                'exists'            => $this->existsOn($row->path, $row->current_file_name),
                'created_by_email'  => $emails[$row->created_by] ?? null,
                'created_at'        => $this->iso($row->created_at),
                'download'          => '/file-manager/sync/download/version/' . $row->id,
                'is_current'        => false,
                'attachments'       => $this->attachmentsFor((int) $row->id, $emails, $withTrashed),
            ];
        }

        if ($doc->current_file_name && ! isset($seen[$doc->current_file_name])) {
            $versions[] = [
                'ref'               => 'info:' . $doc->id,
                'file_name'         => $doc->current_file_name,
                'display_file_name' => $doc->display_file_name,
                'size'              => $this->sizeOf($doc->path, $doc->current_file_name),
                'exists'            => $this->existsOn($doc->path, $doc->current_file_name),
                'created_by_email'  => $emails[$doc->updated_by ?? $doc->created_by] ?? null,
                'created_at'        => $this->iso($doc->updated_at ?: $doc->created_at),
                'download'          => '/file-manager/sync/download/info/' . $doc->id,
                'is_current'        => true,
                // Attachments hang off the version row of the same id.
                'attachments'       => $this->attachmentsFor((int) $doc->id, $emails, $withTrashed),
            ];
        } elseif ($versions) {
            $versions[count($versions) - 1]['is_current'] = true;
        }

        return $versions;
    }

    /**
     * Supporting files attached to a document version — policy wordings behind
     * a certificate, and the like.
     */
    private function attachmentsFor(int $documentId, array $emails, bool $withTrashed): array
    {
        return DB::table('document_attachments')
            ->where('document_id', $documentId)
            ->when(! $withTrashed, fn ($q) => $q->whereNull('deleted_at'))
            ->orderBy('id')
            ->get()
            ->map(fn ($a) => [
                'ref'               => 'attachment:' . $a->id,
                'file_name'         => $a->current_file_name,
                'display_file_name' => $a->display_file_name,
                'doc_type'          => $a->doc_type,
                'size'              => $this->sizeOf($a->path, $a->current_file_name),
                'exists'            => $this->existsOn($a->path, $a->current_file_name),
                'created_by_email'  => $emails[$a->created_by] ?? null,
                'created_at'        => $this->iso($a->created_at),
                'download'          => '/file-manager/sync/download/attachment/' . $a->id,
            ])
            ->values()
            ->all();
    }

    private function tagsFor(int $documentInfoId): array
    {
        return DB::table('document_info_tags')
            ->leftJoin('document_tags', 'document_tags.id', '=', 'document_info_tags.document_tag_id')
            ->where('document_info_tags.document_info_id', $documentInfoId)
            ->pluck('document_tags.name')
            ->filter()
            ->values()
            ->all();
    }

    private function remindersFor(int $documentInfoId, array $emails): array
    {
        $reminders = DB::table('document_info_reminders')
            ->where('document_info_id', $documentInfoId)
            ->whereNull('deleted_at')
            ->get();

        if ($reminders->isEmpty()) {
            return [];
        }

        $employees = $this->employeeLoginEmails();

        return $reminders->map(function ($r) use ($employees, $emails) {
            $recipients = DB::table('document_info_reminder_employees')
                ->where('document_info_reminder_id', $r->id)
                ->pluck('employee_id')
                ->map(fn ($id) => $employees[$id] ?? null)
                ->filter()
                ->values()
                ->all();

            // A reminder can be addressed to a group; expand it to its members
            // so no recipient is lost on the way across.
            $groupEmails = DB::table('document_info_reminder_groups')
                ->where('document_info_reminder_id', $r->id)
                ->join('employee_group_members', 'employee_group_members.employee_group_id', '=', 'document_info_reminder_groups.employee_group_id')
                ->whereNull('employee_group_members.deleted_at')
                ->join('employees', 'employees.id', '=', 'employee_group_members.employee_id')
                ->pluck('employees.email')
                ->filter()
                ->all();

            $recipients = array_values(array_unique(array_merge($recipients, $groupEmails)));

            return [
                'id'                     => (int) $r->id,
                'subject'                => $r->subject,
                'message'                => $r->message,
                'is_repeat'              => (bool) $r->is_repeat_reminder,
                'send_email'             => (bool) $r->is_send_email,
                'single_reminder_date'   => $this->iso($r->single_reminder_date),
                'frequency'              => $r->frequency,
                'repeat_reminder_start'  => $this->iso($r->repeat_reminder_start),
                'repeat_reminder_end'    => $this->iso($r->repeat_reminder_end),
                'created_by_email'       => $emails[$r->created_by] ?? null,
                'recipient_emails'       => $recipients,
            ];
        })->values()->all();
    }

    /** user id => email, for created_by / updated_by columns. */
    /**
     * Employee id => the email that identifies them in the other system.
     *
     * `employees.email` is a personal address; the account people actually sign
     * in with lives on `users`. Operations matches staff by their login email,
     * so sending the employee address silently drops every permission grant it
     * cannot resolve. The employee address is kept only as a fallback for
     * records with no user account behind them.
     */
    private function employeeLoginEmails()
    {
        return DB::table('employees as e')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->get(['e.id', 'e.email as employee_email', 'u.email as user_email'])
            ->mapWithKeys(fn ($r) => [$r->id => $r->user_email ?: $r->employee_email]);
    }

    private function emailLookup(): array
    {
        return DB::table('users')->pluck('email', 'id')->all();
    }

    private function relative(?string $path, ?string $name): ?string
    {
        if (! $path || ! $name) {
            return null;
        }

        return self::ROOT . trim($path, '/') . '/' . $name;
    }

    private function existsOn(?string $path, ?string $name): bool
    {
        $relative = $this->relative($path, $name);

        return $relative ? Storage::disk('local')->exists($relative) : false;
    }

    private function sizeOf(?string $path, ?string $name): ?int
    {
        $relative = $this->relative($path, $name);

        if (! $relative || ! Storage::disk('local')->exists($relative)) {
            return null;
        }

        return Storage::disk('local')->size($relative);
    }

    private function iso($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toISOString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
