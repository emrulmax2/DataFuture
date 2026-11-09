<?php

namespace Tests\Fakes;

use App\Services\StudentEmail\WorkspaceDirectory;

/** Google Workspace stand-in: records what would have been created, licensed or deleted. */
class FakeWorkspace implements WorkspaceDirectory
{
    public bool $configured = false;
    public bool $exists = false;
    public ?string $fail = null;
    public array $created = [];
    public array $licensed = [];
    public array $deleted = [];
    /** email => creationTime, as Google would report */
    public array $accounts = [];

    public function configured(): bool
    {
        return $this->configured;
    }

    public function createUser(string $email, string $givenName, string $familyName, string $password, bool $changeAtFirstLogin, string $orgUnit = '/'): string
    {
        if ($this->fail) {
            throw new \RuntimeException($this->fail);
        }
        if ($this->exists) {
            return 'exists';
        }
        $this->created[] = [$email, $givenName, $familyName, $password, $changeAtFirstLogin, $orgUnit];
        $this->accounts[$email] = now()->toIso8601String();

        return 'created';
    }

    public function assignLicence(string $email): ?string
    {
        $this->licensed[] = $email;

        return 'TEST-SKU';
    }

    public function getUser(string $email): ?array
    {
        return isset($this->accounts[$email]) ? ['email' => $email, 'orgUnitPath' => '/Students', 'creationTime' => $this->accounts[$email]] : null;
    }

    public function deleteUser(string $email): void
    {
        $this->deleted[] = $email;
        unset($this->accounts[$email]);
    }
}
