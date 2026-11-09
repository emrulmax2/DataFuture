<?php

namespace App\Services\ClassRoutine;

use App\Models\StudentDocument;

/**
 * The download link emailed and texted with a class routine: /class-routine/{id}.{expiry}.{signature}.
 * Short enough for an SMS, signed with the app key, and it stops working after
 * config('class_routine.link_days'). It only ever opens the "Class Routine" upload it names.
 */
class ClassRoutineLink
{
    public static function url(StudentDocument $document): string
    {
        $expires = now()->addDays(max(1, (int) config('class_routine.link_days', 365)))->getTimestamp();

        return route('class.routine.download', ['token' => $document->id.'.'.base_convert((string) $expires, 10, 36).'.'.self::sign($document->id, $expires)]);
    }

    /** The document a link points to, or null when it is altered, expired or not a class routine. */
    public static function resolve(string $token): ?StudentDocument
    {
        if (!preg_match('/^(\d{1,12})\.([0-9a-z]{1,10})\.([0-9a-f]{20})$/', $token, $m)) {
            return null;
        }
        $id = (int) $m[1];
        $expires = (int) base_convert($m[2], 36, 10);
        if (!hash_equals(self::sign($id, $expires), $m[3]) || $expires < now()->getTimestamp()) {
            return null;
        }
        $document = StudentDocument::find($id);

        return $document && $document->display_file_name === ClassRoutineSender::DOCUMENT_NAME && $document->is_encrypted != 1 ? $document : null;
    }

    private static function sign(int $id, int $expires): string
    {
        return substr(hash_hmac('sha256', 'class-routine|'.$id.'|'.$expires, (string) config('app.key')), 0, 20);
    }
}
