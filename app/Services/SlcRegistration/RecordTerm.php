<?php

namespace App\Services\SlcRegistration;

use Illuminate\Support\Facades\DB;

/**
 * The Session Term and Attendance Term the attendance is recorded with on the SLC tab (Bulk Attendance &
 * Installment), as step 4 shows and lets staff change them (user, 13 Nov 2026).
 *
 * Suggested: the year's first Term 1–3 with no attendance on the SLC tab (Term 1 there → Term 2 …), its
 * Attendance Term the course instance's term with that session term. Chosen: either can be changed; the
 * other follows it — a Session Term picks the instance's term with it, an Attendance Term of the instance
 * its session term — unless that one was chosen too.
 *
 * $terms: [session term => ['id' => term declaration id, 'name' => …]] — the course instance's terms.
 */
class RecordTerm
{
    public const SESSIONS = [1, 2, 3, 4]; // as SLC tab › Add Attendance offers (Term 01–04)

    public static function suggest(array $terms, array $takenSessions): array
    {
        $n = collect([1, 2, 3])->first(fn ($k) => !in_array($k, $takenSessions, true));

        return ['sessionTerm' => $n, 'termDeclarationId' => $n ? ($terms[$n]['id'] ?? null) : null, 'term' => $n ? ($terms[$n]['name'] ?? null) : null];
    }

    public static function resolve(array $terms, array $suggested, ?int $chosenSession, ?int $chosenDecl): array
    {
        $sessionOf = collect($terms)->mapWithKeys(fn ($t, $n) => [(int) $t['id'] => (int) $n])->all();
        $session = $chosenSession ?: ($chosenDecl && isset($sessionOf[$chosenDecl]) ? $sessionOf[$chosenDecl] : $suggested['sessionTerm']);
        $decl = $chosenDecl ?: ($session ? ($terms[$session]['id'] ?? ($session === $suggested['sessionTerm'] ? $suggested['termDeclarationId'] : null)) : null);
        $name = $decl ? (collect($terms)->firstWhere('id', $decl)['name'] ?? DB::table('term_declarations')->where('id', $decl)->value('name')) : null;

        return ['sessionTerm' => $session, 'termDeclarationId' => $decl ? (int) $decl : null, 'term' => $name, 'chosen' => (bool) ($chosenSession || $chosenDecl)];
    }
}
