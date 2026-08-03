<?php

namespace App\Support;

class GlobalSearch
{
    public static function forCurrentUser(): array
    {
        if (!auth()->check() || auth('agent')->check() || auth('applicant')->check() || auth('student')->check()) {
            return self::makeConfig(false, false, false);
        }

        $privileges = auth()->user()->priv();

        return self::makeConfig(
            self::enabled($privileges['applicant'] ?? null),
            self::enabled($privileges['live'] ?? null),
            self::enabled($privileges['hr_porta'] ?? null),
        );
    }

    private static function makeConfig(bool $applicants, bool $students, bool $employees): array
    {
        $labels = [];

        if ($applicants) {
            $labels[] = 'Applicant';
        }

        if ($students) {
            $labels[] = 'Student';
        }

        if ($employees) {
            $labels[] = 'Staff';
        }

        return [
            'applicants' => $applicants,
            'students' => $students,
            'employees' => $employees,
            'show' => $applicants || $students || $employees,
            'placeholder' => $labels ? 'Search for '.implode(', ', $labels).'...' : 'Search...',
            'empty_label' => $labels ? strtolower(implode(', ', $labels)) : 'records',
        ];
    }

    private static function enabled(mixed $value): bool
    {
        return !empty($value) && $value !== '0';
    }
}
