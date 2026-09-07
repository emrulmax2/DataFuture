<?php

namespace App\Models;

use App\Support\SignatureAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The staff member's own overrides for their college email signature.
 *
 * Nothing here is required: the signature is built by layering a saved row on
 * top of what the HR record already knows (job title, work email,
 * extension, mobile...). A blank column falls back to HR, so a promotion or a
 * new extension flows into the signature without the employee re-editing it.
 */
class EmployeeEmailSignature extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'employee_id',
        'display_name',
        'qualifications',
        'job_title',
        'email',
        'extension',
        'mobile',
        'created_by',
        'updated_by',
    ];

    /**
     * The editable text columns, in the order they appear on the tab. Used by
     * both the defaults resolver and the controller so the two never drift.
     */
    /**
     * Fields where an empty box means "leave this out of the signature"
     * rather than "fall back to the HR record". Stored as '' instead of NULL
     * so the two intentions stay distinguishable.
     */
    const BLANKABLE_FIELDS = [
        'extension',
        'mobile',
    ];

    const TEXT_FIELDS = [
        'display_name',
        'qualifications',
        'job_title',
        'email',
        'extension',
        'mobile',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * What the signature would say if the employee never edited it — straight
     * from the HR record, plus the college-wide values from config.
     */
    public static function defaultsFor(Employee $employee){
        $employment = $employee->employment;

        $name = trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));

        $defaults = [];
        $defaults['display_name'] = self::humaniseName($name);
        $defaults['qualifications'] = '';
        $defaults['job_title'] = (isset($employment->employeeJobTitle->name) ? $employment->employeeJobTitle->name : '');
        $defaults['email'] = self::preferredEmail($employee, $employment);

        // The signature always shows the switchboard number, so the only
        // thing worth reading off the HR record is the desk extension — which
        // is what office_telephone usually holds ("230").
        $office = self::firstFilled([
            (isset($employment->office_telephone) ? $employment->office_telephone : null),
            (isset($employee->telephone) ? $employee->telephone : null),
        ]);
        $digits = preg_replace('/\D/', '', $office);
        $defaults['extension'] = (strlen($digits) > 0 && strlen($digits) <= 5 ? $digits : '');
        $defaults['mobile'] = self::firstFilled([
            (isset($employment->mobile) ? $employment->mobile : null),
            (isset($employee->mobile) ? $employee->mobile : null),
        ]);

        return $defaults;
    }

    /**
     * The values the signature is actually rendered from: the saved row laid
     * over the HR defaults. A saved column that is blank is treated as "not
     * set" and falls back, which is also how the Reset button works.
     */
    public static function fieldsFor(Employee $employee, $overrides = []){
        $defaults = self::defaultsFor($employee);
        $saved = self::where('employee_id', $employee->id)->get()->first();

        $fields = $defaults;

        foreach(self::TEXT_FIELDS as $field):
            if(array_key_exists($field, $overrides)):
                $fields[$field] = trim((string) $overrides[$field]);
            elseif($saved && $saved->{$field} !== null):
                // '' is a saved decision to hide the field; NULL means the
                // employee never set it, so the HR default stands.
                $fields[$field] = trim((string) $saved->{$field});
            endif;
        endforeach;

        // Never rendered from a form field: these follow the employee record
        // and the college config, not anything the staff member types.
        $fields['icons'] = self::icons();
        // The rings around the photo are drawn into the image itself; see
        // SignatureAvatar for why they cannot be CSS here.
        // Both are drawn at a higher density than they are displayed at and
        // sized down in the markup. A signature is read on Retina laptops and
        // phones, where an image built 1:1 with its CSS size looks soft against
        // the text beside it.
        $fields['photo_url'] = self::absoluteUrl(SignatureAvatar::urlFor($employee, 2));
        $fields['badge_url'] = self::absoluteUrl(SignatureAvatar::badgeUrlFor($employee, 3));
        $fields['image_host'] = parse_url($fields['icons']['lcc-logo'], PHP_URL_HOST);
        $fields['images_public'] = self::hostIsPublic($fields['icons']['lcc-logo']);
        // The college's own details are fixed for everyone, so they come
        // from config rather than from anything the employee can edit.
        $fields['telephone'] = config('emailsignature.telephone');
        $fields['website'] = config('emailsignature.website.label');
        $fields['website_url'] = config('emailsignature.website.url');
        $fields['address_line_1'] = config('emailsignature.address.line_1');
        $fields['address_line_2'] = config('emailsignature.address.line_2');
        $fields['socials'] = array_filter((array) config('emailsignature.socials'));
        $fields['disclaimer'] = config('emailsignature.disclaimer');
        $fields['has_saved_row'] = ($saved ? true : false);
        $fields['updated_at'] = ($saved && $saved->updated_at ? $saved->updated_at->format('d M Y, H:i') : '');

        return $fields;
    }

    /**
     * HR names are often stored shouted ("CHRISTOPHER POWELL"); a signature
     * should not shout. Anything already mixed case is left exactly as typed.
     */
    protected static function humaniseName($name){
        $name = trim(preg_replace('/\s+/', ' ', (string) $name));
        if($name === ''):
            return '';
        endif;
        if(preg_match('/[a-z]/', $name)):
            return $name;
        endif;

        return mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Employees can hold both a personal and a college address; the signature
     * must always advertise the college one where there is a choice.
     */
    protected static function preferredEmail(Employee $employee, $employment){
        $candidates = [
            (isset($employment->email) ? $employment->email : null),
            (isset($employee->email) ? $employee->email : null),
        ];
        $candidates = array_values(array_filter($candidates, fn ($item) => filled($item)));

        foreach($candidates as $candidate):
            if(str_ends_with(strtolower(trim($candidate)), 'lcc.ac.uk')):
                return strtolower(trim($candidate));
            endif;
        endforeach;

        return (isset($candidates[0]) ? strtolower(trim($candidates[0])) : '');
    }

    protected static function firstFilled($values){
        foreach($values as $value):
            if(filled($value)):
                return trim((string) $value);
            endif;
        endforeach;

        return '';
    }

    /**
     * The crest, the contact glyphs and the social badges, ready to drop into
     * an <img src>. Built here rather than in the blades so both variants and
     * the photo all resolve against the same host.
     */
    protected static function icons(){
        $names = ['phone', 'mobile', 'email', 'website', 'address', 'corner', 'lcc-logo', 'facebook', 'instagram', 'linkedin', 'youtube', 'x'];

        $icons = [];
        foreach($names as $name):
            $icons[$name] = self::imageUrl('build/assets/images/signature/'.$name.'.png');
        endforeach;

        return $icons;
    }

    /**
     * Email clients fetch images over the wire, so every src has to be a fully
     * qualified URL — a root-relative /storage/... path would silently break.
     *
     * asset() rather than url() on purpose: the signature outlives the request
     * that produced it, so the host has to come from config and not from
     * whichever hostname the member of staff happened to browse in on.
     */
    public static function imageUrl($path){
        $path = ltrim((string) $path, '/');
        $base = trim((string) config('emailsignature.asset_base'));

        return ($base !== '' ? rtrim($base, '/').'/'.$path : asset($path));
    }

    protected static function absoluteUrl($url){
        $url = trim((string) $url);
        if($url === '' || preg_match('/^(https?:)?\/\//i', $url) || str_starts_with($url, 'data:')):
            return $url;
        endif;

        return self::imageUrl($url);
    }

    /**
     * Whether a mail client's servers could actually fetch this image. A
     * signature built against localhost or a private LAN address pastes in
     * looking fine and then shows broken icons to every recipient, so the tab
     * warns about it rather than letting someone find out after sending.
     */
    protected static function hostIsPublic($url){
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if($host === ''):
            return false;
        endif;

        if(in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]', '0.0.0.0'], true)):
            return false;
        endif;
        if(preg_match('/\.(local|test|localhost|internal|invalid|example)$/', $host)):
            return false;
        endif;
        if(preg_match('/^(10|127)\./', $host) || preg_match('/^192\.168\./', $host)):
            return false;
        endif;
        if(preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\./', $host)):
            return false;
        endif;

        return true;
    }
}
