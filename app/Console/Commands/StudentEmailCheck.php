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

        $this->line('Organisational unit: '.config('student_email.google.org_unit', '/').' (must exist in Admin console › Directory › Organisational units; "/" = top level)');
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
