<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Lab404\Impersonate\Models\Impersonate;
use App\Services\RemoteAccessService;
use App\Support\LegacyPrivilegeMap;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes ,Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that appends to returned entities.
     *
     * @var array
     */
    protected $appends = ['photo', 'photo_url', 'remote_access', 'full_name'];

    /**
     * The getter that return accessible URL for user photo.
     *
     * @var array
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo !== null && Storage::disk('s3')->exists('public/users/'.$this->id.'/'.$this->photo)) {
            return Storage::disk('s3')->url('public/users/'.$this->id.'/'.$this->photo);
        } else {
            return \App\Support\Avatar::initials($this->name);
        }
    }
    

    public function getPhotoAttribute($value){
        return $value;
    }

    public function userRole(){
        return $this->hasMany(UserRole::class, 'user_id', 'id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(ApplicantInterview::class);
    }

    public function getFullNameAttribute(){
        return (isset($this->employee->full_name) && !empty($this->employee->full_name) ? $this->employee->full_name : $this->name);
    }

    public function employee(){
        return $this->hasOne(Employee::class, 'user_id', 'id')->withTrashed()->latestOfMany();
    }
    
    /**
     * Per-request caches. priv() is read dozens of times per page (90+ times for
     * access_account_type alone), and every call used to issue its own query.
     */
    private ?array $privCache = null;
    private ?array $permsCache = null;

    /**
     * The employee's permissions from the new system, keyed by the new flat key.
     */
    public function perms(): array
    {
        if ($this->permsCache === null) {
            $this->permsCache = EmployeePermission::where('user_id', $this->id)
                ->pluck('value', 'key')
                ->toArray();
        }

        return $this->permsCache;
    }

    public function hasPerm(string $key): bool
    {
        $value = $this->perms()[$key] ?? null;

        return !empty($value) && $value !== '0';
    }

    public function permValue(string $key): ?string
    {
        $value = $this->perms()[$key] ?? null;

        return ($value === null || $value === '') ? null : (string) $value;
    }

    /**
     * Flat "legacy name => access" map that the whole application gates on.
     *
     * Which table this reads is controlled by config('privileges.source'), so the
     * cutover to employee_permissions is one env var and is instantly revertible.
     * The new system is reverse-mapped back onto the legacy names, so the 60+
     * existing priv()['x'] checks keep working untouched.
     */
    public function priv(){
        if ($this->privCache !== null) {
            return $this->privCache;
        }

        if ($this->isSuperAdmin()) {
            return $this->privCache = $this->privForSuperAdmin();
        }

        return $this->privCache = config('privileges.source') === 'new'
            ? $this->privFromNewSystem()
            : $this->privFromLegacySystem();
    }

    /**
     * Super admins are never gated: not by priv(), not by the route middleware,
     * and not by the remote-access check. Without this, one wrong rule could lock
     * every administrator out of the screens needed to fix that rule.
     */
    public function isSuperAdmin(): bool
    {
        return in_array((int) $this->id, self::bypassUserIds(), true);
    }

    /**
     * The bypass employee ids resolved to user ids, once per process.
     *
     * Resolving them per user would lazy-load the employee relation on every
     * User, which is an N+1 on any page that lists users (remote_access is an
     * appended attribute, so merely serialising a User triggers this).
     */
    private static ?array $bypassUserIds = null;

    public static function bypassUserIds(): array
    {
        if (self::$bypassUserIds !== null) {
            return self::$bypassUserIds;
        }

        $ids = array_map('intval', config('privileges.bypass_user_ids', []));
        $employeeIds = config('privileges.bypass_employee_ids', []);

        if (!empty($employeeIds)) {
            $ids = array_merge($ids, Employee::whereIn('id', $employeeIds)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->map(fn($id) => (int) $id)
                ->all());
        }

        return self::$bypassUserIds = array_values(array_unique($ids));
    }

    /**
     * Every permission there is, granted.
     *
     * Built from the key map rather than the user's rows, so a super admin
     * automatically holds any permission added in future.
     */
    private function privForSuperAdmin(): array
    {
        $priv = [];

        foreach (array_keys(LegacyPrivilegeMap::MAP) as $categoryAndName) {
            $priv[explode('.', $categoryAndName, 2)[1]] = '1';
        }

        // Internal links are per-link rows, so grant each one that exists.
        foreach (InternalLink::pluck('id') as $linkId) {
            $priv[(string) $linkId] = '1';
        }

        // Valued keys are not flags. Keep whatever the user actually has, and
        // fall back to Admin for the accounts role rather than inventing "1"
        // for every valued key.
        $actual = config('privileges.source') === 'new'
            ? $this->privFromNewSystem()
            : $this->privFromLegacySystem();

        $priv['access_account_type'] = $actual['access_account_type'] ?? '1';
        $priv['date_range'] = $actual['date_range'] ?? '';

        return $priv;
    }

    private function privFromLegacySystem(): array
    {
        return $this->hasMany(UserPrivilege::class, 'user_id', 'id')
            ->select('access', 'name')->pluck('access', 'name')->toArray();
    }

    private function privFromNewSystem(): array
    {
        $priv = [];

        foreach ($this->perms() as $key => $value) {
            $legacyName = LegacyPrivilegeMap::toLegacyName($key);

            if ($legacyName !== null) {
                $priv[$legacyName] = $value;
            }
        }

        return $priv;
    }

    /**
     * Ids of the internal links this user may see.
     *
     * In the flat priv() map an internal link appears under its numeric link id
     * (legacy stored the id in `name`; the new system reverse-maps
     * internal_link_<id> back to it), and no other privilege uses a numeric key.
     * Reading it from priv() keeps this following config('privileges.source')
     * instead of querying user_privileges directly.
     */
    public function permittedInternalLinkIds(): array
    {
        $ids = [];

        foreach ($this->priv() as $key => $value) {
            if (ctype_digit((string) $key) && !empty($value) && $value != '0') {
                $ids[] = (int) $key;
            }
        }

        return $ids;
    }

    /**
     * Drops the per-request caches. Only needed after writing this user's
     * permissions and then re-reading them in the same request.
     */
    public function forgetPrivileges(): void
    {
        $this->privCache = null;
        $this->permsCache = null;
    }

    /**
     * May this user use the portal from where they are right now?
     *
     * Answers the "can they be here at all" question: either they hold remote
     * access, or they are on a college network. Delegates to RemoteAccessService,
     * which reads whichever privilege source is configured.
     */
    public function getRemoteAccessAttribute(){
        return app(RemoteAccessService::class)->allows($this);
    }

    public function hourauth(){
        return $this->hasMany(EmployeeHourAuthorisedBy::class, 'user_id', 'id');
    }

    public function holiauth(){
        return $this->hasMany(EmployeeHolidayAuthorisedBy::class, 'user_id', 'id');
    }
    
}
