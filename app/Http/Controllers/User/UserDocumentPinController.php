<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Middleware\PromptPinSetup;
use App\Models\Employee;
use App\Models\HrVacancy;
use App\Models\User;
use App\Services\StaffDocumentVaultService;
use Illuminate\Http\Request;

/**
 * A member of staff's own document PIN: the number that opens encrypted
 * documents, staff and student alike.
 *
 * They choose it themselves (6 to 8 digits). The page has two states: before
 * a PIN exists it asks for one, twice; once one exists it can only be changed,
 * and changing it needs the current PIN. A PIN is never shown — not here, not
 * anywhere — because only its hash is kept.
 *
 * A forgotten PIN is reset by its owner, not by HR: a code is emailed to the
 * address they sign in with, and the code plus a new PIN replaces the old one.
 *
 * Nothing here works from an impersonated session. The PIN is the one thing
 * "Login As User" must not be able to create or change — otherwise it would
 * protect nothing.
 */
class UserDocumentPinController extends Controller
{
    public function __construct(protected StaffDocumentVaultService $vault)
    {
    }

    public function index(){
        abort_unless(StaffDocumentVaultService::canUse(), 403, 'You are not permitted to access this page.');

        return view('pages.users.my-account.document-pin', [
            'title' => 'PIN - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'My HR', 'href' => route('user.account')],
                ['label' => 'PIN', 'href' => 'javascript:void(0);'],
            ],
            'user' => User::find(auth()->user()->id),
            'employee' => $this->currentEmployee(),
            'vacanties' => HrVacancy::where('active', 1)->get()->count(),
            'hasPin' => $this->vault->hasPin(auth()->user()),
            'setAt' => $this->setAt(),
            // True when they were brought here at sign-in rather than choosing to come.
            'prompted' => (bool) session(PromptPinSetup::PROMPT_FLASH, false),
            'impersonating' => StaffDocumentVaultService::isImpersonating(),
            'minLength' => StaffDocumentVaultService::PIN_MIN_LENGTH,
            'maxLength' => StaffDocumentVaultService::PIN_MAX_LENGTH,
            'maxAttempts' => StaffDocumentVaultService::MAX_ATTEMPTS,
            'lockMinutes' => StaffDocumentVaultService::LOCK_MINUTES,
            // A forgotten PIN is reset with a code emailed here.
            'resetEmail' => (string) auth()->user()->email,
            'codeLength' => StaffDocumentVaultService::RESET_CODE_LENGTH,
            'codeMinutes' => StaffDocumentVaultService::RESET_CODE_MINUTES,
        ]);
    }

    /**
     * The first PIN. Once one exists this refuses: replacing a PIN goes
     * through change(), which asks for the current one.
     */
    public function setup(Request $request){
        $refusal = $this->refuseUnlessOwner();
        if($refusal):
            return $refusal;
        endif;

        if($this->vault->hasPin(auth()->user())):
            return response()->json(['message' => 'You already have a PIN. To replace it, enter your current PIN and the new one.'], 409);
        endif;

        $problem = $this->vault->pinProblem($request->pin, $request->pin_confirmation);
        if($problem):
            return response()->json(['message' => $problem], 422);
        endif;

        $this->vault->setPin(auth()->user(), (string) $request->pin);
        $this->vault->log(StaffDocumentVaultService::EVENT_PIN_SET);

        return response()->json([
            'suc' => 1,
            'set_at' => $this->setAt(),
            // The page they were on their way to when sign-in sent them here.
            'continue_url' => session()->pull(PromptPinSetup::RETURN_KEY),
        ], 200);
    }

    /**
     * Replace the PIN. The current one proves it is its owner asking, and it
     * stops working the moment the new one is saved.
     */
    public function change(Request $request){
        $challenge = $this->vault->challengeOwner(auth()->user(), $request->current_pin);
        if(!$challenge['ok']):
            return response()->json(['message' => $challenge['message'], 'field' => 'current_pin'], $challenge['status']);
        endif;

        $problem = $this->vault->pinProblem($request->pin, $request->pin_confirmation);
        if(!$problem && $this->vault->isCurrentPin(auth()->user(), $request->pin)):
            $problem = 'That is your current PIN. Choose a different one.';
        endif;
        if($problem):
            return response()->json(['message' => $problem, 'field' => 'pin'], 422);
        endif;

        $this->vault->setPin(auth()->user(), (string) $request->pin);
        $this->vault->log(StaffDocumentVaultService::EVENT_PIN_CHANGED);

        return response()->json(['suc' => 1, 'set_at' => $this->setAt()], 200);
    }

    /**
     * Forgotten PIN, step one: email a code to the address they sign in with.
     */
    public function sendResetCode(){
        $refusal = $this->refuseUnlessOwner();
        if($refusal):
            return $refusal;
        endif;

        if(!$this->vault->hasPin(auth()->user())):
            return response()->json(['message' => 'You have no PIN to reset. Set one up instead.'], 409);
        endif;

        $sent = $this->vault->sendResetCode(auth()->user());
        if(!$sent['ok']):
            return response()->json(['message' => $sent['message']], $sent['status']);
        endif;

        return response()->json(['suc' => 1, 'email' => $sent['email']], 200);
    }

    /**
     * Forgotten PIN, step two: the emailed code stands in for the current
     * PIN, and they choose a new one. A locked PIN is unlocked by this too —
     * the mailbox has just proved who is asking.
     */
    public function resetWithCode(Request $request){
        $refusal = $this->refuseUnlessOwner();
        if($refusal):
            return $refusal;
        endif;

        $check = $this->vault->checkResetCode(auth()->user(), $request->code);
        if(!$check['ok']):
            return response()->json(['message' => $check['message'], 'field' => 'code'], $check['status']);
        endif;

        $problem = $this->vault->pinProblem($request->pin, $request->pin_confirmation);
        if($problem):
            return response()->json(['message' => $problem, 'field' => 'pin'], 422);
        endif;

        $this->vault->setPin(auth()->user(), (string) $request->pin);
        $this->vault->clearResetCode();
        $this->vault->log(StaffDocumentVaultService::EVENT_PIN_RESET_EMAIL);

        return response()->json(['suc' => 1, 'set_at' => $this->setAt()], 200);
    }

    /**
     * These take no current PIN, so they cannot lean on challengeOwner() for
     * the two refusals.
     */
    protected function refuseUnlessOwner(){
        if(StaffDocumentVaultService::isImpersonating()):
            $this->vault->log(StaffDocumentVaultService::EVENT_DENIED_IMPERSONATION);
            return response()->json(['message' => 'You are signed in as another user. A document PIN cannot be set or changed while impersonating.'], 403);
        endif;

        if(!StaffDocumentVaultService::canUse()):
            return response()->json(['message' => 'You do not have permission to use a document PIN.'], 403);
        endif;

        return null;
    }

    protected function setAt(){
        $pin = $this->vault->pinFor(auth()->user());

        return ($pin && $pin->generated_at ? $pin->generated_at->format('jS F, Y \a\t h:i A') : '');
    }

    protected function currentEmployee(){
        return Employee::where('user_id', auth()->user()->id)->get()->first();
    }
}
