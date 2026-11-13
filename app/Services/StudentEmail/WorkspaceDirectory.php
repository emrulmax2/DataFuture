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

    /**
     * Make sure the organisational unit exists ("/Students/2027 January"), creating it — and any
     * missing parent — when it doesn't. Returns 'exists' or 'created'.
     *
     * @throws \RuntimeException when Google refuses (e.g. the orgunit scope isn't authorised)
     */
    public function ensureOrgUnit(string $path): string;

    /** Put an existing account in that organisational unit. */
    public function moveUser(string $email, string $orgUnit): void;

    /**
     * Add the account to a group as a member. Returns 'added', or 'member' when it already was.
     *
     * @throws \RuntimeException when Google refuses (e.g. the group.member scope isn't authorised)
     */
    public function addToGroup(string $group, string $email): string;
}
