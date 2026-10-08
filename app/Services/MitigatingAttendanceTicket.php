<?php

namespace App\Services;

/**
 * The attendance flavour of a mitigating circumstances claim.
 *
 * Everything about raising it is the same as the assignment claim - what
 * differs is which queue it lands in and the reference it is keyed by, so the
 * two can be routed apart the day Registry wants them apart.
 */
class MitigatingAttendanceTicket extends MitigatingCircumstanceTicket
{
    protected function configKey(): string
    {
        return 'mitigating_attendance';
    }

    protected function referencePrefix(): string
    {
        return 'MCN';
    }
}
