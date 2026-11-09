<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantViewSession;
use Illuminate\Http\Request;

/**
 * Records how long staff spend on an applicant's record. The page calls
 * start when it loads, beat every 15 s while the tab is visible (and once on
 * hide / show), and end when the page is left (navigator.sendBeacon). Active
 * time is the sum of the gaps between heartbeats, each capped, so idle or
 * hidden tabs do not count.
 */
class AdmissionPresenceController extends Controller
{
    public function start(Request $request)
    {
        $data = $request->validate([
            'applicant_id' => 'required|integer|exists:applicants,id',
            'route' => 'nullable|string|max:120',
            'section' => 'nullable|string|max:40',
            'url' => 'nullable|string|max:500',
        ]);
        $section = in_array($data['section'] ?? '', ApplicantViewSession::SECTIONS, true)
            ? $data['section']
            : ApplicantViewSession::sectionFor($data['route'] ?? null);

        $session = ApplicantViewSession::create([
            'applicant_id' => $data['applicant_id'],
            'user_id' => $request->user()->id,
            'section' => $section,
            'route_name' => $data['route'] ?? null,
            'url' => $data['url'] ?? null,
            'started_at' => now(),
            'last_seen_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->header('User-Agent'), 0, 255),
        ]);

        return response()->json(['id' => $session->id], 201);
    }

    public function beat(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'resume' => 'nullable|boolean',
        ]);
        $session = ApplicantViewSession::where('id', $data['id'])->where('user_id', $request->user()->id)->first();
        if (!$session || $session->ended_at) {
            return response()->json(['ok' => false], 410);
        }
        // A beat after the tab was hidden ("resume") only restarts the clock.
        $this->credit($session, empty($data['resume']));
        $session->heartbeats = min(65535, $session->heartbeats + 1);
        $session->last_seen_at = now();
        $session->save();

        return response()->json(['ok' => true, 'active_seconds' => $session->active_seconds]);
    }

    public function end(Request $request)
    {
        $data = $request->validate(['id' => 'required|integer']);
        $session = ApplicantViewSession::where('id', $data['id'])->where('user_id', $request->user()->id)->first();
        if (!$session) {
            return response()->json(['ok' => false], 410);
        }
        if (!$session->ended_at) {
            $this->credit($session, true);
            $session->last_seen_at = now();
            $session->ended_at = now();
            $session->save();
        }

        return response()->json(['ok' => true, 'active_seconds' => $session->active_seconds]);
    }

    /** Adds the time since the previous heartbeat, capped so idle gaps are not counted. */
    private function credit(ApplicantViewSession $session, bool $count): void
    {
        if (!$count) {
            return;
        }
        $gap = $session->last_seen_at ? $session->last_seen_at->diffInSeconds(now()) : 0;
        $session->active_seconds += min($gap, ApplicantViewSession::MAX_BEAT_GAP);
    }
}
