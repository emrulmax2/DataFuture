<?php

namespace App\Console\Commands;

use Google\Client;
use Google\Service\Directory;
use Google\Service\Licensing;
use Illuminate\Console\Command;

/**
 * Read-only check of the Google Workspace connection student emails use
 * (config/student_email.php): key file, the admin it acts as, the Directory API and,
 * when a licence is configured, the License Manager API. Creates nothing.
 */
class StudentEmailCheck extends Command
{
    protected $signature = 'student-email:check';

    protected $description = 'Check the Google Workspace connection used to create student email accounts (read-only)';

    public function handle(): int
    {
        $key = (string) config('student_email.google.credentials');
        $path = str_starts_with($key, '/') ? $key : storage_path('app/'.ltrim($key, '/'));
        $admin = (string) config('student_email.google.admin_email');
        $ok = true;

        $this->line('Key file: '.($key === '' ? 'GOOGLE_WORKSPACE_CREDENTIALS is empty' : $path.(is_readable($path) ? ' (found)' : ' (NOT FOUND)')));
        $this->line('Acts as: '.($admin !== '' ? $admin : 'GOOGLE_WORKSPACE_ADMIN_EMAIL is empty'));
        if ($key === '' || !is_readable($path) || $admin === '') {
            $this->error('Not configured.');
            return self::FAILURE;
        }
        $json = json_decode((string) file_get_contents($path), true) ?: [];
        $this->line('Service account: '.($json['client_email'] ?? '?').' · client ID '.($json['client_id'] ?? '?'));

        $client = fn (array $scopes) => tap(new Client(), function ($c) use ($path, $admin, $scopes) {
            $c->setAuthConfig($path);
            $c->setScopes($scopes);
            $c->setSubject($admin);
        });

        try {
            $me = (new Directory($client([Directory::ADMIN_DIRECTORY_USER])))->users->get($admin);
            $this->info('Directory API: OK. '.$me->getPrimaryEmail().' is '.($me->getIsAdmin() ? 'a super admin' : 'NOT a super admin (it cannot create accounts)').'.');
            $ok = $ok && $me->getIsAdmin();
        } catch (\Throwable $e) {
            $this->error('Directory API: '.$this->explain($e));
            $ok = false;
        }

        $product = (string) config('student_email.google.licence_product');
        $sku = (string) config('student_email.google.licence_sku');
        if ($product === '' || $sku === '') {
            $this->line('Licence: none configured (accounts get whatever Google assigns automatically).');
        } else {
            try {
                $list = (new Licensing($client([Licensing::APPS_LICENSING])))->licenseAssignments
                    ->listForProductAndSku($product, $sku, (string) config('student_email.domain'), ['maxResults' => 1]);
                $this->info('Licence API: OK ('.$product.' / '.$sku.(count($list->getItems() ?: []) ? ', in use' : '').').');
            } catch (\Throwable $e) {
                $this->error('Licence API: '.$this->explain($e));
                $ok = false;
            }
        }

        $this->line('Organisational unit: '.config('student_email.google.org_unit', '/').' (must exist in Admin console › Directory › Organisational units; "/" = top level) — used when a student has no intake');

        // the intake OUs (/Students/2027 January …) and the student group: read only, nothing is made here
        $parent = trim((string) config('student_email.google.intake_ou_parent', ''));
        if ($parent === '') {
            $this->line('Intake OUs: off (GOOGLE_WORKSPACE_INTAKE_OU_PARENT is empty).');
        } else {
            try {
                $orgunits = (new Directory($client([Directory::ADMIN_DIRECTORY_ORGUNIT])))->orgunits;
                $kids = trim($parent, '/') === '' ? $orgunits->listOrgunits('my_customer', ['type' => 'children']) : $orgunits->listOrgunits('my_customer', ['orgUnitPath' => trim($parent, '/'), 'type' => 'children']);
                $names = collect($kids->getOrganizationUnits() ?: [])->map(fn ($o) => $o->getName())->sort()->values()->all();
                $this->info('Intake OUs: OK. '.$parent.' has '.(count($names) ? implode(', ', $names) : 'no OUs yet').'; a missing intake OU is made when a student account is created.');
            } catch (\Throwable $e) {
                if ((int) $e->getCode() === 404) {
                    // the scope works: Google answered, the parent just isn't there yet
                    $this->info('Intake OUs: OK. '.$parent.' doesn\'t exist yet; it is made, with the intake OU (e.g. '.rtrim($parent, '/').'/2027 January), when the next student account is created.');
                } else {
                    $this->error('Intake OUs ('.$parent.'): '.$this->explain($e).' Needs scope admin.directory.orgunit.');
                    $ok = false;
                }
            }
        }
        $group = trim((string) config('student_email.google.student_group', ''));
        if ($group === '') {
            $this->line('Student group: off (GOOGLE_WORKSPACE_STUDENT_GROUP is empty).');
        } else {
            try {
                (new Directory($client([Directory::ADMIN_DIRECTORY_GROUP_MEMBER])))->members->listMembers($group, ['maxResults' => 1]);
                $this->info('Student group: OK. New accounts join '.$group.(config('student_email.address_prefix') !== '' ? ' (not test addresses: STUDENT_EMAIL_ADDRESS_PREFIX is set here)' : '').'.');
            } catch (\Throwable $e) {
                $this->error('Student group ('.$group.'): '.$this->explain($e).' Needs scope admin.directory.group.member.');
                $ok = false;
            }
        }
        $this->line('New addresses look like: '.config('student_email.address_prefix').'lcc20270001@'.config('student_email.domain').' · first password = date of birth as DDMMYYYY');
        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function explain(\Throwable $e): string
    {
        $m = $e->getMessage();
        if (str_contains($m, 'unauthorized_client')) {
            return 'unauthorized_client: the client ID is not authorised for this scope yet (Admin console › Security › API controls › Domain-wide delegation), or it is still propagating.';
        }
        if (str_contains($m, 'Not Authorized to access this resource/api')) {
            return '403 Not Authorized: delegation works, but '.config('student_email.google.admin_email').' is not an admin who can manage users. Use a Super Admin in GOOGLE_WORKSPACE_ADMIN_EMAIL, or give that account a role with Admin API privileges › Users (Create, Read, Update, Delete).';
        }
        if (str_contains($m, 'accessNotConfigured') || str_contains($m, 'has not been used') || str_contains($m, 'is disabled')) {
            return 'the API is not enabled in the Google Cloud project: '.$m;
        }

        return $m;
    }
}
