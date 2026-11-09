<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Mail\OfferAcceptanceUpdate;
use App\Services\DocuSeal\OfferDecline;
use Illuminate\Http\Request;

/**
 * /offer/decline/{slug}: the Decline button in the DocuSeal offer acceptance email.
 * Public (the applicant is not signed in); GET only shows the page, POST declines.
 */
class OfferDeclineController extends Controller
{
    public function show(string $slug, OfferDecline $decline)
    {
        $row = $decline->find($slug);
        if (!$row) {
            return response()->view('pages.offer.decline', $this->page() + ['state' => 'missing', 'slug' => $slug, 'offer' => []], 404);
        }

        return view('pages.offer.decline', $this->page() + [
            'state' => $decline->status($row),
            'slug' => $slug,
            'offer' => $decline->summary($row),
            'justDeclined' => session('offer_declined') === $slug,
        ]);
    }

    public function store(Request $request, string $slug, OfferDecline $decline)
    {
        $row = $decline->find($slug);
        abort_unless($row, 404);
        $data = $request->validate(['reason' => 'nullable|string|max:1000']);

        $result = $decline->decline($row, trim((string) ($data['reason'] ?? '')) ?: null);

        // The confirmation modal posts in the background and shows the outcome itself.
        if ($request->expectsJson()) {
            $copy = [
                OfferDecline::DECLINED => 'We have emailed you a confirmation. If you change your mind, please contact our Admissions Team.',
                OfferDecline::SIGNED => 'Your offer acceptance form has already been signed, so the offer can no longer be declined online. Please contact our Admissions Team.',
                OfferDecline::CLOSED => 'This link is no longer active. If you have a newer offer email, please use that, or contact our Admissions Team.',
                OfferDecline::ERROR => 'We could not complete this just now. Please try again in a few minutes.',
            ];

            return response()->json(['state' => $result['state'], 'message' => $copy[$result['state']] ?? $copy[OfferDecline::ERROR]], $result['state'] === OfferDecline::ERROR ? 503 : 200);
        }

        if ($result['state'] === OfferDecline::ERROR) {
            return redirect()->route('offer.decline', $slug)->withInput()
                ->with('offer_decline_error', 'We could not complete this just now. Please try again in a few minutes.');
        }

        return redirect()->route('offer.decline', $slug)->with('offer_declined', $result['state'] === OfferDecline::DECLINED ? $slug : null);
    }

    private function page(): array
    {
        $site = Option::where('category', 'SITE_SETTINGS')->whereIn('name', ['site_logo', 'company_name', 'company_address', 'company_phone'])->pluck('value', 'name');

        return [
            'logoUrl' => !empty($site['site_logo']) ? url('storage/'.$site['site_logo']) : null,
            'companyName' => $site['company_name'] ?? 'London Churchill College',
            'companyAddress' => $site['company_address'] ?? 'Barclay Hall, 156B Green Street, London, E7 8JQ',
            'companyPhone' => OfferAcceptanceUpdate::displayPhone($site['company_phone'] ?? '02073771077'),
            'companyPhoneTel' => OfferAcceptanceUpdate::telPhone($site['company_phone'] ?? '02073771077'),
            'contactEmail' => (string) config('services.docuseal.contact_email', 'admission@lcc.ac.uk'),
        ];
    }
}
