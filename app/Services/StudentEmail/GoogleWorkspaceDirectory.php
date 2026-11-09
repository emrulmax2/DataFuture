<?php

namespace App\Services\StudentEmail;

use Google\Client;
use Google\Service\Directory;
use Google\Service\Exception as GoogleException;
use Google\Service\Licensing;

/**
 * Google Workspace through a service account with domain-wide delegation, acting as the
 * super admin in config('student_email.google.admin_email'):
 *  - Admin SDK Directory API, scope admin.directory.user (create / read / delete accounts);
 *  - Enterprise License Manager API, scope apps.licensing (only asked for when a licence
 *    product + SKU are configured, so an unlicensed setup needs just the first scope).
 */
class GoogleWorkspaceDirectory implements WorkspaceDirectory
{
    public function configured(): bool
    {
        $key = (string) config('student_email.google.credentials');

        return $key !== '' && is_readable($this->path($key)) && (string) config('student_email.google.admin_email') !== '';
    }

    public function createUser(string $email, string $givenName, string $familyName, string $password, bool $changeAtFirstLogin, string $orgUnit = '/'): string
    {
        $user = new Directory\User([
            'primaryEmail' => $email,
            'name' => new Directory\UserName(['givenName' => $givenName ?: '-', 'familyName' => $familyName ?: '-']),
            'password' => $password,
            'changePasswordAtNextLogin' => $changeAtFirstLogin,
            'orgUnitPath' => $orgUnit ?: '/',
        ]);

        try {
            $this->directory()->users->insert($user);
            return 'created';
        } catch (GoogleException $e) {
            if ((int) $e->getCode() === 409) {
                return 'exists'; // "Entity already exists"
            }
            throw $this->fail('create '.$email, $e);
        }
    }

    public function assignLicence(string $email): ?string
    {
        $product = (string) config('student_email.google.licence_product');
        $sku = (string) config('student_email.google.licence_sku');
        if ($product === '' || $sku === '') {
            return null;
        }
        $licensing = new Licensing($this->client([Licensing::APPS_LICENSING]));
        try {
            $licensing->licenseAssignments->insert($product, $sku, new Licensing\LicenseAssignmentInsert(['userId' => $email]));
        } catch (GoogleException $e) {
            if ((int) $e->getCode() !== 409) { // 409: already has it
                throw $this->fail('license '.$email, $e);
            }
        }

        return $sku;
    }

    public function getUser(string $email): ?array
    {
        try {
            $u = $this->directory()->users->get($email);
        } catch (GoogleException $e) {
            if ((int) $e->getCode() === 404) {
                return null;
            }
            throw $this->fail('read '.$email, $e);
        }

        return [
            'email' => $u->getPrimaryEmail(),
            'name' => trim((string) optional($u->getName())->getFullName()),
            'orgUnitPath' => $u->getOrgUnitPath(),
            'creationTime' => $u->getCreationTime(),
            'lastLoginTime' => $u->getLastLoginTime(),
            'suspended' => (bool) $u->getSuspended(),
            'changePasswordAtNextLogin' => (bool) $u->getChangePasswordAtNextLogin(),
        ];
    }

    public function deleteUser(string $email): void
    {
        try {
            $this->directory()->users->delete($email);
        } catch (GoogleException $e) {
            if ((int) $e->getCode() !== 404) {
                throw $this->fail('delete '.$email, $e);
            }
        }
    }

    private function directory(): Directory
    {
        return new Directory($this->client([Directory::ADMIN_DIRECTORY_USER]));
    }

    private function client(array $scopes): Client
    {
        $client = new Client();
        $client->setAuthConfig($this->path((string) config('student_email.google.credentials')));
        $client->setScopes($scopes);
        $client->setSubject((string) config('student_email.google.admin_email'));

        return $client;
    }

    private function fail(string $what, GoogleException $e): \RuntimeException
    {
        $reason = collect($e->getErrors())->pluck('message')->filter()->implode('; ');
        if (str_contains($reason.$e->getMessage(), 'INVALID_OU_ID')) {
            $reason = 'organisational unit "'.config('student_email.google.org_unit').'" does not exist (GOOGLE_WORKSPACE_STUDENT_OU; paths are case-sensitive, "/" = top level)';
        }

        return new \RuntimeException('Google could not '.$what.': '.($reason ?: $e->getMessage()), (int) $e->getCode(), $e);
    }

    /** Relative key paths are read from storage/app (git-ignored, outside the web root). */
    private function path(string $key): string
    {
        return str_starts_with($key, '/') ? $key : storage_path('app/'.ltrim($key, '/'));
    }
}
