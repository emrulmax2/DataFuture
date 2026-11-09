<?php

namespace App\Services\StudentEmail;

/** The college's Google Workspace (bound to GoogleWorkspaceDirectory; tests use a fake). */
interface WorkspaceDirectory
{
    /** Are the credentials in place to call Google at all? */
    public function configured(): bool;

    /**
     * Create the account. Returns 'created', or 'exists' when Google already has that
     * address (it is then left as it is: no password reset).
     *
     * @throws \RuntimeException when Google refuses for any other reason
     */
    public function createUser(string $email, string $givenName, string $familyName, string $password, bool $changeAtFirstLogin, string $orgUnit = '/'): string;

    /** Give the account a licence; does nothing when no product / SKU is configured. Returns the SKU or null. */
    public function assignLicence(string $email): ?string;

    /** The account, or null when Google has no such address: email, name, orgUnitPath, creationTime, lastLoginTime, suspended, changePasswordAtNextLogin. */
    public function getUser(string $email): ?array;

    /** Delete the account (its licence is released with it). */
    public function deleteUser(string $email): void;
}
