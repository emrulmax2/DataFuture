<?php

namespace App\Http\Controllers\SlcRegistration;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Services\SlcRegistration\Checks;
use App\Services\SlcRegistration\RegistrationActions;
use App\Services\SlcRegistration\RegistrationState;
use App\Services\SlcRegistration\SlcRegistrationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'multiple' => ['students' => RegistrationState::duplicateAgreements()],
        ]);
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

    public function attConfirm(Request $request)
    {
        $request->validate(['row_ids' => 'required|array', 'row_ids.*' => 'integer']);

        return $this->run(fn () => $this->attendance->confirm(array_map('intval', $request->row_ids)));
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
            $data['file'] = ['name' => $file['name'], 'text' => $file['text']];
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
            $data['file'] = ['name' => $file['name'], 'text' => $file['text']];
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
            'dashboard' => route('staff.dashboard'),
        ];
    }

    /** Settings › Branding `site_logo` (the white lockup), as the admission top bar uses it. */
    private function logo(): string
    {
        $file = Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');

        return $file && Storage::disk('local')->exists('public/'.$file) ? Storage::disk('local')->url('public/'.$file) : asset('build/assets/images/red_and_white_logo.png');
    }
}
