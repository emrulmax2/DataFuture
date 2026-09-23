<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryBookIssue;
use App\Models\LibraryDeposit;
use App\Models\Student;
use App\Services\LibraryFinePayment;
use App\Services\LibraryRules;
use App\Services\OperationsLibraryClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The library desk: the staff side of `library_book_issues`.
 *
 * Students raise rows here by reserving; staff act on the same rows when they
 * hand a book over and when it comes back. One table, because the desk and the
 * student are looking at one event — two would have to be reconciled forever.
 *
 * Copy status lives in Operations, so every state change is mirrored there:
 * reserved on request, on_loan at issue, available on return.
 */
class IssueDeskController extends Controller
{
    public function __construct(
        private OperationsLibraryClient $catalogue,
        private LibraryFinePayment $fines,
    ) {
    }

    /**
     * Only staff who hold the library privilege may see or move a loan — this
     * screen exposes every student's borrowing history.
     */
    private function guard(): void
    {
        $priv = auth()->user()->priv();

        abort_unless(
            (isset($priv['library_management']) && $priv['library_management'] == 1)
                || in_array(auth()->id(), [1, 7], true),
            403,
            'You are not permitted to manage library issues.'
        );
    }

    public function index(Request $request)
    {
        $this->guard();

        $type = $request->query('type', 'all');

        return view('pages.library.management.index', [
            'title' => 'Library Management - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Library Management', 'href' => 'javascript:void(0);'],
            ],
            'rules' => new LibraryRules(),
            /* `all` is the default view — the grid is fetched, so landing on a
               filter that can be empty while the tiles show counts would read
               as a broken page. */
            'status' => $request->query('status', 'all'),
            'search' => trim((string) $request->query('q')),
            'type' => $type,
            'overdue' => $request->boolean('overdue'),
            'counts' => [
                'requested' => LibraryBookIssue::where('status', LibraryBookIssue::STATUS_REQUESTED)->count(),
                'issued' => LibraryBookIssue::where('status', LibraryBookIssue::STATUS_ISSUED)->count(),
                'overdue' => LibraryBookIssue::where('status', LibraryBookIssue::STATUS_ISSUED)
                    ->whereNotNull('due_at')
                    ->whereDate('due_at', '<', Carbon::now()->startOfDay())
                    ->count(),
                'day_reading' => LibraryBookIssue::dayReading()
                    ->where('status', LibraryBookIssue::STATUS_ISSUED)
                    ->count(),
            ],
            'money' => $this->deskMoney(),
            'tabCounts' => LibraryBookIssue::selectRaw('status, count(*) as n')
                ->when(
                    in_array($type, [LibraryBookIssue::TYPE_TAKE_HOME, LibraryBookIssue::TYPE_DAY_READING], true),
                    fn ($q) => $q->where('loan_type', $type)
                )
                ->groupBy('status')
                ->pluck('n', 'status')
                ->toArray(),
        ]);
    }

    /**
     * The two money figures the desk is accountable for.
     *
     * Fines are two different things and both are owed: a loan still out
     * accrues by the day and is only known by asking the rules, while a
     * settled loan has its charge frozen on the row. Counting only one of
     * them would understate the till.
     */
    private function deskMoney(): array
    {
        $rules = new LibraryRules();

        $accruing = LibraryBookIssue::where('status', LibraryBookIssue::STATUS_ISSUED)
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', Carbon::now()->startOfDay())
            ->get()
            ->sum(fn ($loan) => $rules->fineFor($loan));

        $unpaid = (float) LibraryBookIssue::whereNot('status', LibraryBookIssue::STATUS_ISSUED)
            ->whereNull('fine_paid_at')
            ->sum('fine_amount');

        return [
            'fines' => round($accruing + $unpaid, 2),
            'deposits' => (float) LibraryDeposit::deposits()->where('status', 'paid')->sum('amount'),
        ];
    }

    /**
     * The money ledger: every deposit and every fine, in one list.
     *
     * Deposits and fine payments both live on `library_deposits`; an unpaid
     * fine has no payment row yet, so it is read off the loan that carries the
     * charge. The three sources are merged into one shape here rather than
     * shown as three tables, because the question the desk is answering —
     * "what does this student owe, and what are we holding for them" — spans
     * all of them.
     */
    public function money(Request $request)
    {
        $this->guard();

        $filter = $request->query('show', 'all');
        $status = (string) $request->query('status', '');
        $search = trim((string) $request->query('q'));
        $rules = new LibraryRules();

        $rows = collect();

        /* 1. Deposits and settled fines — anything with a payment row. */
        $ledger = LibraryDeposit::with('student', 'issue')
            ->when($filter === 'deposits', fn ($q) => $q->deposits())
            ->when($filter === 'fines', fn ($q) => $q->fines())
            ->orderByDesc('id')
            ->get();

        foreach ($ledger as $row):
            $rows->push([
                'kind' => $row->isFine() ? 'fine' : 'deposit',
                'student' => $row->student,
                'amount' => (float) $row->amount,
                'status' => $row->status,
                'detail' => $row->description
                    ?: ($row->isFine() ? 'Fine payment' : 'Refundable deposit'),
                'reference' => $row->issue->reference ?? $row->provider_capture_id,
                'method' => $row->provider,
                'date' => $row->paid_at ?: $row->created_at,
                'outstanding' => false,
                'issue_id' => $row->library_book_issue_id,
            ]);
        endforeach;

        /* 2. Fines charged but not yet paid. A loan still out accrues by the
              day, so its figure comes from the rules; a closed loan has the
              charge frozen on the row. Neither has a payment row yet. */
        if ($filter !== 'deposits'):
            $charged = LibraryBookIssue::with('student')
                ->whereNull('fine_paid_at')
                ->where(function ($q) {
                    $q->where('fine_amount', '>', 0)
                        ->orWhere(function ($q2) {
                            $q2->where('status', LibraryBookIssue::STATUS_ISSUED)
                                ->whereNotNull('due_at')
                                ->whereDate('due_at', '<', Carbon::now()->startOfDay());
                        });
                })
                ->orderByDesc('id')
                ->get();

            foreach ($charged as $loan):
                $owed = $loan->status === LibraryBookIssue::STATUS_ISSUED
                    ? $rules->fineFor($loan)
                    : (float) $loan->fine_amount;

                if ($owed <= 0):
                    continue;
                endif;

                $rows->push([
                    'kind' => 'fine',
                    'student' => $loan->student,
                    'amount' => $owed,
                    'status' => 'outstanding',
                    'detail' => $loan->title,
                    'reference' => $loan->reference,
                    'method' => null,
                    'date' => $loan->due_at,
                    'outstanding' => true,
                    'issue_id' => $loan->id,
                ]);
            endforeach;
        endif;

        /* An abandoned checkout is not money. `depositCheckout()` writes the
           row before sending the student to PayPal, so backing out of the
           PayPal window leaves a `pending` row behind — listing it under a
           column headed Amount reads as a payment that never happened, and as
           a student who paid twice. Hidden unless asked for by name. */
        $rows = $status === ''
            ? $rows->where('status', '!=', 'pending')
            : $rows->where('status', $status);

        if ($search !== ''):
            $needle = mb_strtolower($search);
            $rows = $rows->filter(function ($r) use ($needle) {
                $hay = mb_strtolower(trim(
                    ($r['student']->first_name ?? '').' '.($r['student']->last_name ?? '').' '
                    .($r['student']->registration_no ?? '').' '.$r['detail'].' '.$r['reference']
                ));

                return str_contains($hay, $needle);
            });
        endif;

        $rows = $rows->sortByDesc(fn ($r) => $r['date'] ? $r['date']->timestamp : 0)->values();

        return view('pages.library.management.money', [
            'title' => 'Deposits & fines - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Library Management', 'href' => route('library.management')],
                ['label' => 'Deposits & fines', 'href' => 'javascript:void(0);'],
            ],
            'rows' => $rows,
            'filter' => $filter,
            'status' => $status,
            'search' => $search,
            'abandoned' => LibraryDeposit::where('status', 'pending')->count(),
            'totals' => [
                'held' => (float) LibraryDeposit::deposits()->where('status', 'paid')->sum('amount'),
                'refunded' => (float) LibraryDeposit::deposits()->where('status', 'refunded')->sum('amount'),
                'collected' => (float) LibraryDeposit::fines()->where('status', 'paid')->sum('amount'),
                'outstanding' => round($rows->where('outstanding', true)->sum('amount'), 2),
            ],
        ]);
    }

    /**
     * Rows for the Tabulator grid.
     *
     * Everything the table renders is prepared here rather than in the
     * formatters: fines, day counts and status wording all depend on the
     * settings, and duplicating those rules in JavaScript is how the desk and
     * the student portal end up disagreeing about whether a book is late.
     */
    public function list(Request $request)
    {
        $this->guard();

        $rules = new LibraryRules();

        $query = $this->filtered($request);

        $total = $query->count();
        $page = max((int) $request->input('page', 1), 1);
        $perPage = $request->input('size') === 'true'
            ? max($total, 1)
            : max((int) $request->input('size', 25), 1);

        $issues = $query->with(['student', 'logs'])
            ->orderByRaw("FIELD(status,'requested','issued','returned','cancelled','not_collected')")
            ->orderByDesc('id')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'last_page' => $total > 0 ? (int) ceil($total / $perPage) : 1,
            'data' => $issues->map(function (LibraryBookIssue $issue) use ($rules) {
                $fine = $rules->fineFor($issue);
                $student = $issue->student;

                return [
                    'id' => $issue->id,
                    'reference' => $issue->reference,
                    'status' => $issue->status,
                    'loan_type' => $issue->loan_type,
                    'is_day_reading' => $issue->isDayReading(),
                    'overdue' => $fine > 0,
                    'fine' => (float) ($fine ?: $issue->fine_amount),

                    /* A late return the desk has already submitted. The loan
                       is still `issued` and still accruing — the grid says so
                       rather than showing it as an ordinary overdue, because
                       the book is physically back and the only thing missing
                       is the money. */
                    'return_pending' => $issue->hasPendingReturn(),
                    'return_pending_fine' => (float) $issue->pending_return_fine,
                    'return_pending_until' => $issue->hasPendingReturn()
                        ? $issue->pending_return_expires_at->format('g:ia')
                        : null,
                    'return_pending_url' => $issue->hasPendingReturn()
                        ? route('library.management.charge.link', ['id' => $issue->id])
                        : null,

                    'title' => $issue->title,
                    'author' => $issue->author,
                    'barcode' => $issue->barcode,
                    'cover_url' => self::imageUrl($issue->cover_url),

                    'student_id' => optional($student)->id,
                    /* Built here rather than in JavaScript: the desk grid has no
                       Ziggy route for the student profile, and a hand-written
                       path would drift if the route ever moves. */
                    'student_url' => $student ? route('student.show', $student->id) : null,
                    'student_name' => optional($student)->full_name,
                    'registration_no' => optional($student)->registration_no,
                    'student_photo' => optional($student)->photo_url,
                    'student_initials' => $student
                        ? strtoupper(mb_substr((string) $student->first_name, 0, 1).mb_substr((string) $student->last_name, 0, 1))
                        : '?',

                    'campus' => $issue->campus,
                    'location' => $issue->location,

                    'booked_at' => optional($issue->requested_at)->format('j M Y'),
                    'expires_at' => optional($issue->expires_at)->format('j M Y'),
                    'issued_at' => optional($issue->issued_at)->format('j M Y'),
                    'due_at' => optional($issue->due_at)->format('j M Y'),
                    'returned_at' => optional($issue->returned_at)->format('j M Y'),

                    'logs' => $issue->logs->map(fn ($log) => [
                        'when' => $log->created_at->format('j M Y, H:i'),
                        'action' => ucfirst(str_replace('_', ' ', $log->action)),
                        'who' => $log->performed_by_name ?: ucfirst($log->performed_by_type),
                        'note' => $log->note,
                    ])->all(),
                ];
            })->all(),
        ]);
    }

    /**
     * Only emit a cover a browser can draw.
     *
     * 137 of the catalogue's 899 covers are PDFs — the upload accepts anything.
     * Operations now filters them, but rows snapshotted before that still hold
     * one, and an <img> pointed at a PDF renders a broken icon.
     */
    private static function imageUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $extension = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'], true) ? $url : null;
    }

    /** The filter set shared by the grid and the summary counts. */
    private function filtered(Request $request)
    {
        $status = $request->input('status', 'all');
        $type = $request->input('type', 'all');
        $search = trim((string) $request->input('q'));

        $query = LibraryBookIssue::query();

        if ($status && $status !== 'all'):
            $status === 'open' ? $query->open() : $query->where('status', $status);
        endif;

        if (in_array($type, [LibraryBookIssue::TYPE_TAKE_HOME, LibraryBookIssue::TYPE_DAY_READING], true)):
            $query->where('loan_type', $type);
        endif;

        if ($request->boolean('overdue')):
            $query->where('status', LibraryBookIssue::STATUS_ISSUED)
                ->whereNotNull('due_at')
                ->whereDate('due_at', '<', Carbon::now()->startOfDay());
        endif;

        if ($search !== ''):
            $query->where(function ($where) use ($search) {
                $where->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%')
                    ->orWhere('barcode', 'like', '%'.$search.'%')
                    ->orWhereHas('student', function ($s) use ($search) {
                        $s->where('registration_no', 'like', '%'.$search.'%')
                            ->orWhere('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%');
                    });
            });
        endif;

        return $query;
    }

    /* ------------------------------------------------------------------ */
    /* Day reading                                                         */
    /* ------------------------------------------------------------------ */

    /** Catalogue search at the desk, for issuing a book to read in the library. */
    public function searchCatalogue(Request $request)
    {
        $this->guard();

        $request->validate([
            'q' => ['nullable', 'string', 'max:191'],
            'venue' => ['nullable', 'max:191'],
        ]);

        $results = $this->catalogue->search($request->only(['q', 'venue']) + ['availability' => 'available']);

        if ($results === null):
            return response()->json(['ok' => false, 'message' => 'The catalogue is unavailable right now.'], 503);
        endif;

        return response()->json(['ok' => true] + $results);
    }

    /** Student lookup for the desk — registration number or name. */
    public function searchStudents(Request $request)
    {
        $this->guard();

        $term = trim((string) $request->query('q'));

        if (mb_strlen($term) < 2):
            return response()->json(['ok' => true, 'data' => []]);
        endif;

        /* No status filter: the desk needs to find whoever is standing in front
           of it, and the search is already narrow — registration number or
           name, minimum two characters, capped at 15 rows. */
        $students = Student::where(function ($where) use ($term) {
                $where->where('registration_no', 'like', '%'.$term.'%')
                    ->orWhere('first_name', 'like', '%'.$term.'%')
                    ->orWhere('last_name', 'like', '%'.$term.'%');
            })
            ->orderBy('first_name')
            ->limit(15)
            ->get();

        $rules = new LibraryRules();

        return response()->json([
            'ok' => true,
            'data' => $students->map(fn ($s) => [
                'id' => $s->id,
                'label' => trim($s->first_name.' '.$s->last_name),
                'registration_no' => $s->registration_no,
                /* Accessor falls back to a placeholder when the student has no
                   photo, so the desk always has something to show. */
                'photo_url' => $s->photo_url,
                /* Whether this student can take a book home, and if not why.
                   Day reading ignores it — a book read in the building needs no
                   deposit — so it is only acted on by the direct-issue panel. */
                'take_home_block' => $this->takeHomeBlock($s->id, $rules),
            ])->all(),
        ]);
    }

    /**
     * Issue a book for reading in the library.
     *
     * There is no reservation step: the student is standing at the desk, so the
     * copy is held and handed over in one move. It is due back the same day and
     * does not count against the take-home allowance, because it never leaves
     * the building.
     */
    public function issueDayReading(Request $request)
    {
        $this->guard();

        $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'title_id' => ['required', 'integer'],
            'staff_note' => ['nullable', 'string', 'max:255'],
        ]);

        $student = Student::findOrFail($request->student_id);
        $rules = new LibraryRules();

        $already = LibraryBookIssue::where('student_id', $student->id)
            ->dayReading()
            ->where('status', LibraryBookIssue::STATUS_ISSUED)
            ->where('ops_title_id', $request->title_id)
            ->exists();

        if ($already):
            return back()->with('library_error', 'That student already has this book out for day reading.');
        endif;

        $hold = $this->catalogue->holdCopy($request->title_id, [
            'student_id' => (string) $student->id,
            'registration_no' => (string) $student->registration_no,
            'student_name' => trim($student->first_name.' '.$student->last_name),
        ]);

        if (!$hold || empty($hold['copy'])):
            return back()->with('library_error', 'No copy of that title is free to issue.');
        endif;

        $copy = $hold['copy'];
        $book = $hold['title'] ?? [];

        /* Same guard as the student portal: the copy is already held, so a
           failed write must give it back rather than strand it. */
        try {
            $issue = LibraryBookIssue::create([
                'reference' => LibraryBookIssue::nextReference(),
                'student_id' => $student->id,
                'loan_type' => LibraryBookIssue::TYPE_DAY_READING,
                'ops_title_id' => (int) $request->title_id,
                'ops_copy_id' => $copy['id'] ?? null,
                'barcode' => $copy['barcode'] ?? null,
                'title' => $book['title'] ?? 'Library book',
                'author' => $book['author'] ?? null,
                'isbn13' => $book['isbn13'] ?? null,
                'cover_url' => $book['image_url'] ?? null,
                'campus' => $copy['campus'] ?? null,
                'location' => $copy['location'] ?? null,
                'book_price' => $book['price'] ?? null,
                'status' => LibraryBookIssue::STATUS_ISSUED,
                'requested_at' => Carbon::now(),
                'issued_at' => Carbon::now(),
                'issued_by' => auth()->id(),
                'due_at' => $rules->dayReadingDueDate(),
                'staff_note' => $request->input('staff_note'),
                'created_by' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            $this->catalogue->releaseCopy($copy['id'] ?? null, ['reason' => 'issue_failed']);

            Log::error('[Library] Day reading failed after the copy was held; copy released.', [
                'copy' => $copy['id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()->with('library_error', 'That could not be issued. Nothing has been held — please try again.');
        }

        $issue->log('issued', null, 'Day reading issued at the desk');

        /* Straight to on_loan: it was never on the hold shelf. */
        $this->catalogue->issueCopy($issue->ops_copy_id);

        return back()->with(
            'library_success',
            $issue->reference.' issued to '.$student->first_name.' '.$student->last_name.' for day reading — due back today.'
        );
    }

    /**
     * Record that a student has paid an outstanding charge.
     *
     * Two things happen together: the loan is stamped paid, which is what
     * lifts the borrowing block, and the money is written to the ledger as a
     * fine row so the till and the block can never disagree.
     */
    public function settleCharge(Request $request, $id)
    {
        $this->guard();

        $request->validate(['note' => ['nullable', 'string', 'max:191']]);

        $issue = LibraryBookIssue::with('student')->findOrFail($id);
        $rules = new LibraryRules();

        if ($issue->fine_paid_at):
            return back()->with('library_error', $issue->reference.' is already settled.');
        endif;

        /* A held return was quoted a figure when it was submitted, and that is
           what the student is standing there to pay. Otherwise: a book still
           out keeps accruing, so the amount is read at the moment it is paid
           rather than from a figure frozen earlier. */
        $amount = $issue->hasPendingReturn()
            ? (float) $issue->pending_return_fine
            : ($issue->isOpen() ? $rules->fineFor($issue) : (float) $issue->fine_amount);

        if ($amount <= 0):
            return back()->with('library_error', 'There is nothing outstanding on '.$issue->reference.'.');
        endif;

        DB::transaction(function () use ($issue, $amount, $request) {
            $issue->update([
                'fine_amount' => $amount,
                'fine_paid_at' => Carbon::now(),
                'updated_by' => auth()->id(),
            ]);

            LibraryDeposit::create([
                'student_id' => $issue->student_id,
                'type' => LibraryDeposit::TYPE_FINE,
                'library_book_issue_id' => $issue->id,
                'amount' => $amount,
                'currency' => 'GBP',
                'status' => 'paid',
                'description' => $request->input('note') ?: 'Overdue charge — '.$issue->title,
                'provider' => 'desk',
                'collected_by' => auth()->id(),
                'paid_at' => Carbon::now(),
            ]);

            $issue->log('fine_paid', null, '£'.number_format($amount, 2).' collected at the desk');
        });

        /* Any payment link handed out for this charge is now for money that is
           no longer owed, so it is stood down rather than left live. */
        $this->fines->voidPending($issue, 'Settled at the desk.');

        /* Cash finishes a held return exactly as the link would: the charge is
           what the return was waiting on, and it has just been paid. */
        $this->fines->applySubmission($issue->refresh(), $amount, 'collected at the desk');

        $name = trim(optional($issue->student)->first_name.' '.optional($issue->student)->last_name);

        return back()->with(
            'library_success',
            '£'.number_format($amount, 2).' recorded against '.$issue->reference.($name ? ' for '.$name : '').'.'
        );
    }

    /**
     * A title with its copies, for the direct-issue location picker. Each copy
     * reports its own campus, shelf and status.
     */
    public function title(Request $request, $titleId)
    {
        $this->guard();

        $title = $this->catalogue->title($titleId);

        if ($title === null):
            return response()->json(['ok' => false, 'message' => 'That book could not be loaded.'], 503);
        endif;

        return response()->json(['ok' => true, 'data' => $title]);
    }

    /**
     * Staff-facing reason a student cannot take a book home, or null.
     *
     * Same decision as the student portal (LibraryRules::blockedCode) — only
     * the wording differs, since here it is read by staff about the student.
     */
    private function takeHomeBlock(int $studentId, LibraryRules $rules): ?string
    {
        return match ($rules->blockedCode($studentId, (bool) LibraryDeposit::heldFor($studentId))) {
            'deposit' => 'No library deposit paid. The student needs to pay it from their portal before borrowing.',
            'max_books' => 'Already holding the maximum of '.$rules->maxBooks().' take-home books.',
            'overdue' => 'Has an overdue book that must be returned first.',
            'charges' => 'Owes £'.number_format($rules->outstandingCharges($studentId), 2).' in unpaid library charges.',
            default => null,
        };
    }

    /**
     * Issue a take-home book straight to a student at the desk.
     *
     * The reservation and the collection happen in one step, so the copy is
     * held and handed over together and the loan clock starts now. Everything
     * the student portal enforces is re-checked here — the desk skipping the
     * reservation must not mean skipping the deposit or the allowance.
     */
    public function issueTakeHome(Request $request)
    {
        $this->guard();

        $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'title_id' => ['required', 'integer'],
            'campus' => ['nullable', 'string', 'max:191'],
            'location' => ['nullable', 'string', 'max:191'],
            'staff_note' => ['nullable', 'string', 'max:255'],
        ]);

        $student = Student::findOrFail($request->student_id);
        $rules = new LibraryRules();
        $name = trim($student->first_name.' '.$student->last_name);

        /* The panel greys these students out, but the button is a courtesy and
           this is the control: a stale page or a crafted post lands here. */
        if ($block = $this->takeHomeBlock($student->id, $rules)):
            return back()->with('library_error', $name.': '.$block);
        endif;

        $already = LibraryBookIssue::where('student_id', $student->id)
            ->where('ops_title_id', $request->title_id)
            ->open()
            ->exists();

        if ($already):
            return back()->with('library_error', $name.' already has this book out or reserved.');
        endif;

        $hold = $this->catalogue->holdCopy($request->title_id, [
            'student_id' => (string) $student->id,
            'registration_no' => (string) $student->registration_no,
            'student_name' => $name,
            'campus' => $request->campus,
            'location' => $request->location,
        ]);

        if (!$hold || empty($hold['copy'])):
            return back()->with('library_error', 'No copy of that title is free at that location. It may have just been taken.');
        endif;

        $copy = $hold['copy'];
        $book = $hold['title'] ?? [];

        /* The copy is already off the shelf in Operations: a failed write must
           give it back rather than strand it against a loan nobody can see. */
        try {
            $issue = LibraryBookIssue::create([
                'reference' => LibraryBookIssue::nextReference(),
                'student_id' => $student->id,
                'loan_type' => LibraryBookIssue::TYPE_TAKE_HOME,
                'ops_title_id' => (int) $request->title_id,
                'ops_copy_id' => $copy['id'] ?? null,
                'barcode' => $copy['barcode'] ?? null,
                'title' => $book['title'] ?? 'Library book',
                'author' => $book['author'] ?? null,
                'isbn13' => $book['isbn13'] ?? null,
                'cover_url' => $book['image_url'] ?? null,
                /* The copy actually held, which Operations may have taken from
                   a different shelf than the one picked. */
                'campus' => $copy['campus'] ?? null,
                'location' => $copy['location'] ?? null,
                'book_price' => $book['price'] ?? null,
                'status' => LibraryBookIssue::STATUS_ISSUED,
                'requested_at' => Carbon::now(),
                'issued_at' => Carbon::now(),
                'issued_by' => auth()->id(),
                'due_at' => $rules->dueDateFrom(),
                'staff_note' => $request->input('staff_note'),
                'created_by' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            $this->catalogue->releaseCopy($copy['id'] ?? null, ['reason' => 'issue_failed']);

            Log::error('[Library] Direct issue failed after the copy was held; copy released.', [
                'copy' => $copy['id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()->with('library_error', 'That could not be issued. Nothing has been held — please try again.');
        }

        $issue->log('issued', null, 'Issued directly at the desk');

        /* Straight to on_loan: it never sat on the hold shelf. */
        if (!$this->catalogue->issueCopy($issue->ops_copy_id)):
            Log::warning('[Library] Directly issued copy could not be updated in Operations.', [
                'reference' => $issue->reference,
                'copy' => $issue->ops_copy_id,
            ]);
        endif;

        return back()->with(
            'library_success',
            $issue->reference.' issued to '.$name.' — due back '.$issue->due_at->format('j M Y').'.'
        );
    }

    /**
     * Hand a reserved book to the student.
     *
     * This is where the loan clock starts: the due date is set now, not when
     * the student booked, so time spent on the hold shelf is not charged to
     * them.
     */
    public function issue(Request $request, $id)
    {
        $this->guard();

        $issue = LibraryBookIssue::findOrFail($id);
        $rules = new LibraryRules();

        if (!$issue->isAwaitingCollection()):
            return back()->with('library_error', $issue->reference.' is not waiting for collection.');
        endif;

        $issue->update([
            'status' => LibraryBookIssue::STATUS_ISSUED,
            'issued_at' => Carbon::now(),
            'issued_by' => auth()->id(),
            'due_at' => $rules->dueDateFrom(),
            'expires_at' => null,
            'staff_note' => $request->input('staff_note') ?: $issue->staff_note,
            'updated_by' => auth()->id(),
        ]);

        $issue->log('issued', LibraryBookIssue::STATUS_REQUESTED, $request->input('staff_note'));

        /* Operations moves the copy from the hold shelf to on loan. Best effort:
           the desk has physically handed the book over either way, so a failed
           call must not leave the local record saying otherwise. */
        if (!$this->catalogue->issueCopy($issue->ops_copy_id)):
            Log::warning('[Library] Issued copy could not be updated in Operations.', [
                'reference' => $issue->reference,
                'copy' => $issue->ops_copy_id,
            ]);
        endif;

        return back()->with('library_success', $issue->reference.' issued — due '.$issue->due_at->format('j M Y').'.');
    }

    /**
     * Take a book back.
     *
     * On time, that is the whole story: the loan closes and the copy goes back
     * on the shelf. Late, it is only half of one — the return is held until
     * the charge is paid, because a loan closed with the money still owed is a
     * debt on a finished row, and nobody chases those.
     */
    public function returnBook(Request $request, $id)
    {
        $this->guard();

        $request->validate(['staff_note' => ['nullable', 'string', 'max:255']]);

        $issue = LibraryBookIssue::with('student')->findOrFail($id);
        $rules = new LibraryRules();

        if ($issue->status !== LibraryBookIssue::STATUS_ISSUED):
            return back()->with('library_error', $issue->reference.' is not out on loan.');
        endif;

        /* The fine is frozen at what it was on the day it came back. Leaving it
           to be recalculated later would let a change to the daily rate rewrite
           a settled charge. */
        $fine = $rules->fineFor($issue);
        $note = $request->input('staff_note');

        if ($fine > 0):
            return $this->holdReturn($issue, $fine, $note);
        endif;

        $issue->update([
            'status' => LibraryBookIssue::STATUS_RETURNED,
            'returned_at' => Carbon::now(),
            'returned_by' => auth()->id(),
            'fine_amount' => 0,
            'staff_note' => $note ?: $issue->staff_note,
            'updated_by' => auth()->id(),
        ]);

        $issue->log('returned', LibraryBookIssue::STATUS_ISSUED, $note);

        $this->catalogue->releaseCopy($issue->ops_copy_id, ['reason' => 'returned']);

        return back()->with('library_success', $issue->reference.' returned.');
    }

    /**
     * Hold a late return open until the charge is paid.
     *
     * Nothing about the loan changes: it stays out, it stays overdue, and it
     * keeps accruing. What is saved is the desk's submission, which the
     * payment turns into a return — see `LibraryFinePayment`.
     *
     * The flash carries the link because the desk is standing in front of the
     * student: they pick Email or SMS in the dialog that opens on it, and the
     * QR beside it means the student can pay before they walk away.
     */
    private function holdReturn(LibraryBookIssue $issue, float $fine, ?string $note)
    {
        if ($issue->fine_paid_at):
            return back()->with('library_error', $issue->reference.' already has a settled charge — please check the ledger.');
        endif;

        if (!$this->fines->isAvailable()):
            Log::error('[Library] PayPal is not configured; a late return cannot be held.', [
                'reference' => $issue->reference,
            ]);

            return back()->with(
                'library_error',
                'Card payments are not available, so '.$issue->reference.' cannot be returned against a charge. '
                .'Take the £'.number_format($fine, 2).' at the desk from the ledger instead.'
            );
        endif;

        $deposit = $this->fines->submitReturn($issue, $fine, $note);

        return back()->with('library_returned', $this->linkPayload($issue, $deposit));
    }

    /** Everything the payment dialog renders, for both the routes that open it. */
    private function linkPayload(LibraryBookIssue $issue, LibraryDeposit $deposit): array
    {
        $student = $issue->student;

        return [
            'deposit_id' => $deposit->id,
            'reference' => $issue->reference,
            'title' => $issue->title,
            'student' => trim(optional($student)->first_name.' '.optional($student)->last_name),
            'amount' => number_format($deposit->amount, 2),
            'link' => $this->fines->link($deposit),
            'deadline' => optional($deposit->expires_at)->format('g:ia'),
            'sent_via' => $deposit->sent_via,
            'send_url' => route('library.management.charge.send', ['deposit' => $deposit->id]),
            'reachable' => $this->fines->reachable($student),
        ];
    }

    /**
     * Reopen the payment dialog for a return already waiting.
     *
     * The dialog is shown once, when the return is submitted, and closing it
     * would otherwise lose the link until midnight — with the student gone and
     * the desk unable to submit the return again. This rebuilds the same flash
     * the submission left behind, so the link, the QR and the send options all
     * come back exactly as they were.
     */
    public function chargeLink(Request $request, $id)
    {
        $this->guard();

        $issue = LibraryBookIssue::with('student')->findOrFail($id);

        if (!$issue->hasPendingReturn()):
            return back()->with('library_error', 'There is no return waiting on payment for '.$issue->reference.'.');
        endif;

        $deposit = LibraryDeposit::fines()
            ->where('library_book_issue_id', $issue->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if (!$deposit):
            return back()->with('library_error', 'That payment link could not be found. Please contact support.');
        endif;

        return back()->with('library_returned', $this->linkPayload($issue, $deposit));
    }

    /**
     * Send a payment link on, by the channels the desk picked.
     *
     * At least one is required by the dialog and again here — a link generated
     * and never sent is a return that silently expires at midnight.
     */
    public function sendChargeLink(Request $request, $deposit)
    {
        $this->guard();

        $request->validate([
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['required', 'string', 'in:email,sms'],
        ], [
            'channels.required' => 'Choose Email, SMS, or both.',
        ]);

        $deposit = LibraryDeposit::fines()->with('student', 'issue')->findOrFail($deposit);

        if ($deposit->status !== 'pending'):
            return back()->with('library_error', 'That charge is no longer waiting for payment.');
        endif;

        if ($deposit->hasExpired()):
            return back()->with('library_error', 'That payment link has expired. Submit the return again to make a new one.');
        endif;

        $result = $this->fines->sendLink($deposit, $request->input('channels', []));

        $names = ['email' => 'email', 'sms' => 'SMS'];
        $sent = array_map(fn ($c) => $names[$c], $result['sent']);
        $failed = array_map(fn ($c) => $names[$c], $result['failed']);

        if (!$sent):
            return back()->with(
                'library_error',
                'The link could not be sent by '.implode(' or ', $failed).' — there is no '
                .(in_array('email', $result['failed'], true) ? 'address' : 'mobile number')
                .' on file. Show the QR code at the desk instead.'
            );
        endif;

        return back()->with(
            'library_success',
            'Payment link sent by '.implode(' and ', $sent).' for '.$deposit->issue->reference.'.'
            .($failed ? ' Nothing on file to send by '.implode(' or ', $failed).'.' : '')
        );
    }

    /** Drop a reservation the student did not collect, or staff are withdrawing. */
    public function cancel(Request $request, $id)
    {
        $this->guard();

        /* Required, not optional: the student is shown this in their portal, and
           "Cancelled at the desk" answers nothing when they come back asking. */
        $request->validate(
            ['cancel_reason' => ['required', 'string', 'max:191']],
            ['cancel_reason.required' => 'Please give a reason so the student knows why.']
        );

        $issue = LibraryBookIssue::findOrFail($id);

        if (!$issue->isAwaitingCollection()):
            return back()->with('library_error', 'Only an uncollected reservation can be cancelled.');
        endif;

        $issue->update([
            'status' => LibraryBookIssue::STATUS_CANCELLED,
            'cancelled_by' => auth()->id(),
            'cancel_reason' => $request->input('cancel_reason'),
            'returned_at' => Carbon::now(),
            'updated_by' => auth()->id(),
        ]);

        $issue->log('cancelled', LibraryBookIssue::STATUS_REQUESTED, $issue->cancel_reason);

        $this->catalogue->releaseCopy($issue->ops_copy_id, ['reason' => 'cancelled']);

        return back()->with('library_success', $issue->reference.' cancelled and the copy is back on the shelf.');
    }
}
