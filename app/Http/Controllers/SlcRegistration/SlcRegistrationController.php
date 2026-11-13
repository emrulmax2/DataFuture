<?php

namespace App\Http\Controllers\SlcRegistration;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\EmailController;
use App\Http\Controllers\Student\SmsController;
use App\Http\Requests\SendEmailRequest;
use App\Http\Requests\SendSmsRequest;
use App\Models\Option;
use App\Models\StudentContact;
use App\Services\SlcRegistration\Checks;
use App\Services\SlcRegistration\ModuleReset;
use App\Services\SlcRegistration\RegistrationActions;
use App\Services\SlcRegistration\RegistrationState;
use App\Services\SlcRegistration\SlcRegistrationException;
use App\Services\SlcRegistration\StudentComms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * /slc-registration — SLC bulk registration confirmation (user's "SLC Registration Check"
 * prototype, 2 Nov 2026). The page is one Vue app; it gets everything from state() and every
 * action answers with the message to flash and a fresh state. The work itself is in
 * App\Services\SlcRegistration. Access: privilege "SLC Registration" (dashboard), enforced on
 * slc.registration* from day one (config/privileges.php).
 */
class SlcRegistrationController extends Controller
{
    public function __construct(private RegistrationState $state, private RegistrationActions $actions, private \App\Services\SlcRegistration\AttendanceActions $attendance, private \App\Services\SlcRegistration\PaymentActions $payments)
    {
    }

    public function index()
    {
        return view('pages.slc-registration.index', [
            'title' => 'SLC Registration - London Churchill College',
            'user' => $this->userBox(),
            'logo' => $this->logo(),
            'state' => $this->state->build(),
        ]);
    }

    /** Dashboard › "Duplicate agreements": the students with 2+ agreements for the same year on the same course, on a page of its own. */
    public function multipleAgreements()
    {
        return view('pages.slc-registration.index', [
            'title' => 'Duplicate agreements - SLC Registration - London Churchill College',
            'user' => $this->userBox(),
            'logo' => $this->logo(),
            'page' => 'multiple',
            'state' => null,
            'multiple' => ['students' => RegistrationState::duplicateAgreements(), 'hidden' => RegistrationState::duplicateAgreements(true)],
        ]);
    }

    /** Duplicate agreements › Reviewed & Hide / Unhide: the message and both lists back. */
    public function duplicateReview(Request $request)
    {
        $request->validate(['student_id' => 'required|integer', 'crel_id' => 'nullable|integer', 'year' => 'required|integer', 'agreement_ids' => 'required|array|min:2', 'agreement_ids.*' => 'integer']);

        return $this->duplicateRun(fn () => $this->actions->reviewDuplicate((int) $request->student_id, $request->crel_id ? (int) $request->crel_id : null, (int) $request->year, $request->agreement_ids));
    }

    public function duplicateUnhide(Request $request)
    {
        $request->validate(['student_id' => 'required|integer', 'crel_id' => 'nullable|integer', 'year' => 'required|integer']);

        return $this->duplicateRun(fn () => $this->actions->unhideDuplicate((int) $request->student_id, $request->crel_id ? (int) $request->crel_id : null, (int) $request->year));
    }

    private function duplicateRun(callable $fn)
    {
        try {
            $text = $fn();
            $tone = 'ok';
            $status = 200;
        } catch (SlcRegistrationException $e) {
            [$text, $tone, $status] = [$e->getMessage(), 'bad', 422];
        }

        return response()->json(['flash' => ['text' => $text, 'tone' => $tone], 'multiple' => ['students' => RegistrationState::duplicateAgreements(), 'hidden' => RegistrationState::duplicateAgreements(true)]], $status);
    }

    public function state()
    {
        return response()->json($this->state->build());
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|max:20480']);
        $file = $request->file('file');

        return $this->run(fn () => $this->actions->import((string) file_get_contents($file->getRealPath()), $file->getClientOriginalName()));
    }

    /* Bulk attendance, steps 1–2: the SIS Attendance Confirmation Report, students matched by SSN */

    public function attUpload(Request $request)
    {
        $request->validate(['file' => 'required|file|max:20480']);
        $file = $request->file('file');

        return $this->run(fn () => $this->attendance->import((string) file_get_contents($file->getRealPath()), $file->getClientOriginalName()));
    }

    public function attLink(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'student_id' => 'required|integer']);

        return $this->run(fn () => $this->attendance->link((int) $request->row_id, (int) $request->student_id));
    }

    public function attRoute(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'key' => 'required|in:'.implode(',', array_keys(\App\Services\SlcRegistration\AttendanceChecks::FIELD_ROUTES)), 'route' => 'required|in:update,coc,contact,accept']);

        return $this->run(fn () => $this->attendance->setRoute((int) $request->row_id, $request->key, $request->route));
    }

    /** Data check › Name row: one action for the first and last name differences (all or none). */
    public function attRoutes(Request $request)
    {
        $keys = implode(',', array_keys(\App\Services\SlcRegistration\AttendanceChecks::FIELD_ROUTES));
        $request->validate(['row_id' => 'required|integer', 'keys' => 'required|array|min:1', 'keys.*' => 'in:'.$keys, 'route' => 'required|in:update,coc,contact,accept']);

        return $this->run(fn () => $this->attendance->setRoutes((int) $request->row_id, $request->keys, $request->route));
    }

    public function attClearRoutes(Request $request)
    {
        $keys = implode(',', array_keys(\App\Services\SlcRegistration\AttendanceChecks::FIELD_ROUTES));
        $request->validate(['row_id' => 'required|integer', 'keys' => 'required|array|min:1', 'keys.*' => 'in:'.$keys]);

        return $this->run(fn () => $this->attendance->clearRoutes((int) $request->row_id, $request->keys));
    }

    public function attClearRoute(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'key' => 'required|string|max:20']);

        return $this->run(fn () => $this->attendance->clearRoute((int) $request->row_id, $request->key));
    }

    public function attSuggested(Request $request)
    {
        $request->validate(['row_ids' => 'required|array', 'row_ids.*' => 'integer', 'key' => 'nullable|in:'.implode(',', array_keys(\App\Services\SlcRegistration\AttendanceChecks::FIELD_ROUTES))]);

        return $this->run(fn () => $this->attendance->applySuggested(array_map('intval', $request->row_ids), $request->key));
    }

    public function attCode(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'code' => 'nullable|string|max:2']);

        return $this->run(fn () => $this->attendance->setCode((int) $request->row_id, $request->code));
    }

    /** Step 4 › Session Term / Attendance Term for the SLC tab record (user, 13 Nov 2026); empty = the suggested one. */
    public function attRecordTerm(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'session_term' => 'nullable|integer|min:1|max:4', 'term_declaration_id' => 'nullable|integer']);

        return $this->run(fn () => $this->attendance->setRecordTerm((int) $request->row_id, $request->filled('session_term') ? (int) $request->session_term : null,
            $request->filled('term_declaration_id') ? (int) $request->term_declaration_id : null));
    }

    public function attConfirm(Request $request)
    {
        $request->validate(['row_ids' => 'required|array', 'row_ids.*' => 'integer', 'confirmed' => 'nullable|boolean', 'include_ids' => 'nullable|array', 'include_ids.*' => 'integer']);
        $ids = array_map('intval', $request->row_ids);
        // (user, 13 Nov 2026) ticked students whose period is already on the SLC tab: the page asks first (409 + the
        // list), then sends the ones to put in the batch anyway — the others are left out
        if (!$request->boolean('confirmed')) {
            $existing = $this->attendance->alreadyOnSlcTab($ids);
            if ($existing) {
                return response()->json(['existing' => $existing, 'others' => count($ids) - count($existing)], 409);
            }
        }

        return $this->run(fn () => $this->attendance->confirm($ids, array_map('intval', $request->include_ids ?? [])));
    }

    public function attUndo(Request $request)
    {
        $request->validate(['row_id' => 'required|integer']);

        return $this->run(fn () => $this->attendance->undoConfirm((int) $request->row_id));
    }

    public function attCocDone(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'done' => 'required|boolean']);

        return $this->run(fn () => $this->attendance->cocDone((int) $request->row_id, (bool) $request->done));
    }

    public function attDownload(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20']);
        $file = null;
        $response = $this->run(function () use ($request, &$file) {
            $file = $this->attendance->download($request->code);

            return $file['message'];
        });
        if ($file) {
            $data = $response->getData(true);
            $data['file'] = ['name' => $file['name'], 'text' => $file['text'], 'rebuilt' => $file['rebuilt'] ?? false];
            $response->setData($data);
        }

        return $response;
    }

    public function attBatchFile(Request $request)
    {
        try {
            return response()->json($this->attendance->fileText((string) $request->query('code')));
        } catch (SlcRegistrationException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function attUploaded(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20']);

        return $this->run(fn () => $this->attendance->markUploaded($request->code));
    }

    /** Batches › View students › Rejected by SIS (user, 13 Nov 2026): the ticked lines back to step 4, to send again. */
    public function attRejected(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20', 'row_ids' => 'required|array|min:1', 'row_ids.*' => 'integer', 'reason' => 'nullable|string|max:255']);

        return $this->run(fn () => $this->attendance->markRejected($request->code, array_map('intval', $request->row_ids), (string) $request->reason));
    }

    public function attRejectedUndo(Request $request)
    {
        $request->validate(['row_id' => 'required|integer']);

        return $this->run(fn () => $this->attendance->undoRejected((int) $request->row_id));
    }

    /** Batches › Regenerate file (user, 13 Nov 2026): the batch's file built again, dated as before or today. */
    public function attRegenerateBatch(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20', 'date' => 'required|in:original,today']);
        $file = null;
        $response = $this->run(function () use ($request, &$file) {
            $file = $this->attendance->regenerate($request->code, $request->date === 'today');

            return $file['message'];
        });
        if ($file) {
            $data = $response->getData(true);
            $data['file'] = ['name' => $file['name'], 'text' => $file['text'], 'rebuilt' => false];
            $response->setData($data);
        }

        return $response;
    }

    /** Batches › View students: each line's attendance and instalment, to be recorded or recorded (user, 13 Nov 2026). */
    public function attBatchPreview(Request $request)
    {
        try {
            return response()->json($this->attendance->batchPreview((string) $request->query('code')));
        } catch (SlcRegistrationException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Batches › View students › Bulk Attendance & Installment (user, 13 Nov 2026): the ticked students' attendance and
     * instalment, always new rows — a term already on the SLC tab is passed over for the next free one.
     */
    public function attBulkAttendance(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20', 'row_ids' => 'required|array|min:1', 'row_ids.*' => 'integer']);

        return $this->run(fn () => $this->attendance->bulkAttendance($request->code, array_map('intval', $request->row_ids)));
    }

    /* ---------------------------------------------------- Bulk payment upload */

    public function payUpload(Request $request)
    {
        $request->validate(['file' => 'required|file|max:20480']);
        $file = $request->file('file');

        return $this->run(fn () => $this->payments->import($file->getRealPath(), $file->getClientOriginalName()));
    }

    public function payLink(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'student_id' => 'required|integer']);

        return $this->run(fn () => $this->payments->link((int) $request->row_id, (int) $request->student_id));
    }

    public function payUnlink(Request $request)
    {
        $request->validate(['row_id' => 'required|integer']);

        return $this->run(fn () => $this->payments->unlink((int) $request->row_id));
    }

    public function payAgreement(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'agreement_id' => 'nullable|integer']);

        return $this->run(fn () => $this->payments->chooseAgreement((int) $request->row_id, $request->agreement_id ? (int) $request->agreement_id : null));
    }

    public function paySkip(Request $request)
    {
        $request->validate(['row_ids' => 'required|array', 'row_ids.*' => 'integer', 'reason' => 'nullable|string|max:190']);

        return $this->run(fn () => $this->payments->skip(array_map('intval', $request->row_ids), $request->reason));
    }

    public function payUnskip(Request $request)
    {
        $request->validate(['row_id' => 'required|integer']);

        return $this->run(fn () => $this->payments->unskip((int) $request->row_id));
    }

    public function payRecord(Request $request)
    {
        $request->validate(['row_ids' => 'required|array|max:2000', 'row_ids.*' => 'integer']);

        return $this->run(fn () => $this->payments->record(array_map('intval', $request->row_ids)));
    }

    public function payOpen(Request $request)
    {
        $request->validate(['id' => 'required|integer']);

        return $this->run(fn () => $this->payments->open((int) $request->id));
    }

    /** The payments still waiting in Reports › Accounts › Payments Upload, brought in as an upload. */
    public function payBringIn()
    {
        return $this->run(fn () => $this->payments->bringFromReports());
    }

    /** Uploads › every remittance recorded in bulk (here or in Reports), by remittance date. */
    public function payHistory()
    {
        return response()->json(['dates' => \App\Services\SlcRegistration\PaymentState::history()]);
    }

    /** Uploads › View: the bulk receipts of one remittance date. */
    public function payHistoryDate(Request $request)
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);

        return response()->json(\App\Services\SlcRegistration\PaymentState::historyDate($request->query('date')));
    }

    /** The remittance file as it was uploaded. */
    public function payFile(Request $request)
    {
        try {
            $f = $this->payments->file((int) $request->query('id'));
        } catch (SlcRegistrationException $e) {
            abort(404, $e->getMessage());
        }
        abort_unless(Storage::disk($f['disk'])->exists($f['path']), 404);

        return Storage::disk($f['disk'])->download($f['path'], $f['name']);
    }

    public function attUnlink(Request $request)
    {
        $request->validate(['row_id' => 'required|integer']);

        return $this->run(fn () => $this->attendance->unlink((int) $request->row_id));
    }

    public function students(Request $request)
    {
        return response()->json(['students' => $this->actions->searchStudents((string) $request->query('q', ''))]);
    }

    public function link(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'student_id' => 'required|integer']);

        return $this->run(fn () => $this->actions->link((int) $request->row_id, (int) $request->student_id));
    }

    public function unlink(Request $request)
    {
        $request->validate(['row_id' => 'required|integer']);

        return $this->run(fn () => $this->actions->unlink((int) $request->row_id));
    }

    public function setRoute(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'key' => 'required|in:'.implode(',', array_keys(Checks::FIELD_ROUTES)), 'route' => 'required|in:update,coc,contact,accept']);

        return $this->run(fn () => $this->actions->setRoute((int) $request->row_id, $request->key, $request->route));
    }

    /** Data check › a merged row (Name, Address, Course): one decision for several fields. */
    public function setRoutes(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'keys' => 'required|array|min:1', 'keys.*' => 'in:'.implode(',', array_keys(Checks::FIELD_ROUTES)), 'route' => 'required|in:update,coc,contact,accept']);

        return $this->run(fn () => $this->actions->setRoutes((int) $request->row_id, $request->keys, $request->route));
    }

    public function clearRoutes(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'keys' => 'required|array|min:1', 'keys.*' => 'in:'.implode(',', array_keys(Checks::FIELD_ROUTES))]);

        return $this->run(fn () => $this->actions->clearRoutes((int) $request->row_id, $request->keys));
    }

    public function clearRoute(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'key' => 'required|string|max:20']);

        return $this->run(fn () => $this->actions->clearRoute((int) $request->row_id, $request->key));
    }

    public function suggested(Request $request)
    {
        $request->validate(['row_ids' => 'required|array', 'row_ids.*' => 'integer', 'key' => 'nullable|in:'.implode(',', array_keys(\App\Services\SlcRegistration\Checks::FIELD_ROUTES))]);

        return $this->run(fn () => $this->actions->applySuggested(array_map('intval', $request->row_ids), $request->key));
    }

    public function action(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'key' => 'required|string|max:20', 'state' => 'required|in:issued,contacted,done']);

        return $this->run(fn () => $this->actions->markAction((int) $request->row_id, $request->key, $request->state));
    }

    public function decide(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'd' => 'required|in:notreg,hold', 'reason' => 'nullable|string|max:191']);

        return $this->run(fn () => $this->actions->decide((int) $request->row_id, $request->d, $request->reason));
    }

    public function attendance(Request $request)
    {
        $request->validate(['row_id' => 'required|integer', 'checked' => 'required|boolean']);

        return $this->run(fn () => $this->actions->attendanceChecked((int) $request->row_id, (bool) $request->checked));
    }

    public function undo(Request $request)
    {
        $request->validate(['row_id' => 'required|integer']);

        return $this->run(fn () => $this->actions->undoDecision((int) $request->row_id));
    }

    public function confirm(Request $request)
    {
        $request->validate(['row_ids' => 'required|array', 'row_ids.*' => 'integer', 'checked' => 'nullable|array', 'checked.*' => 'integer']);

        return $this->run(fn () => $this->actions->confirmYes(array_map('intval', $request->row_ids), array_map('intval', $request->checked ?? [])));
    }

    public function download(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20']);
        $file = null;
        $response = $this->run(function () use ($request, &$file) {
            $file = $this->actions->download($request->code);

            return $file['message'];
        });
        if ($file) {
            $data = $response->getData(true);
            $data['file'] = ['name' => $file['name'], 'text' => $file['text'], 'rebuilt' => $file['rebuilt'] ?? false];
            $response->setData($data);
        }

        return $response;
    }

    /** Batches › Regenerate file (user, 12 Nov 2026): built again from the batch, saved, handed back to download. */
    public function regenerateBatch(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20', 'date' => 'required|in:original,today', 'number' => 'nullable|integer|min:1|max:99999']);
        $file = null;
        $response = $this->run(function () use ($request, &$file) {
            $file = $this->actions->regenerate($request->code, $request->date === 'today', $request->filled('number') ? (int) $request->number : null);

            return $file['message'];
        });
        if ($file) {
            $data = $response->getData(true);
            $data['file'] = ['name' => $file['name'], 'text' => $file['text'], 'rebuilt' => false];
            $response->setData($data);
        }

        return $response;
    }

    public function batchFile(Request $request)
    {
        try {
            return response()->json($this->actions->fileText((string) $request->query('code')));
        } catch (SlcRegistrationException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function uploaded(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20']);

        return $this->run(fn () => $this->actions->markUploaded($request->code));
    }

    public function bulkRegister(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20', 'row_ids' => 'required|array|min:1', 'row_ids.*' => 'integer', 'confirmed' => 'nullable|boolean', 'overwrite_ids' => 'nullable|array', 'overwrite_ids.*' => 'integer']);
        $ids = array_map('intval', $request->row_ids);

        // ticked students who already have a registration or agreement for the year: the page asks,
        // student by student, before creating new ones (409 + the list); then it sends who was permitted
        if (!$request->boolean('confirmed')) {
            try {
                $existing = $this->actions->existingRecords($request->code, $ids);
            } catch (SlcRegistrationException $e) {
                return response()->json(['flash' => ['text' => $e->getMessage(), 'tone' => 'bad'], 'state' => $this->state->build()], 422);
            }
            if ($existing) {
                return response()->json(['existing' => $existing, 'others' => count($ids) - count($existing)], 409);
            }
        }

        return $this->run(fn () => $this->actions->bulkRegister($request->code, $ids, array_map('intval', $request->overwrite_ids ?? [])));
    }

    public function mapCourse(Request $request)
    {
        $request->validate(['code' => 'required|string|max:30', 'venue_id' => 'required|integer']);

        return $this->run(fn () => $this->actions->mapCourse($request->code, (int) $request->venue_id));
    }

    /** {flash, state}: 200 when it worked, 422 with the reason when it was refused. */
    /** Clear DB (user, 6 Nov 2026; users 1 and 7 only): what clearing a module would delete. */
    public function clearCounts(Request $request)
    {
        abort_unless(ModuleReset::allowed(Auth::id()), 403);
        $request->validate(['module' => 'required|in:'.implode(',', array_keys(ModuleReset::MODULES))]);

        return response()->json(['counts' => app(ModuleReset::class)->counts((string) $request->query('module'))]);
    }

    /** Clear DB: empties the module's own tables (ModuleReset). Needs the word CLEAR typed. */
    public function clearData(Request $request)
    {
        abort_unless(ModuleReset::allowed(Auth::id()), 403);
        $request->validate(['module' => 'required|in:'.implode(',', array_keys(ModuleReset::MODULES)), 'confirm' => 'required|in:CLEAR']);

        return $this->run(fn () => app(ModuleReset::class)->clear($request->module, (int) Auth::id()));
    }

    /** Actions › Email / SMS (user, 6 Nov 2026): who the dialog sends to, the SMTP accounts and the templates. */
    public function comms(Request $request)
    {
        $request->validate(['student_id' => 'required|integer']);
        try {
            return response()->json(app(StudentComms::class)->options((int) $request->query('student_id')));
        } catch (SlcRegistrationException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /** The dialog's Template list: the template's text to fill the form with. */
    public function commsTemplate(Request $request)
    {
        $request->validate(['type' => 'required|in:email,sms', 'id' => 'required|integer']);

        return response()->json(app(StudentComms::class)->template((string) $request->query('type'), (int) $request->query('id')));
    }

    /**
     * Actions › Email: sent by the Communication tab's own sender (Student\EmailController::store —
     * the same SMTP, BCC, attachments, PDF copy and record on the student's Communication tab), then
     * noted in Activity and, from a Contact student line, marked contacted when asked.
     */
    public function sendEmail(SendEmailRequest $request)
    {
        $request->validate($this->commsRules() + ['documents' => 'nullable|array', 'documents.*' => 'file']);
        try {
            $res = app(EmailController::class)->store($request);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['flash' => ['text' => 'The email could not be sent: '.Str::limit($e->getMessage(), 200), 'tone' => 'bad']], 500);
        }
        if ($res->getStatusCode() !== 200) {
            return response()->json(['flash' => ['text' => $res->getData()->message ?? 'The email could not be sent. Please try again.', 'tone' => 'bad']], 422);
        }

        return $this->run(fn () => $this->actions->messageSent('email', (int) $request->input('student_id'), (string) $request->input('subject'), true, $request->input('row_id') ? (int) $request->input('row_id') : null, $request->input('key'), $request->boolean('mark_contacted')));
    }

    /** Actions › SMS: sent by the Communication tab's own sender (Student\SmsController::store), then as sendEmail. */
    public function sendSms(SendSmsRequest $request)
    {
        $request->validate($this->commsRules());
        $contact = StudentContact::where('student_id', $request->input('student_id'))->first();
        if (!$contact) {
            // the Communication tab's sender can't save an SMS without the student's contact record
            return response()->json(['flash' => ['text' => 'The SMS was not sent: the student has no contact details (no mobile number) on record.', 'tone' => 'bad']], 422);
        }
        try {
            $res = app(SmsController::class)->store($request);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['flash' => ['text' => 'The SMS could not be sent: '.Str::limit($e->getMessage(), 200), 'tone' => 'bad']], 500);
        }
        if ($res->getStatusCode() !== 200) {
            return response()->json(['flash' => ['text' => $res->getData()->message ?? 'The SMS could not be sent. Please try again.', 'tone' => 'bad']], 422);
        }

        return $this->run(fn () => $this->actions->messageSent('sms', (int) $request->input('student_id'), (string) $request->input('subject'), !empty($contact->mobile), $request->input('row_id') ? (int) $request->input('row_id') : null, $request->input('key'), $request->boolean('mark_contacted')));
    }

    /** Email / SMS: the student, and the Contact student line it was sent from (if any). */
    private function commsRules(): array
    {
        return ['student_id' => 'required|integer|exists:students,id', 'row_id' => 'nullable|integer', 'key' => 'nullable|string|max:20', 'mark_contacted' => 'nullable|boolean'];
    }

    private function run(callable $fn)
    {
        try {
            $text = $fn();

            return response()->json(['flash' => ['text' => $text, 'tone' => 'ok'], 'state' => $this->state->build()]);
        } catch (SlcRegistrationException $e) {
            return response()->json(['flash' => ['text' => $e->getMessage(), 'tone' => 'bad'], 'state' => $this->state->build()], 422);
        }
    }

    /** The header's signed-in user, as the other top bars show it. */
    private function userBox(): array
    {
        $user = Auth::user()->loadMissing('employee.title', 'employee.employment.employeeJobTitle');
        $emp = $user->employee;
        $first = trim((string) ($emp->first_name ?? ''));
        $last = trim((string) ($emp->last_name ?? ''));
        $name = trim((isset($emp?->title?->name) ? $emp->title->name.' ' : '').$first.' '.$last) ?: ($user->name ?? 'Staff');
        $words = preg_split('/\s+/', $first !== '' && $last !== '' ? $first.' '.$last : $name);
        $avatar = null;
        if ($emp && $emp->photo && Storage::disk('local')->exists('public/employees/'.$emp->id.'/'.$emp->photo)) {
            $avatar = Storage::disk('local')->url('public/employees/'.$emp->id.'/'.$emp->photo);
        }

        return [
            'name' => $name,
            'role' => $emp?->employment?->employeeJobTitle?->name ?? 'Staff',
            'email' => (string) $user->email,
            'initials' => strtoupper(mb_substr($words[0] ?? 'L', 0, 1).mb_substr(count($words) > 1 ? end($words) : 'C', 0, 1)),
            'avatar' => $avatar,
            'dashboard' => route('dashboard'), // the site's home page (header Dashboard link)
            'canClear' => ModuleReset::allowed($user->id), // Clear DB (users 1 and 7)
        ];
    }

    /** Settings › Branding `site_logo` (the white lockup), as the admission top bar uses it. */
    private function logo(): string
    {
        $file = Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');

        return $file && Storage::disk('local')->exists('public/'.$file) ? Storage::disk('local')->url('public/'.$file) : asset('build/assets/images/red_and_white_logo.png');
    }
}
