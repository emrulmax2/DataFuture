<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\LibraryBookRequested;
use App\Models\LibraryDeposit;
use App\Models\LibraryBookIssue;
use App\Models\Student;
use App\Services\LibraryRules;
use App\Services\OperationsLibraryClient;
use App\Services\LibraryDepositReconciler;
use App\Services\PayPalClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The student's library: search the Operations catalogue, leave a refundable
 * deposit through PayPal, book a copy, and track what is on loan.
 *
 * This app owns the deposit and the loan. Operations owns the catalogue and the
 * physical copies, so every question about "is a copy free" and every change to
 * copy status goes over the API — never guessed locally.
 */
class LibraryController extends Controller
{
    public function __construct(
        private OperationsLibraryClient $catalogue,
        private PayPalClient $paypal,
        private LibraryDepositReconciler $reconciler,
    ) {
    }

    /**
     * The portal renders for whichever student the session has selected, the
     * same rule the rest of the student dashboard follows.
     */
    private function student(): Student
    {
        $selectedStudentId = session('selected_student_id');

        if ($selectedStudentId):
            return Student::findOrFail($selectedStudentId);
        endif;

        return Student::where('student_user_id', auth('student')->id())
            ->orderBy('id', 'DESC')
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $student = $this->student();
        $rules = new LibraryRules();

        /* A student who paid but never made it back from PayPal has an unsettled
           row and no access. Catch that here rather than leaving them to pay
           twice — it only calls out when something is actually pending. */
        $this->reconciler->reconcileStudent($student->id);

        $deposit = LibraryDeposit::heldFor($student->id);

        $loans = LibraryBookIssue::where('student_id', $student->id)
            ->orderByRaw("FIELD(status,'issued','requested','returned','cancelled','not_collected')")
            ->orderByDesc('id')
            ->get();

        return view('pages.students.frontend.library.index', [
            'title' => 'Library - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Library', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'rules' => $rules,
            'deposit' => $deposit,
            'blockedReason' => $rules->blockedReason($student->id, (bool) $deposit),
            'filterOptions' => $this->catalogue->filters(),
            /* Titles this student already holds. borrow() rejects a duplicate
               anyway, but the card should say so before they open the dialog
               and confirm — a guard the user only meets after committing reads
               as the app losing their request. */
            'heldTitles' => LibraryBookIssue::where('student_id', $student->id)
                ->open()
                ->pluck('status', 'ops_title_id')
                ->toArray(),
            'openLoans' => $loans->filter->isOpen(),
            'pastLoans' => $loans->reject->isOpen(),
        ]);
    }

    /** Catalogue search, called by the page as the student types. */
    public function search(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:191'],
            /* A venue arrives as an id from the dropdown but a name is also
               accepted, so this cannot be pinned to `string` or `integer`. */
            'venue' => ['nullable', 'max:191'],
            'course' => ['nullable', 'integer'],
            'module' => ['nullable', 'integer'],
            'availability' => ['nullable', 'in:available,out'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $results = $this->catalogue->search(
            $request->only(['q', 'venue', 'course', 'module', 'availability', 'page'])
        );

        if ($results === null):
            return response()->json([
                'ok' => false,
                'message' => 'The catalogue is unavailable right now. Please try again shortly.',
            ], 503);
        endif;

        return response()->json(['ok' => true] + $results);
    }

    public function title(Request $request, $titleId)
    {
        $title = $this->catalogue->title($titleId);

        if ($title === null):
            return response()->json(['ok' => false, 'message' => 'That book could not be loaded.'], 503);
        endif;

        return response()->json(['ok' => true, 'data' => $title]);
    }

    /* ------------------------------------------------------------------ */
    /* Deposit                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Send the student to PayPal to approve the deposit.
     *
     * The order is recorded before the redirect so a payment that completes but
     * never returns (closed tab, dead phone) still has a row for the webhook to
     * settle against.
     */
    public function depositCheckout(Request $request)
    {
        $student = $this->student();
        $rules = new LibraryRules();

        if (LibraryDeposit::heldFor($student->id)):
            return redirect()->route('students.library.index')
                ->with('library_error', 'Your deposit is already held.');
        endif;

        $amount = $rules->depositAmount();

        if ($amount <= 0):
            return redirect()->route('students.library.index')
                ->with('library_error', 'No deposit amount has been set. Please contact the library.');
        endif;

        if (!$this->paypal->isConfigured()):
            Log::error('[Library] PayPal is not configured; deposit cannot be taken.');

            return redirect()->route('students.library.index')
                ->with('library_error', 'Card payments are not available right now. Please contact the library.');
        endif;

        /* The row is written before the redirect, so every press of Pay leaves
           one behind whether or not the student goes through with it. Reuse
           the last abandoned attempt for the same student and amount instead
           of starting another: two rows for one intention read on the desk's
           ledger as a student who paid twice, and they accumulate — nothing
           ages a pending row out, because a student is allowed to come back
           and finish one.

           A fresh PayPal order is still created below; the abandoned one is
           left to expire on PayPal's side. */
        $deposit = LibraryDeposit::deposits()
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->where('amount', $amount)
            ->latest('id')
            ->first();

        if ($deposit):
            $deposit->update(['provider_order_id' => null, 'failure_reason' => null]);
        else:
            $deposit = LibraryDeposit::create([
                'student_id' => $student->id,
                'type' => LibraryDeposit::TYPE_DEPOSIT,
                'amount' => $amount,
                'currency' => 'GBP',
                'provider' => 'paypal',
                'status' => 'pending',
            ]);
        endif;

        $order = $this->paypal->createOrder([
            'amount' => $amount,
            'currency' => 'GBP',
            'reference' => 'LIBDEP-'.$deposit->id,
            'item_name' => 'Library deposit (refundable)',
            'description' => 'Refundable library deposit for '.$student->registration_no,
            'return_url' => route('students.library.deposit.complete'),
            'cancel_url' => route('students.library.deposit.cancel'),
        ]);

        if (!$order):
            $deposit->update(['status' => 'failed', 'failure_reason' => 'PayPal order could not be created.']);

            return redirect()->route('students.library.index')
                ->with('library_error', 'We could not start the payment. Please try again.');
        endif;

        $deposit->update(['provider_order_id' => $order['id']]);

        return redirect()->away($order['approve_url']);
    }

    /**
     * PayPal returns the student here with ?token=<order id>. The order is
     * captured server-side — the query string only says which order to look at,
     * it is never evidence that anything was paid.
     */
    public function depositComplete(Request $request)
    {
        $orderId = (string) $request->query('token');
        $deposit = LibraryDeposit::where('provider_order_id', $orderId)->first();

        if ($orderId === '' || !$deposit):
            return redirect()->route('students.library.index')
                ->with('library_error', 'That payment could not be matched. Please contact the library.');
        endif;

        /* Guard against the student refreshing the return URL: the money is
           already taken and recorded, so just say so. */
        if ($deposit->status === 'paid'):
            return redirect()->route('students.library.index')
                ->with('library_success', 'Your deposit is held. You can borrow now.');
        endif;

        $capture = $this->paypal->captureOrder($orderId);

        if (!$capture || $capture['status'] !== 'COMPLETED'):
            return redirect()->route('students.library.index')
                ->with('library_error', 'The payment was not completed. Nothing has been charged.');
        endif;

        $this->settle($deposit, $capture);

        return redirect()->route('students.library.index')
            ->with('library_success', 'Deposit received. You can borrow now.');
    }

    public function depositCancel(Request $request)
    {
        $deposit = LibraryDeposit::where('provider_order_id', (string) $request->query('token'))->first();

        if ($deposit && $deposit->status === 'pending'):
            $deposit->update(['status' => 'failed', 'failure_reason' => 'Cancelled at PayPal.']);
        endif;

        return redirect()->route('students.library.index')
            ->with('library_error', 'Payment cancelled. Nothing has been charged.');
    }

    /**
     * Ask for the deposit back.
     *
     * Only once every book is back — the bond exists to cover what is out on
     * loan. Outstanding charges are billed separately and do not hold it up.
     */
    public function depositRefund(Request $request)
    {
        $student = $this->student();
        $deposit = LibraryDeposit::heldFor($student->id);

        if (!$deposit):
            return redirect()->route('students.library.index')
                ->with('library_error', 'There is no deposit to refund.');
        endif;

        $stillOut = LibraryBookIssue::where('student_id', $student->id)->open()->count();

        if ($stillOut > 0):
            return redirect()->route('students.library.index')
                ->with('library_error', 'Return your '.$stillOut.' book(s) before requesting a refund.');
        endif;

        if (!$deposit->provider_capture_id):
            Log::error('[Library] Deposit marked paid with no capture id.', ['deposit' => $deposit->id]);

            return redirect()->route('students.library.index')
                ->with('library_error', 'We could not refund this automatically. Please contact the library.');
        endif;

        $refund = $this->paypal->refundCapture(
            $deposit->provider_capture_id,
            $deposit->amount,
            $deposit->currency,
            'London Churchill College library deposit refund'
        );

        if (!$refund):
            return redirect()->route('students.library.index')
                ->with('library_error', 'The refund could not be processed. Please contact the library.');
        endif;

        $deposit->update([
            'status' => 'refunded',
            'provider_refund_id' => $refund['refund_id'],
            'refunded_at' => Carbon::now(),
        ]);

        return redirect()->route('students.library.index')
            ->with('library_success', 'Refund of £'.number_format($deposit->amount, 2).' sent to your PayPal account.');
    }

    /**
     * Write a completed capture onto the deposit.
     *
     * Shared by the return URL and the webhook, and guarded by a conditional
     * update so whichever arrives second cannot double-settle the row.
     */
    private function settle(LibraryDeposit $deposit, array $capture): void
    {
        LibraryDeposit::where('id', $deposit->id)
            ->where('status', '!=', 'paid')
            ->update([
                'status' => 'paid',
                'provider_capture_id' => $capture['capture_id'],
                'payer_email' => $capture['payer_email'] ?? null,
                'paid_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
    }

    /* ------------------------------------------------------------------ */
    /* Borrowing                                                           */
    /* ------------------------------------------------------------------ */

    public function borrow(Request $request)
    {
        $request->validate([
            'title_id' => ['required', 'integer'],
            'campus' => ['nullable', 'string', 'max:191'],
            /* The shelf the student picked in the dialog. A preference, not an
               instruction: Operations still decides which copy is free, and the
               loan row below is written from the copy it actually held. */
            'location' => ['nullable', 'string', 'max:191'],
        ]);

        $student = $this->student();
        $rules = new LibraryRules();
        $deposit = LibraryDeposit::heldFor($student->id);

        /* The same guard the page renders, re-checked here: the button being
           hidden is a courtesy, not a control. */
        if ($reason = $rules->blockedReason($student->id, (bool) $deposit)):
            return response()->json(['ok' => false, 'message' => $reason], 422);
        endif;

        $titleId = (int) $request->title_id;

        $already = LibraryBookIssue::where('student_id', $student->id)
            ->where('ops_title_id', $titleId)
            ->open()
            ->exists();

        if ($already):
            return response()->json(['ok' => false, 'message' => 'You already have this book.'], 422);
        endif;

        $expiresAtPreview = Carbon::now()->addDays($rules->holdDays())->endOfDay();

        /* Operations picks and marks the copy — this app must not choose one,
           or two students booking at the same moment both get told it is
           theirs. */
        $hold = $this->catalogue->holdCopy($titleId, [
            'student_id' => (string) $student->id,
            'registration_no' => (string) $student->registration_no,
            'student_name' => trim($student->first_name.' '.$student->last_name),
            'campus' => $request->campus,
            'location' => $request->location,
            'hold_until' => $expiresAtPreview->toDateString(),
        ]);

        if (!$hold || empty($hold['copy'])):
            return response()->json([
                'ok' => false,
                'message' => 'No copy could be held. It may have just been taken — try again.',
            ], 409);
        endif;

        $copy = $hold['copy'];
        $book = $hold['title'] ?? [];

        $expiresAt = Carbon::now()->addDays($rules->holdDays())->endOfDay();

        /* The copy is already off the shelf in Operations. If writing the local
           record fails now, that copy is stranded — held for a booking nobody
           can see, cancel or expire. Give it back before surfacing the error. */
        try {
            $loan = LibraryBookIssue::create([
                'reference' => LibraryBookIssue::nextReference(),
                'student_id' => $student->id,
                'ops_title_id' => $titleId,
                'ops_copy_id' => $copy['id'] ?? null,
                'barcode' => $copy['barcode'] ?? null,
                'title' => $book['title'] ?? 'Library book',
                'author' => $book['author'] ?? null,
                'isbn13' => $book['isbn13'] ?? null,
                'cover_url' => $book['image_url'] ?? null,
                'campus' => $copy['campus'] ?? null,
                'location' => $copy['location'] ?? null,
                'book_price' => $book['price'] ?? null,
                'status' => LibraryBookIssue::STATUS_REQUESTED,
                'requested_at' => Carbon::now(),
                /* No due date yet — the loan clock starts when staff hand the book
                   over. What is set now is how long we hold it for collection. */
                'expires_at' => $expiresAt,
                'created_by' => auth('student')->id(),
            ]);
        } catch (\Throwable $e) {
            $this->catalogue->releaseCopy($copy['id'] ?? null, ['reason' => 'booking_failed']);

            Log::error('[Library] Booking failed after the copy was held; copy released.', [
                'title' => $titleId,
                'copy' => $copy['id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'That could not be reserved. Nothing has been held — please try again.',
            ], 500);
        }

        $loan->log('requested', null, 'Reserved from the student portal', 'student');

        $this->notifyLibraryManager($loan, $student);

        return response()->json([
            'ok' => true,
            'message' => 'Reserved as '.$loan->reference.'. Collect it from the '
                .($loan->campus ?: 'library').' desk by '.$expiresAt->format('j M Y').'.',
            'loan_id' => $loan->id,
        ]);
    }

    /**
     * Tell the desk a copy has been reserved.
     *
     * The copy is already off the shelf in Operations by the time this runs,
     * so somebody has to be told to put it aside. Failure is swallowed on
     * purpose: the reservation is real whether or not the mail server is
     * reachable, and turning a working booking into an error because SMTP was
     * down would strand a held copy for no reason. The log is where a missed
     * mail is answered.
     */
    private function notifyLibraryManager(LibraryBookIssue $loan, $student): void
    {
        $to = config('services.library.manager_email');

        if (empty($to)):
            Log::warning('[Library] No manager email configured; reservation not announced.', [
                'reference' => $loan->reference,
            ]);

            return;
        endif;

        try {
            Mail::to($to)->send(new LibraryBookRequested(
                $loan,
                trim($student->first_name.' '.$student->last_name),
                $student->registration_no
            ));
        } catch (\Throwable $e) {
            Log::error('[Library] Could not email the reservation to the desk.', [
                'reference' => $loan->reference,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function renew(Request $request, $loanId)
    {
        $student = $this->student();
        $rules = new LibraryRules();

        $loan = LibraryBookIssue::where('student_id', $student->id)->findOrFail($loanId);

        if ($loan->status !== LibraryBookIssue::STATUS_ISSUED):
            return response()->json([
                'ok' => false,
                'message' => 'Only a book you have collected can be renewed.',
            ], 422);
        endif;

        if ($loan->renewals >= $rules->maxRenewals()):
            return response()->json([
                'ok' => false,
                'message' => 'You have already renewed this book '.$rules->maxRenewals().' times.',
            ], 422);
        endif;

        if ($rules->fineFor($loan) > 0):
            return response()->json([
                'ok' => false,
                'message' => 'This book is overdue and cannot be renewed. Please return it.',
            ], 422);
        endif;

        $loan->update([
            'due_at' => $rules->dueDateFrom($loan->due_at),
            'renewals' => $loan->renewals + 1,
            'updated_by' => auth('student')->id(),
        ]);

        $loan->log('renewed', LibraryBookIssue::STATUS_ISSUED, 'Renewal '.$loan->renewals.' — due '.$loan->due_at->format('j M Y'), 'student');

        return response()->json([
            'ok' => true,
            'message' => 'Renewed. Now due '.$loan->due_at->format('j M Y').'.',
        ]);
    }

    /**
     * Cancel a booking the student has not collected. A book already handed
     * over has to come back to the desk, so only `requested` can be dropped.
     */
    public function cancel(Request $request, $loanId)
    {
        $student = $this->student();
        $loan = LibraryBookIssue::where('student_id', $student->id)->findOrFail($loanId);

        if (!$loan->isAwaitingCollection()):
            return response()->json([
                'ok' => false,
                'message' => 'Only an uncollected booking can be cancelled. Please return the book to the desk.',
            ], 422);
        endif;

        DB::transaction(function () use ($loan) {
            $loan->update([
                'status' => LibraryBookIssue::STATUS_CANCELLED,
                'cancelled_by' => auth('student')->id(),
                'cancel_reason' => 'Cancelled by the student',
                'returned_at' => Carbon::now(),
                'updated_by' => auth('student')->id(),
            ]);
        });

        /* Best effort — the copy going back on the shelf must not fail the
           cancellation the student already saw succeed. */
        $loan->log('cancelled', LibraryBookIssue::STATUS_REQUESTED, 'Cancelled by the student', 'student');

        $this->catalogue->releaseCopy($loan->ops_copy_id, ['reason' => 'cancelled']);

        return response()->json(['ok' => true, 'message' => 'Booking cancelled.']);
    }
}
