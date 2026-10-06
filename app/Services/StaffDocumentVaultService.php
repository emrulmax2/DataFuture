<?php

namespace App\Services;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\Employee;
use App\Models\EmployeeDocumentAccessLog;
use App\Models\EmployeeDocuments;
use App\Models\User;
use App\Models\UserDocumentPin;
use App\Models\UserPrivilege;
use App\Support\LegacyPrivilegeMap;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Lab404\Impersonate\Services\ImpersonateManager;

/**
 * Encrypted staff documents and the PIN that opens them.
 *
 * A document uploaded as "encrypted" is stored encrypted, so the stored file
 * is unreadable on its own and no storage link to it is ever handed out. It
 * is only decrypted for someone who
 *   - holds the "PIN Enabled" privilege, and
 *   - enters their own document PIN.
 *
 * "Someone" is always the person at the keyboard. The PIN is what "Login As
 * User" cannot borrow: impersonating an account gives its privileges but not
 * what its owner knows. So in an impersonated session it is the impersonator
 * who has to hold the privilege and enter a PIN — their own — and the open is
 * recorded as theirs, through impersonation. The impersonated account's PIN
 * opens nothing for them, and cannot be set or changed from the impersonated
 * session.
 *
 * Each person chooses their own PIN (6 to 8 digits). Only its hash is kept,
 * so it is never shown again. A forgotten PIN is reset by its owner alone:
 * a code is emailed to the address they sign in with, and entering it lets
 * them choose a new PIN. HR plays no part in that.
 *
 * Every open — encrypted or not — is written to employee_document_access_logs
 * together with the impersonator, when there is one.
 */
class StaffDocumentVaultService
{
    /** The priv() key. See LegacyPrivilegeMap: hr_portal.staff_document_pin. */
    const PRIVILEGE = 'staff_document_pin';

    /** A PIN is chosen by its owner: digits only, this many of them. */
    const PIN_MIN_LENGTH = 6;
    const PIN_MAX_LENGTH = 8;

    /** Wrong PINs in a row before the PIN is locked, and for how long. */
    const MAX_ATTEMPTS = 5;
    const LOCK_MINUTES = 15;

    /**
     * Resetting a forgotten PIN: a code emailed to the owner. It lives in
     * their session, so it only works in the browser that asked for it.
     */
    const RESET_CODE_LENGTH = 6;
    const RESET_CODE_MINUTES = 15;
    const RESET_CODE_ATTEMPTS = 5;
    const RESET_RESEND_SECONDS = 60;
    const RESET_CODES_PER_HOUR = 5;
    const RESET_SESSION_KEY = 'pin_reset_code';

    const FROM_EMAIL = 'hr@lcc.ac.uk';
    const FROM_NAME = 'London Churchill College';

    /** Leads every encrypted file, so a plain file is never mistaken for one. */
    const MAGIC = 'DFVAULT1';
    const CIPHER = 'aes-256-gcm';
    const IV_LENGTH = 12;
    const TAG_LENGTH = 16;

    /** Appended to the stored file name of an encrypted document. */
    const EXTENSION = '.enc';

    /** Said when an open is refused because it could not be written to the log. */
    const NOT_RECORDED = 'This document could not be opened because the access could not be recorded. Please contact the administrator.';

    const EVENT_VIEW = 'view';
    const EVENT_DOWNLOAD = 'download';
    const EVENT_PIN_FAILED = 'pin_failed';
    const EVENT_PIN_LOCKED = 'pin_locked';
    const EVENT_DENIED_IMPERSONATION = 'denied_impersonation';
    const EVENT_DENIED_PERMISSION = 'denied_permission';
    const EVENT_PIN_SET = 'pin_set';
    const EVENT_PIN_CHANGED = 'pin_changed';
    const EVENT_PIN_RESET_CODE_SENT = 'pin_reset_code_sent';
    const EVENT_PIN_RESET_EMAIL = 'pin_reset_email';

    /** event => [label, tone]. The tone picks the pill colour in the log. */
    const EVENTS = [
        self::EVENT_VIEW => ['Viewed', 'success'],
        self::EVENT_DOWNLOAD => ['Downloaded', 'success'],
        self::EVENT_PIN_FAILED => ['Wrong PIN', 'danger'],
        self::EVENT_PIN_LOCKED => ['PIN locked', 'danger'],
        self::EVENT_DENIED_IMPERSONATION => ['Blocked - impersonating', 'danger'],
        self::EVENT_DENIED_PERMISSION => ['Blocked - no permission', 'danger'],
        self::EVENT_PIN_SET => ['PIN set up', 'info'],
        self::EVENT_PIN_CHANGED => ['PIN changed', 'info'],
        self::EVENT_PIN_RESET_CODE_SENT => ['PIN reset code emailed', 'info'],
        self::EVENT_PIN_RESET_EMAIL => ['PIN reset by email', 'warning'],
    ];

    /** Groups offered by the log's filter. */
    const EVENT_GROUPS = [
        'opened' => [self::EVENT_VIEW, self::EVENT_DOWNLOAD],
        'blocked' => [self::EVENT_PIN_FAILED, self::EVENT_PIN_LOCKED, self::EVENT_DENIED_IMPERSONATION, self::EVENT_DENIED_PERMISSION],
        'pin' => [self::EVENT_PIN_SET, self::EVENT_PIN_CHANGED, self::EVENT_PIN_RESET_CODE_SENT, self::EVENT_PIN_RESET_EMAIL],
    ];

    /** Types a browser can show safely, and what to serve them as. */
    const VIEWABLE = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'txt' => 'text/plain; charset=UTF-8',
    ];

    /**
     * Does this user hold the "PIN Enabled" privilege?
     */
    public static function canUse($user = null): bool
    {
        $user = $user ?: auth()->user();
        if(!$user):
            return false;
        endif;

        $priv = $user->priv();

        return isset($priv[self::PRIVILEGE]) && $priv[self::PRIVILEGE] == 1;
    }

    /**
     * Has "PIN Enabled" actually been ticked for this user?
     *
     * For nearly everybody that is the same question as canUse(). A super
     * admin is the exception: they pass every privilege check without holding
     * anything, so canUse() says yes whether or not anybody ticked the box.
     * They may use a PIN either way, but they are only made to set one up
     * once it has really been switched on for them.
     */
    public function switchedOnFor($user): bool
    {
        if(!$user->isSuperAdmin()):
            return self::canUse($user);
        endif;

        if(config('privileges.source') === 'new'):
            return $user->hasPerm((string) LegacyPrivilegeMap::resolve('hr_portal', self::PRIVILEGE));
        endif;

        return UserPrivilege::where('user_id', $user->id)->where('name', self::PRIVILEGE)->where('access', 1)->exists();
    }

    /**
     * Should the "PIN" link appear in this session's account menu?
     * Staff only: students, applicants and agents sign in on their own guards.
     */
    public static function showsMenu(): bool
    {
        if(auth('agent')->check() || auth('applicant')->check() || auth('student')->check()):
            return false;
        endif;

        return auth('web')->check() && self::canUse(auth('web')->user());
    }

    public static function isImpersonating(): bool
    {
        return app(ImpersonateManager::class)->isImpersonating();
    }

    /**
     * The user really at the keyboard when the session is impersonating.
     */
    public static function impersonatorId(): ?int
    {
        $id = app(ImpersonateManager::class)->getImpersonatorId();

        return ($id ? (int) $id : null);
    }

    /**
     * Whose permission and PIN decide whether an encrypted document opens:
     * the signed-in user or, in an impersonated session, the member of staff
     * who is really at the keyboard.
     *
     * NULL when the session is impersonated by somebody who is not staff.
     */
    public static function holder(): ?User
    {
        if(!self::isImpersonating()):
            $user = auth()->user();

            return ($user instanceof User ? $user : null);
        endif;

        // Staff sign in on the web guard; anybody else has no PIN to offer.
        if(app(ImpersonateManager::class)->getImpersonatorGuardName() !== 'web'):
            return null;
        endif;

        return User::find(self::impersonatorId());
    }

    /**
     * The user's PIN record, or NULL when they have no PIN they could use.
     *
     * A PIN is only ever kept as a one-way hash. A stored value that is not
     * one cannot be checked against anything, so it counts as no PIN and the
     * user is simply asked to set one up.
     */
    public function pinFor($user): ?UserDocumentPin
    {
        $row = UserDocumentPin::where('user_id', $user->id)->get()->first();

        return ($row && password_get_info((string) $row->pin)['algo'] !== null ? $row : null);
    }

    public function hasPin($user): bool
    {
        return $this->pinFor($user) !== null;
    }

    /**
     * Is this an acceptable PIN to choose? Returns what is wrong with it, or
     * NULL when it is fine.
     *
     * Length and digits are the rule. The two refusals after that are the
     * PINs anyone would try first: with five guesses before a lock, 000000
     * and 123456 would otherwise be worth guessing.
     */
    public function pinProblem($pin, $confirmation): ?string
    {
        $pin = (string) $pin;

        if(!preg_match('/^\d{'.self::PIN_MIN_LENGTH.','.self::PIN_MAX_LENGTH.'}$/', $pin)):
            return 'Your PIN must be '.self::PIN_MIN_LENGTH.' to '.self::PIN_MAX_LENGTH.' digits, numbers only.';
        endif;

        if($pin !== (string) $confirmation):
            return 'The two PINs do not match. Type the same PIN in both boxes.';
        endif;

        if(count(array_unique(str_split($pin))) === 1):
            return 'That PIN is too easy to guess. Do not use one digit repeated.';
        endif;

        $steps = [];
        for($i = 1; $i < strlen($pin); $i++):
            $steps[] = (int) $pin[$i] - (int) $pin[$i - 1];
        endfor;
        if(count(array_unique($steps)) === 1 && abs($steps[0]) === 1):
            return 'That PIN is too easy to guess. Do not use a run such as 123456 or 654321.';
        endif;

        return null;
    }

    /**
     * Save the PIN the user chose, replacing any they had. Check it with
     * pinProblem() first.
     *
     * Only its hash is kept: once set, a PIN cannot be read back by its
     * owner, by HR or by the system — only checked.
     */
    public function setPin($user, string $pin): void
    {
        $data = [
            'pin' => Hash::make($pin),
            'failed_attempts' => 0,
            'locked_until' => null,
            // When the PIN was last set. (The column predates chosen PINs.)
            'generated_at' => now(),
        ];

        $row = UserDocumentPin::where('user_id', $user->id)->get()->first();
        if($row):
            $data['updated_by'] = auth()->id();
            $row->update($data);
        else:
            $data['user_id'] = $user->id;
            $data['created_by'] = auth()->id();
            UserDocumentPin::create($data);
        endif;
    }

    /**
     * Would this PIN be the one the user already has?
     */
    public function isCurrentPin($user, $pin): bool
    {
        $row = $this->pinFor($user);

        return ($row ? Hash::check((string) $pin, $row->pin) : false);
    }

    /**
     * Email the user a code that will let them choose a new PIN.
     *
     * Sent straight away rather than queued: somebody is sitting waiting for
     * it, and a failure has to be something they are told about.
     *
     * @return array ['ok' => bool, 'status' => int, 'message' => string, 'email' => string]
     */
    public function sendResetCode($user): array
    {
        $pending = session(self::RESET_SESSION_KEY);
        if(is_array($pending) && (int) $pending['user_id'] === (int) $user->id && (now()->timestamp - (int) $pending['sent_at']) < self::RESET_RESEND_SECONDS):
            return ['ok' => false, 'status' => 429, 'message' => 'A code was emailed to you a moment ago. Please wait a minute before asking for another.'];
        endif;

        $limiter = 'pin-reset-code:'.$user->id;
        if(RateLimiter::tooManyAttempts($limiter, self::RESET_CODES_PER_HOUR)):
            return ['ok' => false, 'status' => 429, 'message' => 'Too many codes have been requested. Please try again in an hour.'];
        endif;

        if(empty($user->email)):
            return ['ok' => false, 'status' => 422, 'message' => 'Your account has no email address to send a code to.'];
        endif;

        $configuration = $this->mailConfiguration();
        if($configuration === null):
            Log::warning('PIN reset code not sent: no SMTP configuration found.', ['user_id' => $user->id]);
            return ['ok' => false, 'status' => 500, 'message' => 'The email could not be sent. Please try again in a moment.'];
        endif;

        $code = '';
        for($i = 0; $i < self::RESET_CODE_LENGTH; $i++):
            $code .= random_int(0, 9);
        endfor;

        try {
            UserMailerJob::dispatchSync($configuration, [$user->email], new CommunicationSendMail('Your PIN reset code', $this->resetEmailBody($user, $code), []));
        } catch (\Throwable $e) {
            Log::error('PIN reset code could not be emailed to user '.$user->id.': '.$e->getMessage());
            return ['ok' => false, 'status' => 500, 'message' => 'The email could not be sent. Please try again in a moment.'];
        }

        RateLimiter::hit($limiter, 3600);
        session([self::RESET_SESSION_KEY => [
            'user_id' => $user->id,
            'hash' => $this->resetCodeHash($code),
            'expires_at' => now()->addMinutes(self::RESET_CODE_MINUTES)->timestamp,
            'sent_at' => now()->timestamp,
            'attempts' => 0,
        ]]);
        $this->log(self::EVENT_PIN_RESET_CODE_SENT);

        return ['ok' => true, 'status' => 200, 'message' => '', 'email' => $user->email];
    }

    /**
     * Is this the code that was emailed to them? A few wrong tries and the
     * code is thrown away, so it cannot be guessed at.
     *
     * @return array ['ok' => bool, 'status' => int, 'message' => string]
     */
    public function checkResetCode($user, $code): array
    {
        $pending = session(self::RESET_SESSION_KEY);

        if(!is_array($pending) || (int) $pending['user_id'] !== (int) $user->id || (int) $pending['expires_at'] < now()->timestamp):
            $this->clearResetCode();
            return ['ok' => false, 'status' => 422, 'message' => 'That code has expired. Ask for a new one.'];
        endif;

        $code = trim((string) $code);
        if(!preg_match('/^\d{'.self::RESET_CODE_LENGTH.'}$/', $code)):
            return ['ok' => false, 'status' => 422, 'message' => 'Enter the '.self::RESET_CODE_LENGTH.'-digit code from the email.'];
        endif;

        if(hash_equals((string) $pending['hash'], $this->resetCodeHash($code))):
            return ['ok' => true, 'status' => 200, 'message' => ''];
        endif;

        $pending['attempts'] = (int) $pending['attempts'] + 1;
        if($pending['attempts'] >= self::RESET_CODE_ATTEMPTS):
            $this->clearResetCode();
            return ['ok' => false, 'status' => 422, 'message' => 'Too many wrong codes. Ask for a new one.'];
        endif;

        session([self::RESET_SESSION_KEY => $pending]);
        $left = self::RESET_CODE_ATTEMPTS - $pending['attempts'];

        return ['ok' => false, 'status' => 422, 'message' => 'That code is not correct. '.$left.' '.Str::plural('attempt', $left).' left.'];
    }

    public function clearResetCode(): void
    {
        session()->forget(self::RESET_SESSION_KEY);
    }

    protected function resetCodeHash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    /**
     * The inner HTML for CommunicationSendMail's branded template.
     */
    protected function resetEmailBody($user, string $code): string
    {
        $firstName = trim((string) (isset($user->employee->first_name) && $user->employee->first_name != '' ? $user->employee->first_name : $user->name));

        $html = '<p style="margin:0 0 14px; font-size:15px; line-height:1.5; color:#1f2937;">Hello '.e($firstName !== '' ? $firstName : 'there').',</p>';
        $html .= '<p style="margin:0 0 14px; font-size:15px; line-height:1.5; color:#1f2937;">Use this code to reset your PIN for encrypted staff documents:</p>';
        $html .= '<p style="margin:0 0 14px; font-size:30px; line-height:1.2; font-weight:bold; letter-spacing:6px; color:#0f7b76;">'.e($code).'</p>';
        $html .= '<p style="margin:0 0 14px; font-size:15px; line-height:1.5; color:#1f2937;">Enter it on the PIN page, in the browser you asked from. It works once, for '.self::RESET_CODE_MINUTES.' minutes.</p>';
        $html .= '<p style="margin:0; font-size:13px; line-height:1.5; color:#64748b;">If you did not ask for this, ignore this email. Your PIN has not been changed, and nobody can change it without this code.</p>';

        return $html;
    }

    /**
     * The college's default SMTP account, or NULL when none is set up.
     */
    protected function mailConfiguration(): ?array
    {
        $smtp = ComonSmtp::where('is_default', 1)->first();
        if(!$smtp):
            $smtp = ComonSmtp::first();
        endif;
        if(!$smtp || empty($smtp->smtp_host)):
            return null;
        endif;

        return [
            'smtp_host' => $smtp->smtp_host,
            'smtp_port' => (!empty($smtp->smtp_port) ? $smtp->smtp_port : '587'),
            'smtp_username' => $smtp->smtp_user,
            'smtp_password' => $smtp->smtp_pass,
            'smtp_encryption' => (!empty($smtp->smtp_encryption) ? $smtp->smtp_encryption : 'tls'),
            'from_email' => self::FROM_EMAIL,
            'from_name' => self::FROM_NAME,
        ];
    }

    /**
     * May the person at the keyboard open this encrypted document?
     *
     * The permission and the PIN checked are the holder's — see holder(). In
     * an impersonated session that is the impersonator, so the PIN asked for
     * is their own, and the PIN of the account they are signed in as opens
     * nothing for them. Each refusal is written to the log.
     *
     * @return array ['ok' => bool, 'status' => int, 'message' => string]
     */
    public function challengeForDocument($pin, EmployeeDocuments $document): array
    {
        $impersonating = self::isImpersonating();
        $holder = self::holder();

        if(!$holder):
            $this->log(self::EVENT_DENIED_IMPERSONATION, $document);
            return ['ok' => false, 'status' => 403, 'message' => 'Encrypted documents cannot be opened from this session. Leave impersonation and use your own account.'];
        endif;

        if(!self::canUse($holder)):
            $this->log(self::EVENT_DENIED_PERMISSION, $document);
            return ['ok' => false, 'status' => 403, 'message' => ($impersonating
                ? 'You are signed in as another user, and your own account does not have permission to open encrypted documents.'
                : 'You do not have permission to open encrypted documents.')];
        endif;

        return $this->verifyPin($holder, $pin, $document, $impersonating);
    }

    /**
     * May this user replace their own PIN?
     *
     * Never from an impersonated session, whatever PIN is offered: this is
     * the door to the account's own PIN, and it stays shut to anyone signed
     * in as that account.
     *
     * @return array ['ok' => bool, 'status' => int, 'message' => string]
     */
    public function challengeOwner($user, $pin): array
    {
        if(self::isImpersonating()):
            $this->log(self::EVENT_DENIED_IMPERSONATION);
            return ['ok' => false, 'status' => 403, 'message' => 'You are signed in as another user. A document PIN cannot be set or changed while impersonating.'];
        endif;

        if(!self::canUse($user)):
            $this->log(self::EVENT_DENIED_PERMISSION);
            return ['ok' => false, 'status' => 403, 'message' => 'You do not have permission to use a document PIN.'];
        endif;

        return $this->verifyPin($user, $pin, null, false);
    }

    /**
     * Check a PIN against its holder's, counting wrong attempts against that
     * holder. $impersonating only changes the wording.
     */
    protected function verifyPin($holder, $pin, ?EmployeeDocuments $document, bool $impersonating): array
    {
        $row = $this->pinFor($holder);
        if(!$row):
            return ['ok' => false, 'status' => 409, 'message' => ($impersonating
                ? 'Your own account has no document PIN yet. Leave impersonation, then set one up from "PIN" in your account menu.'
                : 'You have not set up a document PIN yet. Open "PIN" from your account menu to create one.')];
        endif;

        if($row->locked_until && $row->locked_until->isFuture()):
            $this->log(self::EVENT_PIN_LOCKED, $document);
            return ['ok' => false, 'status' => 423, 'message' => $this->lockedMessage($row)];
        endif;

        // Something that could not be a PIN at all is a slip, not a guess.
        $pin = trim((string) $pin);
        if(!preg_match('/^\d{'.self::PIN_MIN_LENGTH.','.self::PIN_MAX_LENGTH.'}$/', $pin)):
            return ['ok' => false, 'status' => 422, 'message' => 'Enter your '.($impersonating ? 'own ' : '').'document PIN ('.self::PIN_MIN_LENGTH.' to '.self::PIN_MAX_LENGTH.' digits).'];
        endif;

        // A lock that has run out starts a fresh run of attempts.
        if($row->locked_until):
            $row->failed_attempts = 0;
            $row->locked_until = null;
        endif;

        if(Hash::check($pin, $row->pin)):
            $row->failed_attempts = 0;
            $row->save();

            return ['ok' => true, 'status' => 200, 'message' => ''];
        endif;

        $row->failed_attempts = $row->failed_attempts + 1;
        if($row->failed_attempts >= self::MAX_ATTEMPTS):
            $row->locked_until = now()->addMinutes(self::LOCK_MINUTES);
            $row->save();
            $this->log(self::EVENT_PIN_LOCKED, $document);

            return ['ok' => false, 'status' => 423, 'message' => $this->lockedMessage($row)];
        endif;

        $row->save();
        $this->log(self::EVENT_PIN_FAILED, $document);

        $left = self::MAX_ATTEMPTS - $row->failed_attempts;

        return ['ok' => false, 'status' => 422, 'message' => 'That PIN is not correct. '
            .($impersonating ? 'Use your own document PIN, not the one for the account you are signed in as. ' : '')
            .$left.' '.Str::plural('attempt', $left).' left before your PIN is locked.'];
    }

    protected function lockedMessage(UserDocumentPin $row): string
    {
        $minutes = max(1, (int) ceil(now()->diffInSeconds($row->locked_until, false) / 60));

        return 'Too many wrong PINs. Your document PIN is locked for '.$minutes.' more '.Str::plural('minute', $minutes).'.';
    }

    public function encrypt(string $contents): string
    {
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $cipher = openssl_encrypt($contents, self::CIPHER, $this->key(), OPENSSL_RAW_DATA, $iv, $tag, '', self::TAG_LENGTH);

        if($cipher === false):
            throw new \RuntimeException('The document could not be encrypted.');
        endif;

        return self::MAGIC.$iv.$tag.$cipher;
    }

    /**
     * Returns NULL when the payload is not one of ours, has been altered, or
     * was written under a different app key.
     */
    public function decrypt(string $payload): ?string
    {
        $header = strlen(self::MAGIC) + self::IV_LENGTH + self::TAG_LENGTH;
        if(strlen($payload) < $header || substr($payload, 0, strlen(self::MAGIC)) !== self::MAGIC):
            return null;
        endif;

        $iv = substr($payload, strlen(self::MAGIC), self::IV_LENGTH);
        $tag = substr($payload, strlen(self::MAGIC) + self::IV_LENGTH, self::TAG_LENGTH);
        $plain = openssl_decrypt(substr($payload, $header), self::CIPHER, $this->key(), OPENSSL_RAW_DATA, $iv, $tag);

        return ($plain === false ? null : $plain);
    }

    /**
     * A key of its own, derived from the app key, so document encryption does
     * not share a key with sessions and cookies. Rotating APP_KEY makes every
     * encrypted document unreadable — re-encrypt them first.
     */
    protected function key(): string
    {
        $key = (string) config('app.key');
        if(Str::startsWith($key, 'base64:')):
            $key = base64_decode(substr($key, 7));
        endif;

        return hash_hkdf('sha256', $key, 32, 'staff-document-vault');
    }

    /**
     * The name to hand the file back under: the stored name without the
     * upload timestamp in front or the vault extension behind.
     */
    public static function downloadName(EmployeeDocuments $document): string
    {
        $name = (string) $document->current_file_name;
        if(Str::endsWith($name, self::EXTENSION)):
            $name = substr($name, 0, -strlen(self::EXTENSION));
        endif;
        $name = preg_replace('/^\d{9,}_/', '', $name);

        return ($name !== '' ? $name : 'document');
    }

    public static function isViewable(EmployeeDocuments $document): bool
    {
        return isset(self::VIEWABLE[strtolower((string) $document->doc_type)]);
    }

    /**
     * Write one line of the audit trail.
     *
     * $employeeId is whose record the line is about. It is read from the
     * document when there is one; a PIN event has no document, so pass the
     * PIN owner's employee id (it defaults to the signed-in user's own).
     *
     * Returns false when the line could not be written, so a caller about to
     * hand over an encrypted document can refuse instead of doing it unseen.
     */
    public function log(string $event, ?EmployeeDocuments $document = null, ?int $employeeId = null): bool
    {
        $user = auth()->user();
        if(!$user):
            return false;
        endif;

        if($document):
            $employeeId = $document->employee_id;
        elseif(!$employeeId):
            $employeeId = Employee::where('user_id', $user->id)->value('id');
        endif;

        try {
            EmployeeDocumentAccessLog::create([
                'employee_document_id' => ($document ? $document->id : null),
                'employee_id' => $employeeId,
                'user_id' => $user->id,
                'impersonator_id' => self::impersonatorId(),
                'event' => $event,
                'is_encrypted' => ($document && $document->is_encrypted == 1 ? 1 : 0),
                'document_name' => ($document ? Str::limit((string) $document->display_file_name, 188, '...') : null),
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 252, '...'),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Document access could not be logged ('.$event.'): '.$e->getMessage());

            return false;
        }
    }
}
